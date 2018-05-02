<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Region_model extends MY_Model
{
    protected $table_name = 'master_regions';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

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

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('regions_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'regions_all', CACHE_DURATION_DAY);
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
            $list["{$record->id}"] = $record->name_en;
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	// cache name without prefix
        return $this->delete_cache('regions_all');
    }
}