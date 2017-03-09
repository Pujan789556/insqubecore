<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_heading_group_model extends MY_Model
{
    protected $table_name = 'ac_account_heading_groups';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', 'range_min', 'range_max'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "range_min", "range_max", "name", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Heading Group Name',
            'rules' => 'trim|required|max_length[30]',
            '_type'     => 'text',
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 12; // Prevent first 12 records from deletion.

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

    public function valid_range($id, $ac_number)
    {
        $valid = FALSE;

        $record = parent::find($id);
        if($record)
        {
            $valid = $ac_number >= $record->range_min && $ac_number <= $record->range_max;
        }
        return $valid;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('ac_hdg_all');
        if(!$list)
        {
            $list = $this->db->select('`id`, `range_min`, `range_max`, `name`')
                        ->from($this->table_name)
                        ->get()->result();
            $this->write_cache($list, 'ac_hdg_all', CACHE_DURATION_DAY);
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
            $list["{$record->id}"] = "[{$record->range_min}-{$record->range_max}] " . $record->name;
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
            'ac_hdg_all'
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
        return FALSE;
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
            'module' => 'ac_heading_group',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}