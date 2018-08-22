<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends MY_Model
{
    protected $table_name = 'master_companies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['trg_after_save', 'clear_cache'];
    protected $after_update  = ['trg_after_save', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name", "picture", "pan_no", "active", "type", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 88; // First 86; i.e. imported old data

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Validation Rules
        $this->validation_rules();
    }


    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $type_in_list = implode(',', array_keys(_COMPANY_type_dropdown(FALSE)));
        $this->validation_rules = [
            [
                'field' => 'name',
                'label' => 'Company Name',
                'rules' => 'trim|required|max_length[80]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'pan_no',
                'label' => 'Company Pan No',
                'rules' => 'trim|max_length[20]',
                '_type'     => 'text',
                '_required' => false
            ],
            [
                'field' => 'type',
                'label' => 'Company Type',
                'rules' => 'trim|required|alpha|exact_length[1]|in_list[' . $type_in_list . ']',
                '_type'     => 'dropdown',
                '_data'     => _COMPANY_type_dropdown(),
                '_required' => true
            ],
            [
                'field' => 'active',
                'label' => 'Is Active?',
                'rules' => 'trim|required|integer|exact_length[1]',
                '_type'     => 'dropdown',
                '_data'     => [ '' => 'Select...', '1' => 'Active', '0' => 'Not Active'],
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function trg_after_save($arr_record)
    {
        /**
         *
         * Data Structure
                Array
                (
                    [id] => 10
                    [fields] => Array
                        (
                            [name] => 6
                            [picture] => 1
                            [pan_no] =>

                            ...

                            [updated_at] => 2016-12-28 15:51:47
                            [updated_by] => 1
                        )

                    [result] => 1
                    [method] => update
                )
        */
        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $fields = $arr_record['fields'];
            $method = $arr_record['method'];

            $this->load->model('company_branch_model');
            $contact = get_contact_data_from_form();

            if($method == 'update')
            {
                // Update Branch Head Office Contact
                $this->company_branch_model->update_ho_contact($id, $contact);
            }
            else
            {
                // Create a New Head Office Contact
                $branch_data = [
                    'company_id'     => $id,
                    'name'           => 'Head Office',
                    'is_head_office' => IQB_FLAG_ON,
                    'contact'        => $contact,
                ];
                $this->company_branch_model->insert($branch_data, TRUE);
            }
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Creditor Companies
     *
     * @return array
     */
    public function dropdown_creditor()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('company_creditor_dropdown');
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_BANK)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                $list["{$column}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'company_creditor_dropdown', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Creditor Companies
     *
     * @return array
     */
    public function dropdown_general()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('company_general_dropdown');
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_GENERAL)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                $list["{$column}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'company_general_dropdown', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Broker Companies
     *
     * @return array
     */
    public function dropdown_brokers()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('company_broker_dropdown');
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_BROKER)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                $list["{$column}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'company_broker_dropdown', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Reinsurers Companies
     *
     * @return array
     */
    public function dropdown_reinsurers()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('company_reinsurer_dropdown');
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_RE_INSURANCE)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                $list["{$column}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'company_reinsurer_dropdown', CACHE_DURATION_DAY);
            }
        }
        return $list;
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
        return $this->db->select('C.name')
                 ->from($this->table_name . ' as C')
                 ->where('C.id', $id)
                 ->get()->row()->name;
    }

    // ----------------------------------------------------------------

    /**
     * Get Name
     *
     * @param integer $id
     * @return string
     */
    public function get($id)
    {
        return $this->db->select('C.*, CB.contact as ho_contact, CB.name as ho_branch_name')
                 ->from($this->table_name . ' as C')
                 ->join('master_company_branches CB', "CB.company_id = C.id AND CB.is_head_office ='".IQB_FLAG_ON."'", 'left')
                 ->where('C.id', $id)
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
        $this->db->select('C.id, C.name, C.pan_no, C.type, C.active')
                 ->from($this->table_name . ' as C');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['C.id >=' => $next_id]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['C.type' =>  $type]);
            }

            $active = $params['active'];
            $active = $active === '' ? NULL : $active; // to work with 0 value
            if( $active !== NULL )
            {
                $this->db->where(['C.active' =>  $active]);
            }

            $pan_no = $params['pan_no'] ?? NULL;
            if( $pan_no )
            {
                $this->db->where(['C.pan_no' =>  $pan_no]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('C.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'company_creditor_dropdown',
            'company_general_dropdown',
            'company_broker_dropdown',
            'company_reinsurer_dropdown'
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
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}