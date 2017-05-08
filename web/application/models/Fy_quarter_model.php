<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fy_quarter_model extends MY_Model
{

    protected $table_name = 'master_fy_quarters';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ['id', 'fiscal_yr_id', 'quarter', 'starts_at', 'ends_at', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $list = $this->get_cache('fy_quarter_all');
        if(!$list)
        {
            $list = $this->db->select('Q.id, Q.fiscal_yr_id, Q.quarter, Q.starts_at, Q.ends_at, FY.starts_at_en as fy_starts_at, FY.ends_at_en as fy_ends_at, FY.code_np as fy_code_np')
                            ->from($this->table_name . ' as Q')
                            ->join('master_fiscal_yrs FY', 'FY.id = Q.fiscal_yr_id')
                            ->order_by('Q.id', 'desc')
                            ->get()
                            ->result();
            $this->write_cache($list, 'fy_quarter_all', CACHE_DURATION_MONTH);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_by_fiscal_year( $fiscal_yr_id )
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'fy_quarter_' . $fiscal_yr_id;
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $list = $this->db->select('Q.id, Q.fiscal_yr_id, Q.quarter, Q.starts_at, Q.ends_at, FY.starts_at_en as fy_starts_at, FY.ends_at_en as fy_ends_at, FY.code_np as fy_code_np')
                            ->from($this->table_name . ' as Q')
                            ->join('master_fiscal_yrs FY', 'FY.id = Q.fiscal_yr_id')
                            ->where('Q.fiscal_yr_id', $fiscal_yr_id)
                            ->get()
                            ->result();
            $this->write_cache($list, $cache_name, CACHE_DURATION_MONTH);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown( $fiscal_yr_id )
    {
        $records = $this->get_by_fiscal_year($fiscal_yr_id);
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = fiscal_year_quarters_dropdown()[$record->quarter];
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
            'fy_quarter_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function log_activity($id, $action = 'C')
    {
        return TRUE;
    }
}