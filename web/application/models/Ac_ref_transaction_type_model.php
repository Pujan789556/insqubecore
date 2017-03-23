<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_ref_transaction_type_model extends MY_Model
{
    protected $table_name = 'ac_ref_txn_types';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', 'code'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "code", "name", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Transaction Type Name',
            'rules' => 'trim|required|max_length[100]',
            '_type'     => 'text',
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 9; // Prevent first 12 records from deletion.

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

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('ac_ref_txn_type_all');
        if(!$list)
        {
            $list = $this->db->select('`id`, `code`, `name`')
                        ->from($this->table_name)
                        ->get()->result();
            $this->write_cache($list, 'ac_ref_txn_type_all', CACHE_DURATION_DAY);
        }
        return $list;
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
            $list["{$record->id}"] =  $record->code . ' - ' . $record->name;
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
            'ac_ref_txn_type_all'
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
    public function log_activity($id, $action = 'E')
    {
        $action = is_string($action) ? $action : 'E';
        // Save Activity Log
        $activity_log = [
            'module' => 'ac_ref_transaction_type',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}