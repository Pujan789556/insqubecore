<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_installment_model extends MY_Model
{
    protected $table_name = 'dt_policy_installments';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'policy_transaction_id', 'installment_date', 'percent', 'amt_total_premium', 'amt_pool_premium', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat', 'flag_first', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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


        // Set validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $this->validation_rules = [

            [
                'field' => 'installment_date[]',
                'label' => 'Installment Date',
                'rules' => 'trim|required|valid_date',
                '_key'  => 'installment_date',
                '_type'             => 'date',
                '_default'      => date('Y-m-d'),
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_required' => true,
                '_show_label' => false
            ],
            [
                'field' => 'percent[]',
                'label' => 'Installment(%)',
                '_key'  => 'percent',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]|callback__cb_installment_complete',
                '_type' => 'text',
                '_default' => 100,
                '_required' => true,
                '_show_label' => false
            ],
        ];
    }

    // --------------------------------------------------------------------

    /**
     * Build Installments for a Policy Transaction
     *
     * @param object $policy_transaction_record
     * @param array $installment_data
     * @return bool
     */
    public function build($policy_transaction_record, $installment_data)
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
                 * Task 1: Delete Old installments
                 */
                parent::delete_by(['policy_transaction_id' => $policy_transaction_record->id]);

                /**
                 * Task 2: Build Batch Insert For Installments
                 */
                $dates      = $installment_data['dates'];
                $percents   = $installment_data['percents'];

                $batch_data = [];
                for($i = 0; $i < count($dates); $i++ )
                {
                    $installment_date       = $dates[$i];
                    $percent                = $percents[$i];

                    $amt_total_premium      = ( $policy_transaction_record->amt_total_premium * $percent ) / 100.00;

                    $amt_pool_premium       = $policy_transaction_record->amt_pool_premium
                                                ? ( $policy_transaction_record->amt_pool_premium * $percent ) / 100.00 : NULL;

                    $amt_agent_commission   = $policy_transaction_record->amt_agent_commission
                                                ? ( $policy_transaction_record->amt_agent_commission * $percent ) / 100.00 : NULL;

                    $amt_stamp_duty         = $i === 0 ? $policy_transaction_record->amt_stamp_duty : NULL;

                    // Compute VAT
                    $taxable_amount = $amt_total_premium + floatval($amt_stamp_duty);
                    $this->load->helper('account');
                    $amt_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);

                    $batch_data[] = [
                        'policy_transaction_id' => $policy_transaction_record->id,
                        'installment_date'      => $installment_date,
                        'percent'               => $percent,
                        'amt_total_premium'     => $amt_total_premium,
                        'amt_pool_premium'      => $amt_pool_premium,
                        'amt_agent_commission'  => $amt_agent_commission,
                        'amt_stamp_duty'        => $amt_stamp_duty,
                        'amt_vat'               => $amt_vat,
                        'flag_first'            => $i === 0 ? IQB_FLAG_ON : IQB_FLAG_OFF
                    ];
                }
                parent::insert_batch($batch_data, TRUE);

                /**
                 * Task 3: Delete Cache
                 */
                $cache_keys = [
                    'ptxi_bytxn_' . $policy_transaction_record->id,
                    'ptxi_fst_stts_bytxn_' . $policy_transaction_record->id,
                    'ptxi_bypolicy_' . $policy_transaction_record->policy_id
                ];
                $this->clear_cache($cache_keys);

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
     * Update Policy Transaction Status
     *
     * !!! NOTE: We can only change status of current Transaction Record
     *
     * @param integer $policy_id_or_txn_record Policy ID or Transaction Record
     * @param alpha $to_status_flag Status Code
     * @return bool
     */
    public function update_status($id_or_record, $to_status_flag)
    {
        // Get the Policy Record
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: update_status()]: Installment record could not be found.");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, $to_status_flag) )
        {
            throw new Exception("Exception [Model:Policy_installment_model][Method: update_status()]: Current Status does not qualify to upgrade/downgrade.");
        }

        $data = [
            'status'        => $to_status_flag,
            'updated_by'    => $this->dx_auth->get_user_id(),
            'updated_at'    => $this->set_date()
        ];

        /**
         * Update Status and Clear Cache Specific to this Policy ID
         */
        if( $this->_to_status($record->id, $data) )
        {

            /**
             * DO RI Distribution on Paid
             */
            if( $to_status_flag == IQB_POLICY_INSTALLMENT_STATUS_PAID )
            {
                RI__distribute( $record->id );
            }

            /**
             * Delete Caches
             */
            $cache_keys = [
                'ptxi_bytxn_' . $record->id,
                'ptxi_fst_stts_bytxn_' . $record->policy_transaction_id,
                'ptxi_bypolicy_' . $record->policy_id
            ];
            $this->clear_cache($cache_keys);

            return TRUE;
        }

        return FALSE;
    }

    // ----------------------------------------------------------------

    public function status_qualifies($current_status, $to_status)
    {
        $flag_qualifies = FALSE;

        switch ($to_status)
        {
            case IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED:
                $flag_qualifies = $current_status === IQB_POLICY_INSTALLMENT_STATUS_DRAFT;
                break;

            case IQB_POLICY_TXN_STATUS_INVOICED:
                $flag_qualifies = $current_status === IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED;
                break;

            // For non-txnal endorsement, its from approved
            case IQB_POLICY_INSTALLMENT_STATUS_PAID:
                $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_INVOICED;
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
     * Get the status of first installment belonging to this policy transaction
     *
     * @param int $policy_transaction_id
     * @return char
     */
    public function first_installment_status($policy_transaction_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ptxi_fst_stts_bytxn_' . $policy_transaction_id;
        $status       = $this->get_cache($cache_var);
        if(!$status)
        {
            $status = $this->db->select('PTI.status')
                        ->from($this->table_name . ' AS PTI')
                        ->where('PTI.policy_transaction_id', $policy_transaction_id)
                        ->where('PTI.flag_first', IQB_FLAG_ON)
                        ->get()->row()->status;
            if($status)
            {
                $this->write_cache($status, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $status;
    }


    // --------------------------------------------------------------------


    /**
     * Get single record
     *
     * @param int $id
     * @return object
     */
    public function get($id)
    {
        return $this->db->select(

                                // Policy Installment Table Data
                                'PTI.*, '.

                                // Policy Table Data
                                'P.id AS policy_id, P.branch_id, P.code AS policy_code, P.portfolio_id, '.

                                // Policy Transaction Table Data
                                'PTXN.txn_type,
                                    PTXN.amt_sum_insured as policy_transaction_amt_sum_insured,
                                    PTXN.flag_current as policy_transaction_flag_current,
                                    PTXN.status AS policy_transaction_status,
                                    PTXN.flag_ri_approval AS policy_transaction_flag_ri_approval'
                            )
                        ->from($this->table_name . ' AS PTI')
                        ->join('dt_policy_transactions PTXN', 'PTXN.id = PTI.policy_transaction_id')
                        ->join('dt_policies P', 'P.id = PTXN.policy_id')
                        ->where('PTI.id', $id)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get all records belonging to single Policy Transaction
     *
     *
     * @param int $id
     * @return array
     */
    public function get_many_by_policy_transaction($policy_transaction_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ptxi_bytxn_'.$policy_transaction_id;
        $rows       = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows(['PTI.policy_transaction_id' => $policy_transaction_id]);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

    // ----------------------------------------------------------------



    /**
     * Get All Transactions Rows for Supplied Policy
     *
     * @param int $policy_id
     * @return type
     */
    public function get_many_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'ptxi_bypolicy_'.$policy_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows(['P.id' => $policy_id]);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

    // --------------------------------------------------------------------

        /**
         * Get Rows from Database
         *
         * @param array $where
         * @return mixed
         */
        private function _rows($where)
        {
            $this->db->select('PTI.*, P.branch_id, P.code AS policy_code, PTXN.flag_current as policy_transaction_flag_current, PTXN.status AS policy_transaction_status, PTXN.flag_ri_approval AS policy_transaction_flag_ri_approval')
                    ->from($this->table_name . ' AS PTI')
                    ->join('dt_policy_transactions PTXN', 'PTXN.id = PTI.policy_transaction_id')
                    ->join('dt_policies P', 'P.id = PTXN.policy_id')
                    ->where($where);

            /**
             * Apply User Scope
             */
            $this->dx_auth->apply_user_scope('P');

            // Get the damn result (Latest Transaction with first installment date order)
            return $this->db->order_by('PTI.installment_date', 'ASC')
                            ->order_by('PTI.policy_transaction_id', 'DESC')
                            ->get()->result();
        }

    // --------------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache( $data=null )
    {
        /**
         * If no data supplied, delete all caches
         */
        if( !$data )
        {
            $cache_names = [
                'ptxi_bytxn_*',
                'ptxi_fst_stts_bytxn_*',
                'ptxi_bypolicy_*'
            ];
        }
        else
        {
            /**
             * If data supplied, we only delete the supplied
             * caches
             */
            $cache_names = is_array($data) ? $data : [$data];
        }
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Delete a Record
     *
     * We do not allow direct delete of a installment
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
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
        $action = is_string($action) ? $action : 'C';

        // Save Activity Log
        $activity_log = [
            'module' => 'policy_installments',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}