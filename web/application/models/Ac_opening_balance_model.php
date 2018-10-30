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

    protected $fields = ['id', 'account_id', 'fiscal_yr_id', 'party_type', 'party_id', 'dr', 'cr', 'balance', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
     * Get Opening Balance For Supplied Account ( with/out Party)
     *
     * @param int $account_id
     * @param int $fiscal_yr_id
     * @param char|null $party_type
     * @param int|null $party_id
     * @return mixed
     */
    public function by_account_fiscal_yr($account_id, $fiscal_yr_id, $party_type=NULL, $party_id=NULL)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'ac_ob_' . $account_id . '_' . $fiscal_yr_id;
        $where = [
            'OB.account_id' => $account_id,
            'OB.fiscal_yr_id' => $fiscal_yr_id
        ];
        $group_by = ['account_id', 'fiscal_yr_id'];

        /**
         * Party Supplied?
         */
        if( !empty($party_type) && !empty($party_id))
        {
            $where['OB.party_type'] = $party_type;
            $where['OB.party_id'] = $party_id;

            $group_by = array_merge($group_by, ['party_type', 'party_id']);

            $cache_name .= '_' . $party_type . '_' . $party_id;
        }

        $record = $this->get_cache($cache_name);
        if(!$record)
        {
            $select = "OB.account_id, SUM(OB.dr) AS dr, SUM(OB.cr) AS cr, SUM(OB.balance) AS balance";
            $record = $this->db->select($select)
                                 ->from($this->table_name . ' as OB')
                                 ->where($where)
                                 ->group_by($group_by)
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