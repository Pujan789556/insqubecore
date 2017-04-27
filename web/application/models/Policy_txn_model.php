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

        $this->load->model('policy_crf_model');
    }

    // ----------------------------------------------------------------

    /**
     * Reset Current Policy Transaction Record
     *
     * This function will reset the current transaction record of the
     * specified policy to default(empty/null).
     *
     * It will further reset the cost reference record if any
     *
     * !!!IMPORTANT: The record MUST NOT be ACTIVE.
     *
     *
     * @param integer $policy_id
     * @return bool
     */
    public function reset($policy_id)
    {
        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;

        /**
         * Disable DB Debugging
         */
        $this->db->db_debug = FALSE;
        $this->db->trans_start();

                $this->_reset($policy_id);

        /**
         * Complete transactions or Rollback
         */
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $transaction_status = FALSE;
        }

        /**
         * Restore DB Debug Configuration
         */
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $transaction_status;
    }

        // --------------------------------------------------------------------

        private function _reset($policy_id)
        {
            $record = $this->get_current_txn_by_policy($policy_id);

            if(!$record)
            {
                throw new Exception("Exception [Model: Policy_txn_model][Method: _reset()]: Current TXN record could not be found.");
            }

            /**
             * Task 1: Reset Policy Transaction Record
             */
            // !!!NOTE: 'amt_sum_insured' can not be emptied as it is updated when policy is updated
            $nullable_fields = ['txn_date', 'amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_comissionable', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat', 'cost_calculation_table', 'txn_details', 'remarks', 'flag_ri_approval'];

            $reset_data = [];

            foreach ($nullable_fields as $field)
            {
                 $reset_data[$field] = NULL;
            }
            $this->db->where('id', $record->id)
                     ->update($this->table_name, $reset_data);

            /**
             * Task 2: Reset Policy Cost Reference Record
             */
            $this->policy_crf_model->reset($record->id);
        }

    // --------------------------------------------------------------------

    /**
     * RI Approval Required?
     *
     * Check if the transaction required RI Approval.
     *
     * @param integer|object $policy_id_or_record   Policy ID or Transaction Record
     * @return bool
     */
    public function ri_approval_required($policy_id_or_record)
    {
        $record = is_numeric($policy_id_or_record) ? $this->get_current_txn_by_policy( (int)$policy_id_or_record ): $policy_id_or_record;
        $approval_required  = FALSE;

        if( $record )
        {
            $approval_required = (int)$record->flag_ri_approval === IQB_FLAG_ON;
        }

        return $approval_required;
    }

    // --------------------------------------------------------------------

    /**
     * RI Approved?
     *
     * Check if the transaction is RI Approved (if required).
     * If RI approval is not required for this txn record,
     * it will simply return TRUE.
     *
     *
     * @param integer|object $policy_id_or_record   Policy ID or Transaction Record
     * @return bool
     */
    public function ri_approved( $record )
    {
        $record = is_numeric($policy_id_or_record) ? $this->get_current_txn_by_policy( (int)$policy_id_or_record ): $policy_id_or_record;
        $approved  = TRUE;

        // First check if it requires RI Approval
        if( $this->ri_approval_required($record) )
        {
            // Transaction status must be "RI Approved"
            $approved = $record->status === IQB_POLICY_TXN_STATUS_RI_APPROVED;
        }

        return $approved;
    }

    // --------------------------------------------------------------------

    public function is_editable($status)
    {
        return $status === IQB_POLICY_TXN_STATUS_DRAFT;
    }

    // --------------------------------------------------------------------

    /**
     * Update Policy Transaction Status
     *
     * !!! NOTE: We can only change status of current Transaction Record
     *
     * @param integer $policy_id Policy ID
     * @param alpha $to_status_flag Status Code
     * @return bool
     */
    public function update_status($policy_id, $to_status_flag)
    {
        $record = $this->get_current_txn_by_policy($policy_id);
        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_txn_model][Method: update_status()]: Current TXN record could not be found.");
        }

        // Status Qualified?
        if( !$this->_status_qualifies($record->status, $to_status_flag) )
        {
            throw new Exception("Exception [Model:Policy_txn_model][Method: update_status()]: Current Status does not qualify to upgrade/downgrade.");
        }

        $data = [
            'status'        => $to_status_flag,
            'updated_by'    => $this->dx_auth->get_user_id(),
            'updated_at'    => $this->set_date()
        ];

        switch($to_status_flag)
        {
            /**
             * Reset Verified date/user to NULL
             */
            case IQB_POLICY_TXN_STATUS_DRAFT:
                $data['verified_at'] = NULL;
                $data['verified_by'] = NULL;
                break;

            /**
             * Update Verified date/user and Reset ri_approved date/user to null
             */
            case IQB_POLICY_TXN_STATUS_VERIFIED:
                $data['verified_at'] = $this->set_date();
                $data['verified_by'] = $this->dx_auth->get_user_id();
                $data['ri_approved_at'] = NULL;
                $data['ri_approved_by'] = NULL;
            break;

            /**
             * Update RI Approved date/user
             */
            case IQB_POLICY_TXN_STATUS_RI_APPROVED:
                $data['ri_approved_at'] = $this->set_date();
                $data['ri_approved_by'] = $this->dx_auth->get_user_id();
            break;

            /**
             * Update approved date/user
             */
            case IQB_POLICY_TXN_STATUS_ACTIVE:
                $data['approved_at'] = $this->set_date();
                $data['approved_by'] = $this->dx_auth->get_user_id();
            break;

            default:
                break;
        }

        return $this->_to_status($record->id, $data);
    }

        // ----------------------------------------------------------------

        private function _status_qualifies($current_status, $to_status)
        {
            $flag_qualifies = FALSE;

            switch ($to_status)
            {
                case IQB_POLICY_TXN_STATUS_DRAFT:
                    $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_VERIFIED;
                    break;

                case IQB_POLICY_TXN_STATUS_VERIFIED:
                    $flag_qualifies = in_array($current_status, [IQB_POLICY_TXN_STATUS_DRAFT, IQB_POLICY_TXN_STATUS_RI_APPROVED]);
                    break;

                case IQB_POLICY_TXN_STATUS_RI_APPROVED:
                    $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_VERIFIED;
                    break;

                case IQB_POLICY_TXN_STATUS_ACTIVE:
                    $flag_qualifies = in_array($current_status, [IQB_POLICY_TXN_STATUS_VERIFIED, IQB_POLICY_TXN_STATUS_RI_APPROVED]);
                    break;

                default:
                    break;
            }
            return $flag_qualifies;
        }

        // ----------------------------------------------------------------

        private function _to_status($id, $data)
        {
            return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
        }

    // --------------------------------------------------------------------

    /**
     * Save Cost Reference Data and Transactional Data
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function save($id, $crf_data, $txn_data)
    {
        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;

        /**
         * Disable DB Debugging
         */
        $this->db->db_debug = FALSE;
        $this->db->trans_start();


                /**
                 * Task 1: Update CRF Data
                 */
                $this->policy_crf_model->save($id, $crf_data);

                /**
                 * Task 2: Update TXN Data
                 */
                parent::update($id, $txn_data, TRUE);

        /**
         * Complete transactions or Rollback
         */
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $transaction_status = FALSE;
        }

        /**
         * Restore DB Debug Configuration
         */
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $transaction_status;
    }

    // --------------------------------------------------------------------

    /**
     * Get Current Policy Transaction Record for Supplied Policy
     *
     * @param int $policy_id
     * @return object
     */
    public function get_current_txn_by_policy($policy_id)
    {
        $where = [
            'PTXN.policy_id'    => $policy_id,
            'PTXN.flag_current' => IQB_FLAG_ON
        ];
        return $this->db->select('PTXN.*')
                        ->from($this->table_name . ' AS PTXN')
                        ->where($where)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Fresh/Renewal Transaction Record of the Policy
     *
     * If the policy is renewed, we need renewed record or fresh
     * txn record
     *
     * @param int $policy_id
     * @return object
     */
    public function get_fresh_renewal_by_policy($policy_id, $txn_type)
    {
        if( !in_array($txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
        {
            throw new Exception("Exception [Model:Policy_txn_model][Method: get_fresh_renewal_by_policy()]: Invalid Transaction Type.");
        }
        $where = [
            'PTXN.policy_id'    => $policy_id,
            'PTXN.txn_type'     => $txn_type
        ];
        return $this->db->select('PTXN.*')
                        ->from($this->table_name . ' AS PTXN')
                        ->where($where)
                        ->get()->row();
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