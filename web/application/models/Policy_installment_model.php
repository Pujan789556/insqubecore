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

    protected $fields = ['id', 'endorsement_id', 'fiscal_yr_id', 'fy_quarter', 'installment_date', 'type', 'percent', 'amt_basic_premium', 'amt_pool_premium', 'amt_agent_commission', 'amt_ri_commission', 'amt_stamp_duty', 'amt_transfer_fee', 'amt_transfer_ncd', 'amt_cancellation_fee', 'amt_vat', 'flag_first', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
     * Build Installments for a Endorsement
     *
     * @param object $endorsement_record
     * @param array $installment_data
     * @return bool
     */
    public function build($endorsement_record, $installment_data)
    {
        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;

        /**
         * Disable DB Debugging
         */
        $this->db->db_debug = TRUE;
        $this->db->trans_start();

                /**
                 * Task 1: Delete Old installments
                 */
                parent::delete_by(['endorsement_id' => $endorsement_record->id]);

                /**
                 * Task 2: Build Batch Insert For Installments
                 */
                $dates              = $installment_data['dates'];
                $percents           = $installment_data['percents'];
                $installment_type   = $installment_data['installment_type'];

                // First Installment Only Fields
                $first_instlmnt_only_fields = ['amt_stamp_duty', 'amt_transfer_fee', 'amt_transfer_ncd', 'amt_cancellation_fee', 'amt_ri_commission'];

                $batch_data = [];
                for($i = 0; $i < count($dates); $i++ )
                {
                    $installment_date       = $dates[$i];
                    $percent                = $percents[$i];

                    $p_val = bcdiv($percent, 100.00, IQB_AC_DECIMAL_PRECISION);

                    $amt_basic_premium      = bcmul( $endorsement_record->amt_basic_premium, $p_val, IQB_AC_DECIMAL_PRECISION);
                    $amt_pool_premium       = $endorsement_record->amt_pool_premium
                                                ? bcmul( $endorsement_record->amt_pool_premium, $p_val, IQB_AC_DECIMAL_PRECISION): 0.00;
                    $amt_agent_commission   = $endorsement_record->amt_agent_commission
                                                ? bcmul( $endorsement_record->amt_agent_commission , $p_val, IQB_AC_DECIMAL_PRECISION) : NULL;


                    /**
                     * Compute Taxable Amount
                     */
                    $taxable_amount = bcadd($amt_basic_premium, $amt_pool_premium, IQB_AC_DECIMAL_PRECISION);


                    /**
                     * Items for 1st Installment Only
                     *
                     *  - Stamp Duty
                     *  - Cancellation Fee
                     *  - Transfer Fee
                     *  - No calaim Discount (return)
                     *  - RI Commission
                     */
                    $fst_inst_only_data = [];
                    if($i === 0 )
                    {
                        foreach($first_instlmnt_only_fields as $field)
                        {
                            $value = $endorsement_record->{$field} ?? NULL;
                            $fst_inst_only_data[$field] = $value;

                            // Update Taxable Amount
                            if($value)
                            {
                                $taxable_amount = bcadd($taxable_amount, $value, IQB_AC_DECIMAL_PRECISION);
                            }
                        }
                    }
                    else
                    {
                        foreach($first_instlmnt_only_fields as $field)
                        {
                            $fst_inst_only_data[$field] =  NULL;
                        }
                    }

                    /**
                     * Let's Compute VAT
                     *
                     * NOTE: IF POlicy is FAC-Inward, NO VAT Computation
                     */
                    $amt_vat = 0.00;
                    if( in_array(
                            $endorsement_record->policy_category,
                            [IQB_POLICY_CATEGORY_REGULAR, IQB_POLICY_CATEGORY_CO_INSURANCE])
                    )
                    {
                        $this->load->helper('account');
                        $amt_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);
                    }


                    $batch_data[] = array_merge( $fst_inst_only_data, [
                        'endorsement_id'        => $endorsement_record->id,
                        'installment_date'      => $installment_date,
                        'type'                  => $installment_type,
                        'percent'               => $percent,
                        'amt_basic_premium'     => $amt_basic_premium,
                        'amt_pool_premium'      => $amt_pool_premium,
                        'amt_agent_commission'  => $amt_agent_commission,
                        'amt_vat'               => $amt_vat,
                        'flag_first'            => $i === 0 ? IQB_FLAG_ON : IQB_FLAG_OFF
                    ]);
                }

                parent::insert_batch($batch_data, TRUE);

                /**
                 * Task 3: Delete Cache
                 */
                $cache_keys = [
                    'ptxi_bytxn_' . $endorsement_record->id,
                    'ptxi_fst_stts_bytxn_' . $endorsement_record->id,
                    'ptxi_bypolicy_' . $endorsement_record->policy_id
                ];
                $this->clear_cache($cache_keys);

        /**
         * Complete transactions or Rollback
         */
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: build()]: Instellment(s) could not be saved.");
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
     * Perform Policy Installment Post Paid Tasks
     *
     * Tasks:
     *
     * 1. Update Fiscal Year, FY Quarter and Installment Date
     * 2. Update Status to Paid
     * 3. RI Distribute
     *
     *
     *
     * @param int|object $id_or_record
     * @return mixed
     */
    public function post_paid_tasks($id_or_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: post_paid_tasks()]: Installment record could not be found.");
        }

        $installment_date = date('Y-m-d');

        $fy_record  = $this->fiscal_year_model->get_fiscal_year($installment_date);
        if(!$fy_record)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: post_paid_tasks()]: Fiscal Year not found for supplied installment date ({$installment_date}).");
        }

        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($installment_date);
        if(!$fy_quarter)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: post_paid_tasks()]: Fiscal Year Quarter not found for supplied installment date ({$installment_date}).");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, IQB_POLICY_INSTALLMENT_STATUS_PAID) )
        {
            throw new Exception("Exception [Model:Policy_installment_model][Method: post_paid_tasks()]: Current Status does not qualify to upgrade/downgrade.");
        }

        /**
         * Prepare Data
         */
        $data = [
            'installment_date'  => $installment_date,
            'fiscal_yr_id'      => $fy_record->id,
            'fy_quarter'        => $fy_quarter->quarter,
            'status'            => IQB_POLICY_INSTALLMENT_STATUS_PAID
        ];


        /**
         * Update Status and Clear Cache Specific to this Policy ID
         */
        if( $this->_update($record->id, $data) )
        {

            /**
             * DO RI Distribution on Paid
             */
            $this->load->helper('ri');

            /**
             * RI__distribute - Fresh/Renewal Transaction's First Installment
             * RI__distribute_endorsement - All other transaction or installments
             */
            if(
                _ENDORSEMENT_is_first($record->txn_type)
                    &&
                $record->flag_first == IQB_FLAG_ON
            )
            {
                RI__distribute( $record->id );
            }

            /**
             * Only Premium Computable Endorsement has RI Distribution
             * i.e.
             *  - Premium Upgrade
             *  - Premium Refund
             */
            else if( _ENDORSEMENT_is_premium_computable_by_type($record->txn_type) )
            {
                RI__distribute_endorsement($record->id);
            }

            /**
             * Delete Caches
             */
            $cache_keys = [
                'ptxi_bytxn_' . $record->id,
                'ptxi_fst_stts_bytxn_' . $record->endorsement_id,
                'ptxi_bypolicy_' . $record->policy_id
            ];
            $this->clear_cache($cache_keys);

            return TRUE;
        }
        else
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: post_paid_tasks()]: Could not update 'Post-Paid Tasks'.");
        }

        return FALSE;
    }

    // ----------------------------------------------------------------

    public function to_vouchered($id_or_record)
    {
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: to_vouchered()]: Installment record could not be found.");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED) )
        {
            throw new Exception("Exception [Model:Policy_installment_model][Method: to_vouchered()]: Current Status does not qualify to upgrade/downgrade.");
        }


        /**
         * Update Status and Clear Cache Specific to this Policy ID
         */
        if( $this->_update($record->id, ['status' => IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED]) )
        {

            /**
             * Delete Caches
             */
            $cache_keys = [
                'ptxi_bytxn_' . $record->id,
                'ptxi_fst_stts_bytxn_' . $record->endorsement_id,
                'ptxi_bypolicy_' . $record->policy_id
            ];
            $this->clear_cache($cache_keys);

            return TRUE;
        }
        else
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: to_vouchered()]: Could not update 'Installment Status'.");
        }
    }

    // ----------------------------------------------------------------

    public function to_invoiced($id_or_record)
    {
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: to_invoiced()]: Installment record could not be found.");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED) )
        {
            throw new Exception("Exception [Model:Policy_installment_model][Method: to_invoiced()]: Current Status does not qualify to upgrade/downgrade.");
        }


        /**
         * Update Status and Clear Cache Specific to this Policy ID
         */
        if( $this->_update($record->id, ['status' => IQB_POLICY_ENDORSEMENT_STATUS_INVOICED]) )
        {

            /**
             * Delete Caches
             */
            $cache_keys = [
                'ptxi_bytxn_' . $record->id,
                'ptxi_fst_stts_bytxn_' . $record->endorsement_id,
                'ptxi_bypolicy_' . $record->policy_id
            ];
            $this->clear_cache($cache_keys);

            return TRUE;
        }
        else
        {
            throw new Exception("Exception [Model: Policy_installment_model][Method: to_invoiced()]: Could not update 'Installment Status'.");
        }
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

            case IQB_POLICY_ENDORSEMENT_STATUS_INVOICED:
                $flag_qualifies = $current_status === IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED;
                break;

            // For non-txnal endorsement, its from approved
            case IQB_POLICY_INSTALLMENT_STATUS_PAID:
                $flag_qualifies = $current_status === IQB_POLICY_ENDORSEMENT_STATUS_INVOICED;
                break;

            default:
                break;
        }
        return $flag_qualifies;
    }

        // ----------------------------------------------------------------

        private function _update($id, $data)
        {
            return parent::update($id, $data, TRUE);
        }


    // --------------------------------------------------------------------

    /**
     * Get the status of first installment belonging to this Endorsement
     *
     * @param int $endorsement_id
     * @return char
     */
    public function first_installment_status($endorsement_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ptxi_fst_stts_bytxn_' . $endorsement_id;
        $status       = $this->get_cache($cache_var);
        if(!$status)
        {
            $status = $this->db->select('PTI.status')
                        ->from($this->table_name . ' AS PTI')
                        ->where('PTI.endorsement_id', $endorsement_id)
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
                                'P.id AS policy_id, P.branch_id, P.code AS policy_code, P.portfolio_id, P.start_date as policy_start_date, P.end_date as policy_end_date, '.

                                // Endorsement Table Data
                                'ENDRSMNT.txn_type,
                                    ENDRSMNT.amt_sum_insured_net as endorsement_amt_sum_insured,
                                    ENDRSMNT.flag_current as endorsement_flag_current,
                                    ENDRSMNT.status AS endorsement_status,
                                    ENDRSMNT.flag_ri_approval AS endorsement_flag_ri_approval'
                            )
                        ->from($this->table_name . ' AS PTI')
                        ->join('dt_endorsements ENDRSMNT', 'ENDRSMNT.id = PTI.endorsement_id')
                        ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                        ->where('PTI.id', $id)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get all records belonging to single Endorsement
     *
     *
     * @param int $id
     * @return array
     */
    public function get_many_by_endorsement($endorsement_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ptxi_bytxn_'.$endorsement_id;
        $rows       = $this->get_cache($cache_var);
        if(!$rows)
        {
            $this->_rows_select(['PTI.endorsement_id' => $endorsement_id]);

            // First Installment Date on top
            $rows = $this->db->order_by('PTI.installment_date', 'ASC')
                            ->get()->result();

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

    // ----------------------------------------------------------------



    /**
     * Get All Endorsements Rows for Supplied Policy
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
        private function _rows_select($where)
        {
            $this->db->select('PTI.*, P.branch_id, P.code AS policy_code, ENDRSMNT.flag_current as endorsement_flag_current, ENDRSMNT.status AS endorsement_status, ENDRSMNT.flag_ri_approval AS endorsement_flag_ri_approval')
                    ->from($this->table_name . ' AS PTI')
                    ->join('dt_endorsements ENDRSMNT', 'ENDRSMNT.id = PTI.endorsement_id')
                    ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                    ->where($where);

            /**
             * Apply User Scope
             */
            $this->dx_auth->apply_user_scope('P');
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
            $this->_rows_select($where);

            // Get the damn result (Latest Transaction with first installment date order)
            return $this->db->order_by('PTI.installment_date', 'DESC')
                            ->order_by('PTI.endorsement_id', 'DESC')
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
}