<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_duties_and_tax_model extends MY_Model
{
    protected $table_name = 'ac_duties_and_tax';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name", "rate", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Name',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'rate',
            'label' => 'Rate(%)',
            'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
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

    public function get($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'ac_dat_' . $id;

        $record = $this->get_cache($cache_name);
        if(!$record)
        {
            $record = $this->db->select('`id`, `name`, `rate`')
                        ->from($this->table_name)
                        ->where('id', $id)
                        ->get()->row();
            $this->write_cache($record, $cache_name, CACHE_DURATION_MONTH);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('ac_dat_all');
        if(!$list)
        {
            $list = $this->db->select('`id`, `name`, `rate`')
                        ->from($this->table_name)
                        ->get()->result();
            $this->write_cache($list, 'ac_dat_all', CACHE_DURATION_MONTH);
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
            $list["{$record->id}"] = $record->name . "({$record->rate}%) ";
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
            'ac_dat_*'
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
            'module' => 'ac_duties_and_tax',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}