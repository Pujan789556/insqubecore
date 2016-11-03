<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fiscal_year_model extends MY_Model
{

    protected $table_name = 'master_fiscal_yrs';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "code_np", "code_en", "starts_at_en", "ends_at_en", "starts_at_np", "ends_at_np", "created_at", "created_by", "updated_at", "updated_by"];

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
     * Get Current Fiscal Year
     *
     * Get the current fiscal year based on the date today.
     * We cache the data for a 6 hours. On every six hours, it is refreshed
     * automatically on the next call.
     *
     * @param void
     * @return object
     */
    public function get_current_fiscal_year( $today = NULL )
    {
        $record = NULL;

        /**
         * Caching Strategy
         *
         * On a new day start, we add anohter cache variable to tell that this is a new
         * cache for the next day for the first quarter of the day.
         * Any request to this method will first check the existance of this variable.
         * If no variable is found, a fresh cache is created along with this variable.
         *
         * For other quarters, we don't need this monitoring variable.
         */
        $hour = (int)date('H');
        if( $hour <= 6 )
        {
            // Check for monotoring Variable
            $flag_fresh_fiscal_yr = $this->get_cache('flag_fresh_fiscal_yr');
            if( !$flag_fresh_fiscal_yr )
            {
                // Delete cache first
                $this->delete_cache('fiscal_yr_current');

                // Get the record and set cache
                $record = $this->_get_current_fiscal_year($today);
                $this->write_cache($record, 'fiscal_yr_current', CACHE_DURATION_6HRS);
                $this->write_cache('y', 'flag_fresh_fiscal_yr', CACHE_DURATION_6HRS);
            }

        }

        /**
         * Regular Case
         * Get Cached Result, If no, cache the query result
         */
        if(!$record)
        {
            $record = $this->_get_current_fiscal_year($today);
            $this->write_cache($record, 'fiscal_yr_current', CACHE_DURATION_6HRS);
        }
        return $record;
    }
        private function _get_current_fiscal_year( $today = NULL )
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
        $list = $this->get_cache('fiscal_yrs_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'fiscal_yrs_all', CACHE_DURATION_MONTH);
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
            'fiscal_yrs_all',
            'fiscal_yr_current'
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