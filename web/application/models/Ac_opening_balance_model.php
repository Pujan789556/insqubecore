<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_opening_balance_model extends MY_Model
{
    protected $table_name = 'ac_opening_balances';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'account_id', 'fiscal_yr_id', 'dr', 'cr', 'balance', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 800;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Helper
        $this->load->helper('account');
    }



    // ----------------------------------------------------------------

    /**
     * Check Duplicate
     *
     * @param array $where
     * @param integer|null $id
     * @return integer
     */
    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List by Account Group ID
     *
     * @param integer $account_group_id
     * @param bool $include_group_path
     * @return array
     */
    public function by_account_fiscal_yr($account_id, $fiscal_yr_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'ac_ob_' . $account_id . '_' . $fiscal_yr_id;
        $where = [
            'ACOB.account_id' => $account_id,
            'ACOB.fiscal_yr_id' => $fiscal_yr_id
        ];

        $record = $this->get_cache($cache_name);
        if(!$record)
        {
            $record = $this->db->select('ACOB.*')
                                 ->from($this->table_name . ' as ACOB')
                                 ->where($where)
                                 ->get()->row();

            if($record)
            {
                $this->write_cache($record, $cache_name, CACHE_DURATION_DAY);
            }
        }
        return $record;
    }



	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'ac_ob_*'
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

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        return TRUE;
        // $action = is_string($action) ? $action : 'C';
        // // Save Activity Log
        // $activity_log = [
        //     'module'    => 'ac_account',
        //     'module_id' => $id,
        //     'action'    => $action
        // ];
        // return $this->activity->save($activity_log);
    }
}