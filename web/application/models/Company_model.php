<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends MY_Model
{
    protected $table_name = 'master_companies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name_en", "name_np", "picture", "pan_no", "active", "type", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Load Dependant Model
        $this->load->model('address_model');

        // Validation Rules
        $this->validation_rules();
    }


    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $type_in_list = implode(',', array_keys(_COMPANY_type_dropdown(FALSE)));
        $this->validation_rules = [
            [
                'field' => 'name_en',
                'label' => 'Company Name(EN)',
                'rules' => 'trim|required|max_length[150]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'name_np',
                'label' => 'Company Name(ने)',
                'rules' => 'trim|required|max_length[200]',
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
                'rules' => 'trim|integer|in_list[1]',
                '_type' => 'switch',
                '_checkbox_value' => '1'
            ]
        ];
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
        $cols = ["name_en", "name_np", "picture", "pan_no", "active", "type"];
        $data = [];

        /**
         * Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        $done  = FALSE;
        // Use automatic transaction
        $this->db->trans_start();

            // Insert Company Record
            $done = parent::insert($data, TRUE);

            $branch_data = [
                'company_id'        => $done,
                'name_en'           => 'Head Office',
                'name_np'           => 'प्रधान कार्यालय',
                'is_head_office'    => IQB_FLAG_ON
            ];

            // Unset All Company Columns
            foreach($cols as $col)
            {
                unset($post_data[$col]);
            }
            $post_data = array_merge($branch_data, $post_data);

            // Add Branch and its contact
            $this->company_branch_model->add_ho($post_data);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Add New Record
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function edit($id, $post_data)
    {
        $cols = ["name_en", "name_np", "picture", "pan_no", "active", "type"];
        $data = [];

        /**
         * Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        // Disable DB Debug for transaction to work
        $done = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Insert Company Record
            $done = parent::update($id, $data, TRUE);

            // Update Head Office Contact Address
            $this->company_branch_model->update_ho_contact($id, $post_data);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Creditor Companies
     *
     * @return array
     */
    public function dropdown_creditor($lang="both")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'company_creditor_dd_' . $lang;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_BANK)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = $this->_format_dropdown($records, $lang);
            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
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
    public function dropdown_general($lang="both")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'company_general_dd_' . $lang;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_GENERAL)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = $this->_format_dropdown($records, $lang);
            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
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
    public function dropdown_brokers($lang="both")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'company_broker_dd_' . $lang;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_BROKER)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = $this->_format_dropdown($records, $lang);
            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
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
    public function dropdown_reinsurers($lang="both")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'company_reinsurer_dd_' . $lang;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_RE_INSURANCE)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = $this->_format_dropdown($records, $lang);
            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List of Insurance Companies
     *
     * @return array
     */
    public function dropdown_insurance($lang="both")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'company_insurance_dd_' . $lang;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $records = $this->db->select('C.id, C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.type', IQB_COMPANY_TYPE_INSURANCE)
                             ->where('C.active', IQB_STATUS_ACTIVE)
                             ->get()->result();

            $list = $this->_format_dropdown($records, $lang);
            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

        /**
         * Format Dropdown based on Language
         *
         * @param array $records
         * @param string $lang
         * @return array
         */
        private function _format_dropdown($records, $lang)
        {
            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                if($lang == 'both')
                {
                    $value = $record->name_en . ' (' . $record->name_np . ')';
                }
                else if($lang == 'en')
                {
                    $value = $record->name_en;
                }
                else
                {
                    $value = $record->name_np;
                }

                $list["{$column}"] = $value;
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
    public function get($id)
    {
        return $this->db->select('C.*, CB.id AS company_branch_id, CB.name_en as ho_branch_name_en, CB.name_np as ho_branch_name_np')
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
        $this->db->select('C.id, C.name_en, C.name_np, C.pan_no, C.type, C.active')
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
                $this->db->like('C.name_en', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
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
        $record = $this->db->select('C.name_en, C.name_np')
                             ->from($this->table_name . ' as C')
                             ->where('C.id', $id)
                             ->get()->row();

        $name = $lang == 'en' ? $record->name_en : $record->name_np;

        return $name;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'company_creditor_dd_*',
            'company_general_dd_*',
            'company_broker_dd_*',
            'company_reinsurer_dd_*',
            'company_insurance_dd_*',
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

        $this->load->model('company_branch_model');


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();


            // Delete all branch and it's address first
            $this->company_branch_model->delete_by_company($id);

            // Delete Main Record
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