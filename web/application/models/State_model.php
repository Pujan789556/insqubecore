<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class State_model extends MY_Model
{
    protected $table_name = 'master_states';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', 'code'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "code", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ]
    ];


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

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown($type="both")
    {
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $label = $type === "both"
                        ? $record->name_en . " ({$record->name_np})"
                        : ($type === "en" ? $record->name_en : $record->name_np);
            $list["{$record->id}"] = $label;
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('states_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'states_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

	// --------------------------------------------------------------------

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

    // ----------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	// cache name without prefix
        return $this->delete_cache('states_all');
    }
}