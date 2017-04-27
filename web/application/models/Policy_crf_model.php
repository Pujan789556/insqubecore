<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_crf_model extends MY_Model
{
    protected $table_name = 'dt_policy_crf';

    protected $set_created = false;
    protected $set_modified = false;
    protected $log_user = false;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['policy_txn_id'];
    protected $fields               = ['policy_txn_id', 'transfer_type', 'computation_type', 'amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_stamp_duty', 'other_cost_fields', 'cost_calculation_table'];

    /**
     * Fields that are copied from ( directly or after some computation )
     * CRF to TXN Table
     */
    public static $fields_to_txn_transfer = ['amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_stamp_duty', 'cost_calculation_table'];

    protected $validation_rules = [];
    protected $skip_validation  = TRUE; // No need to validate on Model

    /**
     * Protect Default Records?
     */
    public static $protect_default  = FALSE;
    public static $protect_max_id   = 0;

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
     * Reset Policy Cost Reference for Current Policy Transaction Record
     *
     * !!!IMPORTANT: The Policy Transaction Record MUST NOT be ACTIVE.
     *
     *
     * @param integer $policy_txn_id
     * @return bool
     */
    public function reset($policy_txn_id)
    {
        // Record Exist?
        $record = parent::find_by(['policy_txn_id' => $policy_txn_id]);
        if( $record )
        {
            $nullable_fields = ['amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_stamp_duty', 'other_cost_fields', 'cost_calculation_table'];

            $reset_data = [];

            foreach ($nullable_fields as $field)
            {
                 $reset_data[$field] = NULL;
            }
            return $this->db->where('policy_txn_id', $policy_txn_id)
                     ->update($this->table_name, $reset_data);
        }

        return FALSE;
    }

    // ----------------------------------------------------------------


    /**
     * Save Cost Reference for a Policy Transaction
     *
     * @param int $policy_txn_id
     * @param array $data
     * @return bool
     */
    public function save($policy_txn_id, $data)
    {
        return parent::update_by(['policy_txn_id' => $policy_txn_id], $data);
    }

    // --------------------------------------------------------------------


    /**
     * Get the Policy Cost Reference Table.
     *
     * You can insert a default blank record if not found.
     * Useful for Fresh/Renewal/Transactional Endorsement Update Premium
     *
     * @param integer $policy_txn_id
     * @param bool $flag_insert_if_not_found    Insert a default blank record if record not found
     * @return object
     */
    public function get( $policy_txn_id )
    {
        return parent::find_by(['policy_txn_id' => $policy_txn_id]);
    }

    // ----------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
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
        // You can not directly delete these records
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
        //     'module' => 'policy_txn',
        //     'module_id' => $id,
        //     'action' => $action
        // ];
        // return $this->activity->save($activity_log);
    }
}