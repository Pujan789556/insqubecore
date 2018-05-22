<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bs_report_model extends MY_Model
{
    protected $table_name = 'dt_bs_reports';

    protected $skip_validation = TRUE;

    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = FALSE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'type', 'fiscal_yr_id', 'fy_quarter_month', 'filename', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0;

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

    public function save($data)
    {
        $where = [
            'type'              => $data['type'],
            'fiscal_yr_id'      => $data['fiscal_yr_id'],
            'fy_quarter_month'  => $data['fy_quarter_month'],
        ];

        if( !$this->check_duplicate($where) )
        {
            return parent::insert($data);
        }
        return TRUE;
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
	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
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
}