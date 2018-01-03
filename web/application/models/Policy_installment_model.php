<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_installment_model extends MY_Model
{
    protected $table_name = 'dt_policy_installments';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
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
                $cache_key = 'ptxi_bytxn_' . $policy_transaction_record->id;
                $this->delete_cache($cache_key);

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
     * Get single record
     *
     * @param int $id
     * @return object
     */
    public function get($id)
    {
        return $this->db->select('PTI.*, P.branch_id')
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
            $rows = $this->_rows($policy_transaction_id);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

        private function _rows($policy_transaction_id)
        {
            $this->db->select('PTI.*, P.branch_id, P.code')
                    ->from($this->table_name . ' AS PTI')
                    ->join('dt_policy_transactions PTXN', 'PTXN.id = PTI.policy_transaction_id')
                        ->join('dt_policies P', 'P.id = PTXN.policy_id')
                    ->where('PTI.policy_transaction_id', $policy_transaction_id);

            /**
             * Apply User Scope
             */
            $this->dx_auth->apply_user_scope('P');

            // Get the damn result
            return $this->db->order_by('PTI.flag_first', 'DESC')
                            ->get()->result();
        }


    // ----------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache(  )
    {
        $cache_names = [
            'ptxi_bytxn_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
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