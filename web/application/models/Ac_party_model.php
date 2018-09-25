<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_party_model extends MY_Model
{
    protected $table_name = 'ac_parties';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data', 'prepare_party_defaults', 'prepare_party_fts_data'];
    // protected $before_update = ['prepare_contact_data', 'prepare_party_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'branch_id', 'type', 'pan', 'full_name', 'company_reg_no', 'citizenship_no', 'passport_no', 'fts', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'type',
            'label' => 'Party Type',
            'rules' => 'trim|required|alpha|exact_length[1]|in_list[I,C]',
            '_type'     => 'radio',
            '_data'     => [ 'I' => 'Individual', 'C' => 'Company'],
            '_required' => true
        ],
        [
            'field' => 'full_name',
            'label' => 'Full Name',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],

        // If type is Company
        [
            'field' => 'company_reg_no',
            'label' => 'Company Reg Number',
            'rules' => 'trim|max_length[20]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'citizenship_no',
            'label' => 'Citizenship Number',
            'rules' => 'trim|max_length[20]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'passport_no',
            'label' => 'Passport Number',
            'rules' => 'trim|alpha_dash|max_length[20]',
            '_type'     => 'text',
            '_required' => false
        ],

        [
            'field' => 'pan',
            'label' => 'PAN',
            'rules' => 'trim|alpha_dash|max_length[20]',
            '_type'     => 'text',
            '_required' => false
        ]
    ];


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


        // Dependant Model
        $this->load->model('address_model');
    }

    // ----------------------------------------------------------------

    /**
     * Add New Record
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function add($post_data)
    {

        $cols = ['type', 'pan', 'full_name', 'company_reg_no', 'citizenship_no', 'passport_no'];
        $data = [];

        /**
         * Task1: Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        /**
         * Task 2: Branch ID
         */
        $data = $this->prepare_party_defaults($data);


        /**
         * Task 3: Get Fulltext Search Field
         */
        $data['fts'] = $this->prepare_party_fts_data($post_data);


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
                $this->address_model->add(IQB_ADDRESS_TYPE_GENERAL_PARTY, $done ,$post_data);
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
     * Edit an Party
     *
     * @param int $id Party ID
     * @param inte $address_id Address ID of this Party
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function edit($id, $address_id, $post_data)
    {
        $cols = ['type', 'pan', 'full_name', 'company_reg_no', 'citizenship_no', 'passport_no'];
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
        $data['fts'] = $this->prepare_party_fts_data($post_data);

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
     * Add branch_id Information before insert
     *
     * @param array $data
     * @return array
     */
    public function prepare_party_defaults($data)
    {
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
    public function prepare_party_fts_data($data)
    {
        // $contact_arr = $data['contacts'];

        $dt = $data;
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

        return $this->db->where('P.id', $id)
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
                $this->db->where(['P.id <=' => $next_id]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['P.type' =>  $type]);
            }

            $company_reg_no = $params['company_reg_no'] ?? NULL;
            if( $company_reg_no )
            {
                $this->db->where(['P.company_reg_no' =>  $company_reg_no]);
            }

            $citizenship_no = $params['citizenship_no'] ?? NULL;
            if( $citizenship_no )
            {
                $this->db->where(['P.citizenship_no' =>  $citizenship_no]);
            }

            $passport_no = $params['passport_no'] ?? NULL;
            if( $passport_no )
            {
                $this->db->where(['P.passport_no' =>  $passport_no]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->where("MATCH ( P.fts ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
                // $this->db->like('P.full_name', $keywords, 'after');
            }
        }
        return $this->db
                        ->order_by('P.id', 'desc')
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
        $this->db->select('P.id, P.pan, P.full_name, P.type, P.company_reg_no, P.citizenship_no, P.passport_no')
                 ->from($this->table_name . ' as P');


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
            'module' => 'P'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_GENERAL_PARTY, NULL, $table_aliases);
    }

    // --------------------------------------------------------------------

    /**
     * Get Name
     *
     * @param integer $id
     * @return string
     */
    public function name($id)
    {
        return $this->db->select('P.full_name')
                 ->from($this->table_name . ' as P')
                 ->where('P.id', $id)
                 ->get()->row()->full_name;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [];

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

        /**
         * Party already used with Vouchers?
         */
        if( !$this->is_deletable($id) )
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
            $this->address_model->delete_by(['type' => IQB_ADDRESS_TYPE_GENERAL_PARTY, 'type_id' => $id]);

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

        /**
         * Is this party Deletable?
         *
         * Check to make sure that the party is not associated with any :
         *      1. Voucher
         *
         * @param integer $id
         * @return bool
         */
        private function is_deletable($id)
        {
            /**
             * Has this party been associated with any transaction?
             */
            $voucher_count = $this->db->from($this->table_name . ' AS P')
                                      ->join('ac_voucher_details VDTL', 'VDTL.party_id = P.id')
                                      ->where('VDTL.party_type', IQB_AC_PARTY_TYPE_GENERAL )
                                      ->count_all_results();

            return $voucher_count ? FALSE : TRUE;
        }
}