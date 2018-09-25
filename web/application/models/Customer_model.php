<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends MY_Model
{
    protected $table_name = 'dt_customers';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data', 'prepare_customer_defaults', 'prepare_customer_fts_data'];
    // protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'branch_id', 'code', 'type', 'pan', 'full_name', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no', 'fts', 'flag_locked', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $endorsement_fields = [
        'customer' =>  ['type', 'pan', 'full_name', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no'],
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
        $this->validation_rules();
    }


    // ----------------------------------------------------------------

    public function validation_rules()
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

        $this->validation_rules = [
            [
                'field' => 'type',
                'label' => 'Customer Type',
                'rules' => 'trim|required|alpha|exact_length[1]|in_list[I,C]',
                '_type'     => 'radio',
                '_data'     => [ 'I' => 'Individual', 'C' => 'Company'],
                '_required' => true
            ],
            [
                'field' => 'full_name',
                'label' => 'Full Name',
                'rules' => 'trim|required|max_length[150]',
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
    }

    // ----------------------------------------------------------------

    /**
     * Add New Customer
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function add($post_data)
    {

        $cols = ['type', 'pan', 'full_name', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no'];
        $data = [];

        /**
         * Task1: Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        /**
         * Task 2: Branch ID and Customer Code
         */
        $data = $this->prepare_customer_defaults($data);


        /**
         * Task 3: Get Fulltext Search Field
         */
        $data['fts'] = $this->prepare_customer_fts_data($post_data);


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::insert($data, TRUE);

            // Insert Address
            if($done)
            {
                $this->address_model->add(IQB_ADDRESS_TYPE_CUSTOMER, $done ,$post_data);
            }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $done;
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
        $cols = ['type', 'pan', 'full_name', 'grandfather_name', 'father_name', 'mother_name', 'spouse_name', 'picture', 'profession', 'nationality', 'dob', 'identification_no', 'identification_doc', 'company_reg_no'];
        $data = [];

        /**
         * Task1: Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        /**
         * Task 2: Get Fulltext Search Field
         */
        $data['fts'] = $this->prepare_customer_fts_data($post_data);

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::update($id, $data, TRUE);

            // Insert Address
            if($done)
            {
                $this->address_model->edit($address_id ,$post_data);
            }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Prepare Customer Defaults
     *
     * Generate customer code and branch id before inserting.
     * These are non changable values.
     *
     * Before Insert Trigger to generate customer code and branch_id
     *
     * @param array $data
     * @return array
     */
    public function prepare_customer_defaults($data)
    {
        $this->load->library('Token');

        $data['code']       = $this->token->generate(12);
        $data['branch_id']  = $this->dx_auth->get_branch_id();

        return $data;
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
    public function prepare_customer_fts_data($data)
    {
        // $contact_arr = $data['contacts'];

        $dt = $data;
        unset($dt['code']); // we have  separate field to filter
        unset($dt['contacts']);
        unset($dt['contact']);
        unset($dt['updated_at']);
        unset($dt['updated_by']);
        unset($dt['created_at']);
        unset($dt['created_by']);

        $fts_data = [];
        foreach($dt as $key=>$val)
        {
            if($val){
                if($key == 'type')
                {
                    $fts_data []= $val == 'C' ? 'Company' : 'Individual';
                }else{
                    $fts_data [] =  $val;
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
        return $this->db->where('id', $id)
                        ->update($this->table_name, $customer)

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
     * Get Name
     *
     * @param integer $id
     * @return string
     */
    public function name($id)
    {
        return $this->db->select('C.full_name')
                 ->from($this->table_name . ' as C')
                 ->where('C.id', $id)
                 ->get()->row()->full_name;
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
        $this->db->select('C.id, C.code, C.pan, C.full_name, C.picture, C.type, C.profession, C.company_reg_no, C.identification_no, C.dob, C.flag_locked')
                 ->from($this->table_name . ' as C');


        // Include Address Information
        $this->address_model->module_select('C', IQB_ADDRESS_TYPE_CUSTOMER);
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