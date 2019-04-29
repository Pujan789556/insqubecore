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

    protected $fields = ["id", "code_np", "code_en", "starts_at_en", "ends_at_en", "starts_at_np", "ends_at_np", "settings", "created_at", "created_by", "updated_at", "updated_by"];

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
     * Get a Fiscal year Record
     *
     * @param integer $id
     * @return object
     */
    public function get($id)
    {
        /**
         * CACHE first
         */
        $cache_key = 'fy_id_' . $id;
        $record = $this->get_cache($cache_key);
        if(!$record)
        {
            $record = parent::find($id);
            $this->write_cache($record, $cache_key, CACHE_DURATION_WEEK);
        }
        return $record;
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
        $list = $this->get_cache('fy_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'fy_all', CACHE_DURATION_MONTH);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get all the fiscal years till current fiscal year
     * @return type
     */
    public function get_till_current_fy()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $date = date('Y-m-d');
        $cache_var = 'fy_till_' . date('Ymd');
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('FY.*')
                             ->from($this->table_name . ' FY')
                             ->where('FY.starts_at_en <=', $date)
                             ->get()->result();
            $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     *
     * @param string $mode all|till_now   till_now: list till current fiscal year
     */
    public function dropdown($mode = 'all')
    {
        if($mode == 'all')
        {
            $records = $this->get_all();
        }
        else
        {
            $records = $this->get_till_current_fy();
        }

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
            'fy_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }
}