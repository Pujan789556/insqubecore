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
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
        	$this->delete_cache('fiscal_yrs_all');
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