<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_type_model extends MY_Model
{

    protected $table_name = 'ri_setup_treaty_types';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', 'name'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "name", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [];


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
        $list = $this->get_cache('ri_treaty_types_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'ri_treaty_types_all', CACHE_DURATION_MONTH);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name;
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
                        ->count_all_results($this->table);
    }


	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	$cache_names = [
            'ri_treaty_types_all',
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }
}