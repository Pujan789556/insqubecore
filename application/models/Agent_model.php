<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agent_model extends MY_Model
{
    protected $table_name = 'master_agents';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data'];
    protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name", "picture", "ud_code", "bs_code", "commission_group", "active", "type", "contact", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Agent Name',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'ud_code',
            'label' => 'Agent UD Code',
            'rules' => 'trim|integer|max_length[15]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'bs_code',
            'label' => 'Agent BS Code',
            'rules' => 'trim|max_length[15]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'type',
            'label' => 'Agent Type',
            'rules' => 'trim|required|integer|exact_length[1]|in_list[1,2]',
            '_type'     => 'dropdown',
            '_data'     => [ '' => 'Select...', '1' => 'Individual', '2' => 'Company'],
            '_required' => true
        ],
        [
            'field' => 'commission_group',
            'label' => 'Commission Group',
            'rules' => 'trim|required|integer|exact_length[1]|in_list[1,2,3]',
            '_type'     => 'dropdown',
            '_data'     => [ '' => 'Select...', '1' => 'Commission Group 1', '2' => 'Commission Group 2', '3' => 'Commission Group 3'],
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

    public function prepare_contact_data($data)
    {
        $data['contact'] = get_contact_data_from_form();
        return $data;
    }

    // ----------------------------------------------------------------

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
        $this->db->select('A.id, A.name, A.ud_code, A.bs_code, A.type, A.active, A.commission_group')
                 ->from($this->table_name . ' as A');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['A.id >=' => $next_id]);
            }

            $ud_code = $params['ud_code'] ?? NULL;
            if( $ud_code )
            {
                $this->db->where(['A.ud_code' =>  $ud_code]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['A.type' =>  $type]);
            }

            $active = $params['active'];
            $active = $active === '' ? NULL : $active; // to work with 0 value
            if( $active !== NULL )
            {
                $this->db->where(['A.active' =>  $active]);
            }

            $commission_group = $params['commission_group'] ?? NULL;
            if( $commission_group )
            {
                $this->db->where(['A.commission_group' =>  $commission_group]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('A.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }


    /**
     * Get Dropdown List
     */
    public function dropdown($flag_active_only = false)
    {
        $cache_name = $flag_active_only ? 'agent_dropdown_active' : 'agent_dropdown';
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $this->db->select('id, name')
                        ->from($this->table_name);

            if($flag_active_only)
            {
                $this->db->where('active', 1);
            }

            $records = $this->db->get()->result();
            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = $record->name;
            }

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }

        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'agent_dropdown_active',
            'agent_dropdown',
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
            'module' => 'agent',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}