<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends MY_Model
{
    protected $table_name = 'dt_customers';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data', 'prepare_customer_defaults', 'prepare_customer_fts_data'];
    protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'branch_id', 'code', 'type', 'pan', 'full_name', 'picture', 'profession', 'contact', 'company_reg_no', 'citizenship_no', 'passport_no', 'fts', 'flag_locked', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
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
        ],
        [
            'field' => 'profession',
            'label' => 'Profession / Field of Experties',
            'rules' => 'trim|max_length[50]',
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
    }


    // ----------------------------------------------------------------

    /**
     * Prepare Contact Data
     *
     * Before Insert/Update Trigger to Get Contact Data From Contact Form
     *
     * @param array $data
     * @return array
     */
    public function prepare_contact_data($data)
    {
        $data['contact'] = get_contact_data_from_form();
        return $data;
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
        $contact_arr = $data['contacts'];

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

        foreach($contact_arr as $key=>$val)
        {
            if($val){
                $fts_data [] =  $val;
            }
        }
        $fts_data = implode(' ', $fts_data);

        $data['fts'] = $fts_data;

        return $data;
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
     * @param array $data
     * @return bool
     */
    public function commit_endorsement($id, $data)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
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
            'PTXN.id'           => $txn_id,
            'PTXN.flag_current' => IQB_FLAG_ON
        ];
        return $this->db->select("C.*, P.branch_id as policy_branch_id")
                 ->from($this->table_name . ' as C')
                 ->join('dt_policies P', 'P.customer_id = C.id')
                 ->join('dt_policy_txn PTXN', 'P.id = PTXN.policy_id')
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
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {
        $this->db->select('C.id, C.code, C.pan, C.full_name, C.picture, C.type, C.profession, C.company_reg_no, C.citizenship_no, C.passport_no, C.contact, C.flag_locked')
                 ->from($this->table_name . ' as C');


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

            $citizenship_no = $params['citizenship_no'] ?? NULL;
            if( $citizenship_no )
            {
                $this->db->where(['C.citizenship_no' =>  $citizenship_no]);
            }

            $passport_no = $params['passport_no'] ?? NULL;
            if( $passport_no )
            {
                $this->db->where(['C.passport_no' =>  $passport_no]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->where("MATCH ( C.`fts` ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
                // $this->db->like('C.full_name', $keywords, 'after');
            }
        }
        return $this->db
                        ->order_by('C.id', 'desc')
                        ->limit($this->settings->per_page+1)
                        ->get()->result();
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

            parent::delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            $this->log_activity($id, 'D');
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'customer',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}