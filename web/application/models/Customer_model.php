<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends MY_Model
{
    protected $table_name = 'dt_customers';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    // protected $before_insert = [];
    // protected $before_update = [];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'branch_id', 'code', 'mobile_identity', 'type', 'pan', 'full_name_en', 'full_name_np', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no', 'fts', 'flag_locked', 'flag_kyc_verified', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    // Columns on Edit (NOT ENDORSEMENT)
    public static $editable_fields = ['type', 'pan', 'full_name_en', 'full_name_np', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no'];

    protected $endorsement_fields = [
        'customer' =>  ['type', 'pan', 'full_name_en', 'full_name_np', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no'],
        'address' => ['country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web']
    ];



    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Load Dependant Model
        $this->load->model('address_model');

        // Validation rules
        // $this->validation_rules();
    }


    // ----------------------------------------------------------------

    /**
     * Validation Rules for Customer ADD/EDIT
     *
     * @return array
     */
    public function v_rules($action)
    {
        $v_rules_api_identity    = $this->_v_rules_api_identity();
        $v_rules_common          = $this->_v_rules_common();

        $v_rules = [];

        switch ($action)
        {
            case 'add':
                $v_rules = array_merge($v_rules_api_identity, $v_rules_common);
                break;

            case 'edit':
            case 'endorsement':
                $v_rules = $v_rules_common;
                break;

            case 'app_identity':
                $v_rules = $v_rules_api_identity;
                break;

            case 'verify_kyc':
                $v_rules = $this->_v_rules_verify_kyc();
                break;

            default:
                # code...
                break;
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Common Validation Rules
     *
     * @return array
     */
    public function _v_rules_common()
    {
        $this->load->model('country_model');
        $countries = $this->country_model->dropdown();


        $post = $this->input->post();
        $type = $post['type'] ?? NULL;

        $individual_requird = '';
        if( $type )
        {
            if ($type == 'I' )
            {
                $individual_requird = '|required';
            }
        }
        $nationality_rules = 'trim' . $individual_requird . '|alpha|exact_length[2]';
        $father_name_rules = 'trim' . $individual_requird . '|max_length[150]';

        $v_rules = [
            [
                'field' => 'type',
                'label' => 'Customer Type',
                'rules' => 'trim|required|alpha|exact_length[1]|in_list[I,C]',
                '_type'     => 'radio',
                '_data'     => [ 'I' => 'Individual', 'C' => 'Company'],
                '_required' => true
            ],
            [
                'field' => 'full_name_en',
                'label' => 'Full Name (EN)',
                'rules' => 'trim|required|max_length[150]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'full_name_np',
                'label' => 'Full Name (ने)',
                'rules' => 'trim|required|max_length[200]',
                '_type'     => 'text',
                '_required' => true
            ],

            /**
             * Individual Only Fields
             */
            [
                'field'     => 'nationality',
                'label'     => 'Nationality',
                'rules'     => $nationality_rules,
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $countries,
                '_default'  => 'NP',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => true
            ],
            [
                'field' => 'grandfather_name',
                'label' => 'Grandfather Name',
                'rules' => 'trim|max_length[150]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => false
            ],
            [
                'field' => 'father_name',
                'label' => 'Father Name',
                'rules' => $father_name_rules,
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => true
            ],
            [
                'field' => 'mother_name',
                'label' => 'Mother Name',
                'rules' => 'trim|max_length[150]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => false
            ],
            [
                'field' => 'spouse_name',
                'label' => 'Spouse Name',
                'rules' => 'trim|max_length[150]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => false
            ],
            [
                'field' => 'identification_no',
                'label' => 'Citizenship / Passport / License Number',
                'rules' => 'trim|max_length[40]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_required' => false
            ],
            [
                'field' => 'dob',
                'label' => 'Date of Birth(AD/BS)',
                'rules' => 'trim|alpha_dash|max_length[10]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="I"',
                '_help_text' => 'Valid date example: 2040-09-09',
                '_required' => false
            ],

            /**
             * Company Only Fields
             */
            [
                'field' => 'company_reg_no',
                'label' => 'Company Reg Number',
                'rules' => 'trim|max_length[20]',
                '_type'     => 'text',
                '_extra_attributes' => 'data-ref="C"',
                '_required' => false
            ],
            [
                'field' => 'pan',
                'label' => 'PAN',
                'rules' => 'trim|alpha_dash|max_length[20]',
                '_type'     => 'text',
                '_required' => false
            ],
            [
                'field' => 'profession',
                'label' => 'Profession / Field of Experties',
                'rules' => 'trim|max_length[50]',
                '_type'     => 'text',
                '_required' => false
            ]
        ];

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Validation Rules - API/APP Identity
     *
     * @return array
     */
    public function _v_rules_api_identity()
    {
        $v_rules = [
            [
                'field' => 'mobile_identity',
                'label' => 'Mobile Identity',
                'rules' => 'trim|valid_mobile|max_length[10]|callback__cb_valid_mobile_identity',
                '_type' => 'text',
                '_id'   => 'mobile-identity',
                '_help_text' => 'This mobile number is used by customer to access Mobile App',
                '_required' => false
            ]
        ];

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Validation Rules - API/APP Identity
     *
     * @return array
     */
    public function _v_rules_verify_kyc()
    {
        $dropdown = _FLAG_on_off_dropdown(FALSE);
        $v_rules = [
            [
                'field' => 'flag_kyc_verified',
                'label' => 'Verify KYC',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',', array_keys($dropdown)).']',
                '_type' => 'dropdown',
                '_data' => IQB_BLANK_SELECT + $dropdown,
                '_help_text'    => 'You have to verify KYC coming directly from customer. Only after you verify, they will able to proceed further.',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     * Add New Customer
     *
     * @param array $post_data Form Post Data
     * @param string $origin [main|api] Where it came from?
     * @return mixed
     */
    public function add($post_data, $origin = 'main')
    {
        /**
         * Prepare Data
         */
        $data = $this->__prepare_add_data($post_data, $origin);

        // Use automatic transaction
        $done = FALSE;
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::insert($data, TRUE);

            // Post ADD Tasks
            if($done)
            {
                $this->__post_add_tasks($done, $post_data, $origin);
            }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

        /**
         * Perform Pre-Customer-Add Tasks
         *
         *  - ADD Columns
         * - Code
         * - Branch ID
         * - FTS
         *
         * @param array $data
         * @param string $origin [main|api] Where it came from?
         * @return array
         */
        public function __prepare_add_data($post_data, $origin = 'main')
        {
            $this->load->library('Token');

            $cols = ['mobile_identity', 'type', 'pan', 'full_name_en', 'full_name_np', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no', 'flag_kyc_verified'];
            $data = [];

            /**
             * Task1: Prepare Basic Data
             */
            foreach($cols as $col)
            {
                $data[$col] = $post_data[$col] ?? NULL;
            }

            // Dummy Characters if Mobile Identity is Blank
            if(!$data['mobile_identity'])
            {
                $data['mobile_identity'] = TOKEN::v2(12);
            }

            // Code
            $data['code']       = TOKEN::v2(12);

            // Branch ID
            if($origin == 'main')
            {
                $data['branch_id']  = $this->dx_auth->get_branch_id();
            }
            else
            {
                // Headquarter ID
                $this->load->model('branch_model');
                $data['branch_id']  = $this->branch_model->head_office_id();
            }


           // FTS
            $data['fts'] = $this->_prepare_fts_data($data);

            return $data;
        }

        // ----------------------------------------------------------------
        /**
         * Perform Post-Customer-Add Tasks
         *
         * @param int $id
         * @param array $post_data
         * @param string $origin [main|api] Where it came from?
         * @return bool
         */
        private function __post_add_tasks($id, $post_data, $origin = 'main')
        {
            $customer = parent::find($id);

            /**
             * Task 1: Add Customer Address
             */
            $this->address_model->add(IQB_ADDRESS_TYPE_CUSTOMER, $customer->id ,$post_data);

            /**
             * Task 2: Create a Mobile App User
             */
            $this->load->model('api/app_user_model', 'app_user_model');
            $app_user_data = [
                'mobile'        => $customer->mobile_identity,
                'auth_type'     => IQB_API_AUTH_TYPE_CUSTOMER,
                'auth_type_id'  => $customer->id,
                'password'      => $post_data['password'] ?? NULL, // Set password if sent
            ];
            $this->app_user_model->register($app_user_data, FALSE);



            return TRUE;
        }

    // ----------------------------------------------------------------

    /**
     * Edit an Customer
     *
     * @param int $id Customer ID
     * @param inte $address_id Address ID of this Customer
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function edit($id, $address_id, $post_data)
    {
        /**
         * Prepare Data
         */
        $data = $this->__prepare_edit_data($post_data);

        // Use automatic transaction
        $done = FALSE;
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::update($id, $data, TRUE);

            // Post Update Tasks
            if($done)
            {
                $this->__post_edit_tasks($id, $address_id, $post_data);
            }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

        /**
         * Perform Pre-Customer-Add Tasks
         *
         *  - Edit Columns
         *  - FTS
         *
         * @param array $data
         * @return array
         */
        public function __prepare_edit_data($post_data)
        {
            $data = [];

            /**
             * Task1: Prepare Basic Data
             */
            foreach(self::$editable_fields as $col)
            {
                $data[$col] = $post_data[$col] ?? NULL;
            }

            /**
             * Task 2: Get Fulltext Search Field
             */
            $data['fts'] = $this->_prepare_fts_data($post_data);

            return $data;
        }

        // ----------------------------------------------------------------
        /**
         * Perform Post-Customer-Add Tasks
         *
         * @param int $id
         * @param array $post_data
         * @return bool
         */
        private function __post_edit_tasks($id, $address_id, $post_data)
        {
            $customer = parent::find($id);

            /**
             * Task 1: Update Customer Address
             */
            $this->address_model->edit($address_id ,$post_data);

            return TRUE;
        }

    // ----------------------------------------------------------------

    public function change_app_identity($id, $mobile_identity)
    {
        $data = [
            'mobile_identity' => $mobile_identity
        ];
        // Use automatic transaction
        $done = FALSE;
        $this->db->trans_start();

            // Update Customer Record
            $done = parent::update($id, $data, TRUE);

            // Post Update Tasks
            if($done)
            {
                /**
                 * Task 2: Update Mobile if Change
                 */
                $this->load->model('api/app_user_model', 'app_user_model');
                $this->app_user_model->change_mobile_by(IQB_API_AUTH_TYPE_CUSTOMER, $id, $mobile_identity, FALSE);
            }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    public function verify_kyc($id, $flag_kyc_verified)
    {
        $data = [
            'flag_kyc_verified' => $flag_kyc_verified
        ];
        // Use automatic transaction
        $done = FALSE;
        $this->db->trans_start();

            // Update Customer Record
            $done = parent::update($id, $data, TRUE);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $done = FALSE;
        }

        // return result/status
        return $done;
    }



    // ----------------------------------------------------------------

    /**
     * Prepare Customer Full Text Search Data
     *
     * Build Full text Search data from all the revelant columns
     *
     * Before Insert/Update Trigger to generate customer code and branch_id
     *
     * @param array $data
     * @return array
     */
    public function _prepare_fts_data($data)
    {
        $fts_keys = ['type', 'pan', 'full_name_en', 'full_name_np', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'profession', 'nationality', 'dob', 'identification_no', 'company_reg_no'];

        $fts_data = [];
        foreach($fts_keys as $key)
        {
            $val = $data[$key] ?? '';
            if($val)
            {
                if($key == 'type')
                {
                    $fts_data[] = $val == 'C' ? 'Company' : 'Individual';
                }else{

                    $fts_data[] =  $val;
                }
            }
        }

        // foreach($contact_arr as $key=>$val)
        // {
        //     if($val){
        //         $fts_data [] =  $val;
        //     }
        // }
        $fts_data = implode(' ', $fts_data);
        return $fts_data;


        // $data['fts'] = $fts_data;

        // return $data;
    }

    // ----------------------------------------------------------------

    /**
     * Update Lock Flag
     *
     * @param int $id
     * @param int $flag
     * @return bool
     */
    public function update_lock($id, $flag)
    {
        if( !in_array($flag, [IQB_FLAG_UNLOCKED, IQB_FLAG_LOCKED]) )
        {
            return FALSE;
        }


        /**
         * !!! NOTE
         *
         *  While unlocking a customr, please make sure that customer do not have
         *  an active policy
         */
        if( $flag == IQB_FLAG_UNLOCKED && $this->has_active_policy($id))
        {
            return FALSE;
        }

        // Let's Update the Flag
        $data = [
            'flag_locked'   => $flag,
            'updated_by'    => $this->dx_auth->get_user_id(),
            'updated_at'    => $this->set_date()
        ];
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
    }


    // ----------------------------------------------------------------

    public function has_active_policy($id)
    {
        $this->load->model('policy_model');
        return $this->policy_model->has_active_policy_by_customer($id);
    }

    // ----------------------------------------------------------------

    /**
     * Update Endorsement Changes on Policy Table
     *
     * @param int $id
     * @param object $data contains both customer and address data
     * @return bool
     */
    public function commit_endorsement($id, $data)
    {
        $customer = (array)$data->customer;
        $address  = (array)$data->address;

        // Update Customer Data
        return parent::update($id, $customer, TRUE)
                        &&
            // Update Address Data
            $this->address_model->commit_endorsement(IQB_ADDRESS_TYPE_CUSTOMER, $id, $address);
    }

    // ----------------------------------------------------------------

    /**
     * Get record for Endorsement Edit
     *
     * @param integer $policy_id
     * @param integer $txn_id
     * @param integer $id
     * @return object
     */
    public function get_for_endorsement( $policy_id, $txn_id, $id )
    {
        $where = [
            'C.id'              => $id,
            'P.id'              => $policy_id,
            'ENDRSMNT.id'           => $txn_id,
            'ENDRSMNT.flag_current' => IQB_FLAG_ON
        ];
        return $this->db->select("C.*, P.branch_id as policy_branch_id")
                 ->from($this->table_name . ' as C')
                 ->join('dt_policies P', 'P.customer_id = C.id')
                 ->join('dt_endorsements ENDRSMNT', 'P.id = ENDRSMNT.policy_id')
                 ->where($where)
                 ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Single Data Row by ID
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param int $id
     * @return object
     */
    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('C.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {
        $this->_row_select();

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['C.id <=' => $next_id]);
            }

            $code = $params['code'] ?? NULL;
            if( $code )
            {
                $this->db->like('LOWER(C.code)', strtolower($code), 'after');
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['C.type' =>  $type]);
            }

            $company_reg_no = $params['company_reg_no'] ?? NULL;
            if( $company_reg_no )
            {
                $this->db->where(['C.company_reg_no' =>  $company_reg_no]);
            }

            $identification_no = $params['identification_no'] ?? NULL;
            if( $identification_no )
            {
                $this->db->where(['C.identification_no' =>  $identification_no]);
            }

            $dob = $params['dob'] ?? NULL;
            if( $dob )
            {
                $this->db->where(['C.dob' =>  $dob]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $keywords = addslashes($keywords);
                $this->db->where("MATCH ( C.`fts` ) AGAINST ( '+{$keywords}' IN BOOLEAN MODE)", NULL);
            }
        }
        return $this->db
                        ->order_by('C.id', 'desc')
                        ->limit($this->settings->per_page+1)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    /**
     * Select Columns for Data Listing
     *
     * @return void
     */
    private function _row_select()
    {
        $this->db->select('C.*')
                 ->from($this->table_name . ' as C');


        // Include Address Information
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADR',

            // Country Table Alias
            'country' => 'CNTRY',

            // State Table Alias
            'state' => 'STATE',

            // Local Body Table Alias
            'local_body' => 'LCLBD',

            // Type/Module Table Alias
            'module' => 'C'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_', FALSE);
    }

    // ----------------------------------------------------------------

    /**
     * Get Name
     *
     * Required By Voucher
     *
     * @param integer $id
     * @return string
     */
    public function name($id, $lang="en")
    {
        $record = $this->db->select('C.full_name_en, C.full_name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.id', $id)
                             ->get()->row();

        $name = $lang == 'en' ? $record->full_name_en : $record->full_name_np;

        return $name;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'customer_dropdown',
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Delete Primary Record
            parent::delete($id);

            // Delete Address Record
            $this->address_model->delete_by(['type' => IQB_ADDRESS_TYPE_CUSTOMER, 'type_id' => $id]);

            // Delete Mobile App User
            $this->load->model('api/app_user_model', 'app_user_model');
            $this->app_user_model->delete_user(IQB_API_AUTH_TYPE_CUSTOMER, $id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}