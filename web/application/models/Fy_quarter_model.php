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

    /**
     * Get fiscal year
     *
     * Get the fiscal year for the specified date
     *
     * @param date $date
     * @return object
     */
    public function get_fiscal_year( $date )
    {
        /**
         * CACHE first
         */
        $cache_key = 'fy_' . date( 'Ymd', strtotime($date) );
        $date = date('Y-m-d', strtotime($date));
        $record = $this->get_cache($cache_key);
        if(!$record)
        {
            $record = $this->_get_fiscal_year($date);
            $this->write_cache($record, $cache_key, CACHE_DURATION_DAY);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    // Get fiscal yer of given date
    private function _get_fiscal_year( $today = NULL )
    {
        $today = $today ?? date('Y-m-d');
        return $this->db->from($this->table_name)
                        ->where('starts_at_en <=', $today)
                        ->where('ends_at_en >=', $today)
                        ->get()->row();
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
            $list = $this->db->select('Q.`id`, Q.`fiscal_yr_id`, Q.`quarter`, Q.`starts_at`, Q.`ends_at`, FY.starts_at_en as fy_starts_at, FY.ends_at_en as fy_ends_at, FY.code_np as fy_code_np')
                            ->from($this->table_name . ' as Q')
                            ->join('master_fiscal_yrs FY', 'FY.id = Q.fiscal_yr_id')
                            ->order_by('Q.id', 'desc')
                            ->get()
                            ->result();
            $this->write_cache($list, 'fy_quarter_all', CACHE_DURATION_MONTH);
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
            $list["{$record->id}"] = $record->code_np . " ({$record->code_en})";
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
            'fy_quarter_all',
            // 'fy_current',
            // 'fy_*'
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

      //    $action = is_string($action) ? $action : 'C';
      //    // Save Activity Log
            // $activity_log = [
            //  'module' => 'fiscal_year',
            //  'module_id' => $id,
            //  'action' => $action
            // ];
            // return $this->activity->save($activity_log);
    }
}