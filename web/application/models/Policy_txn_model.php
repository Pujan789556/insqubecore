<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_txn_model extends MY_Model
{
    protected $table_name = 'dt_policy_txn';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'policy_id', 'txn_type', 'txn_date', 'amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_comissionable', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat', 'cost_calculation_table', 'txn_details', 'remarks', 'flag_ri_approval', 'flag_current', 'status', 'ri_approved_at', 'ri_approved_by', 'created_at', 'created_by', 'verified_at', 'verified_by', 'approved_at', 'approved_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

    // protected $skip_validation = TRUE; // No need to validate on Model

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

        // Required Helpers/Configurations
        $this->load->config('policy');
        $this->load->helper('policy');
        $this->load->helper('object');
    }

    // ----------------------------------------------------------------

    public function reset($policy_id)
    {
        // !!!NOTE: 'amt_sum_insured' can not be emptied as it is updated when policy is updated
        $nullable_fields = ['txn_date', 'amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_comissionable', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat', 'cost_calculation_table', 'txn_details', 'remarks', 'flag_ri_approval'];

        $reset_data = [];

        foreach ($nullable_fields as $field)
        {
             $reset_data[$field] = NULL;
        }
        return $this->db->where('policy_id', $policy_id)
                 ->update($this->table_name, $reset_data);
    }

    // --------------------------------------------------------------------


    // --------------------------------------------------------------------

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
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        $record = $this->row($id);
        if(!$record)
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);
            $this->log_activity($id, 'D');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
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
        $action = is_string($action) ? $action : 'C';

        // Save Activity Log
        $activity_log = [
            'module' => 'policy_txn',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}