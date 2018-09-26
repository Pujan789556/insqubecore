<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle_reg_prefix_model extends MY_Model
{
    protected $table_name = 'master_vehicle_reg_prefixes';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'type', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


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

    public function lookup($keywords)
    {
        // remove whitespace from start and end if any
        $keywords = trim($keywords);

        // remove non-alphanumeric characters, but retain white space
        $keywords = preg_replace("/[^A-Za-z0-9 ]/", '', $keywords);

        // Prepare Cache Key
        $key = preg_replace('/\s+/', '', $keywords); // remove all whitespaces for
        $cache_key = 'vrp_' . $key;

        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_key);
        if(!$list)
        {
            $records = $this->db->select('VRP.id, VRP.name_en, VRP.name_np')
                         ->from($this->table_name . ' VRP')
                         ->like('VRP.name_en', $keywords, 'after')
                         ->limit(20)
                         ->get()
                         ->result();

            $list = [];
            foreach($records as $single)
            {
                $list[] = ['key' => $single->id, 'value' => $single->name_en];
            }
            $list ? $this->write_cache($list, $cache_key, CACHE_DURATION_HALF_HR) : '';
        }
        return $list;
    }


    // ----------------------------------------------------------------

    public function exists($where, $id=NULL)
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
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'vrp_*'
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
}