<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Risk_model extends MY_Model
{
    protected $table_name = 'master_risks';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'name', 'type', 'agent_commission', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $type_dropdown = risk_type_dropdown(FALSE);
        $agent_commission_dropdown = _FLAG_on_off_dropdwon(FALSE);
        $this->validation_rules = [
            [
                'field' => 'name',
                'label' => 'Risk Name',
                'rules' => 'trim|required|max_length[30]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'type',
                'label' => 'Risk Type',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',',array_keys($type_dropdown)).']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $type_dropdown,
                '_required' => true
            ],
            [
                'field' => 'agent_commission',
                'label' => 'Apply Agent Commission',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',',array_keys($agent_commission_dropdown)).']',
                '_type'     => 'radio',
                '_data'     => $agent_commission_dropdown,
                '_show_label'   => true,
                '_required'     => true
            ]

        ];
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('risks_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'risks_all', CACHE_DURATION_DAY);
        }
        return $list;
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

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name . ' (' . risk_type_dropdown(FALSE)[$record->type] . ')';
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'risks_all'
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
            'module' => 'risk',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}