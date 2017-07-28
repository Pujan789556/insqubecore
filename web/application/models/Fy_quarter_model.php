<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fy_quarter_model extends MY_Model
{

    protected $table_name = 'master_fy_quarters';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
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

        // Load validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $this->validation_rules = [
            [
                'field' => 'fiscal_yr_id',
                'label' => 'Fiscal Year',
                'rules' => 'trim|required|integer|max_length[3]',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                '_default'  => '',
                '_required' => true
            ],
            [
                'field' => 'quarter',
                'label' => 'Quarter',
                'rules' => 'trim|required|integer|exact_length[1]|callback__cb_valid_quarter',
                '_type'     => 'dropdown',
                '_data'     => fiscal_year_quarters_dropdown(),
                '_default'  => '',
                '_required' => true
            ],
            [
                'field' => 'starts_at',
                'label' => 'Start Date',
                'rules' => 'trim|required|valid_date',
                '_type'             => 'date',
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_required' => true
            ],
            [
                'field' => 'ends_at',
                'label' => 'End Date',
                'rules' => 'trim|required|valid_date|callback__cb_valid_dates',
                '_type'             => 'date',
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_required' => true
            ]
        ];
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

    public function get($id)
    {
        return $this->db->select('Q.id, Q.fiscal_yr_id, Q.quarter, Q.starts_at, Q.ends_at, FY.starts_at_en as fy_starts_at, FY.ends_at_en as fy_ends_at, FY.code_np as fy_code_np')
                            ->from($this->table_name . ' as Q')
                            ->join('master_fiscal_yrs FY', 'FY.id = Q.fiscal_yr_id')
                            ->where('Q.id', $id)
                            ->get()
                            ->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Quarter for Given Fiscal year's Date
     *
     * @param type $date
     * @return type
     */
    public function get_quarter_by_date($date)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'fy_quarter_' . date('Ymd', strtotime($date));
        $record = $this->get_cache($cache_name);
        if(!$record)
        {
            $where = [
                'Q.starts_at <=' => $date,
                'Q.ends_at >=' => $date
            ];
            $record = $this->db->select('Q.id, Q.fiscal_yr_id, Q.quarter, Q.starts_at, Q.ends_at')
                            ->from($this->table_name . ' as Q')
                            ->where($where)
                            ->get()->row();
            $this->write_cache($record, $cache_name, CACHE_DURATION_DAY);
        }
        return $record;
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
                        ->count_all_results($this->table_name);
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
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'fy_quarter',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}