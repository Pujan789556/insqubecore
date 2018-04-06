<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Endorsement_model extends MY_Model
{
    protected $table_name = 'dt_endorsements';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'policy_id', 'txn_type', 'txn_date', 'gross_amt_sum_insured', 'gross_amt_total_premium', 'gross_amt_pool_premium', 'gross_amt_commissionable', 'gross_amt_agent_commission', 'net_amt_sum_insured', 'net_amt_total_premium', 'net_amt_pool_premium', 'net_amt_commissionable', 'net_amt_agent_commission', 'amt_stamp_duty', 'amt_transfer_fee', 'amt_transfer_ncd', 'amt_vat', 'computation_basis', 'premium_computation_table', 'cost_calculation_table', 'txn_details', 'remarks', 'transfer_customer_id', 'flag_ri_approval', 'flag_current', 'flag_terminate', 'status', 'audit_policy', 'audit_object', 'audit_customer', 'ri_approved_at', 'ri_approved_by', 'created_at', 'created_by', 'verified_at', 'verified_by', 'updated_at', 'updated_by'];

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
        $txn_type_dropdown          = _ENDORSEMENT_type_eonly_dropdown(FALSE);
        $computation_basis_dropdown = _ENDORSEMENT_computation_basis_dropdown(FALSE);


        $txn_type = (int)$this->input->post('txn_type');
        $cb_required = '';
        if( in_array($txn_type, [IQB_POLICY_TXN_TYPE_PREMIUM_UPGRADE, IQB_POLICY_TXN_TYPE_PREMIUM_REFUND]) )
        {
            $cb_required = 'required|';
        }


        $this->validation_rules = [

            /**
             * Basic Information
             */
            'basic' => [
                [
                    'field' => 'txn_details',
                    'label' => 'Transaction Details (सम्पुष्टि विवरण )',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_id'       => '_txn_details',
                    '_type'     => 'textarea',
                    '_required' => true
                ]
            ],


            /**
             * Computation Basis
             */
            'computation_basis' => [
                [
                    'field' => 'computation_basis',
                    'label' => 'Computation Basis',
                    'rules' => 'trim|'.$cb_required.'integer|exact_length[1]|in_list['. implode( ',', array_keys( $computation_basis_dropdown ) ) .']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $computation_basis_dropdown,
                    '_required' => true
                ]
            ],
        ];
    }

    // ----------------------------------------------------------------

    public function get_v_rules( $txn_type, $formatted = FALSE )
    {
        $txn_type                   = (int)$txn_type;
        $computation_basis_dropdown = _ENDORSEMENT_computation_basis_dropdown(FALSE);
        $v_rules                    = [];


        $basic = [
            [
                'field' => 'txn_details',
                'label' => 'Transaction Details (सम्पुष्टि विवरण )',
                'rules' => 'trim|required|htmlspecialchars',
                '_id'       => '_txn_details',
                '_type'     => 'textarea',
                '_required' => true
            ]
        ];

        $computation_basis = [
            [
                'field' => 'computation_basis',
                'label' => 'Computation Basis',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $computation_basis_dropdown ) ) .']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $computation_basis_dropdown,
                '_required' => true
            ],
            [
                'field' => 'amt_stamp_duty',
                'label' => 'Stamp Duty (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ]
        ];


        switch ($txn_type)
        {
            case IQB_POLICY_TXN_TYPE_GENERAL:
                $v_rules = ['basic' => $basic];
                break;

            case IQB_POLICY_TXN_TYPE_OWNERSHIP_TRANSFER:
                $v_rules = [

                    'basic' => $basic,

                    /**
                     * Customer Information
                     */
                    'customer' => [
                        [
                            'field' => 'transfer_customer_id',
                            'label' => 'Customer',
                            'rules' => 'trim|required|integer|max_length[11]|callback_cb_valid_transfer_customer',
                            '_type'     => 'hidden',
                            '_id'       => 'customer-id',
                            '_required' => true
                        ]
                    ],

                    /**
                     * Transfer Fee
                     */
                    'fees' => [
                        [
                            'field' => 'amt_transfer_fee',
                            'label' => 'Transfer Fee (Rs.)',
                            'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                            '_type'     => 'text',
                            '_required' => true
                        ],
                        [
                            'field' => 'amt_transfer_ncd',
                            'label' => 'No Claim Discount',
                            'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                            '_type'     => 'text',
                            '_default'  => 0.00,
                            '_help_text' => 'This applies only in <strong class="text-red">MOTOR</strong> portfoliios.',
                            '_required' => true
                        ],
                        [
                            'field' => 'amt_stamp_duty',
                            'label' => 'Stamp Duty (Rs.)',
                            'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                            '_type'     => 'text',
                            '_default'  => 0,
                            '_required' => true
                        ]
                    ]
                ];
                break;

            case IQB_POLICY_TXN_TYPE_PREMIUM_UPGRADE:
                $v_rules = ['basic' => $basic, 'computation_basis' => $computation_basis];
                break;

            case IQB_POLICY_TXN_TYPE_PREMIUM_REFUND:
                $v_rules = [
                    'basic'             => $basic,
                    'computation_basis' => $computation_basis,

                    /**
                     * Option To Terminate
                     */
                    'terminate' => [
                        [
                            'field' => 'flag_terminate',
                            'label' => 'Terminate this policy after refund?',
                            'rules' => 'trim|alpha|in_list['.IQB_FLAG_YES.']',
                            '_type'             => 'checkbox',
                            '_checkbox_value'   => IQB_FLAG_YES,
                            '_required'         => true
                        ],
                    ]
                ];
                break;

            case IQB_POLICY_TXN_TYPE_TERMINATE:
                $v_rules = ['basic' => $basic];
                break;

            default:
                # code...
                break;
        }

        if( !$formatted )
        {
            return $v_rules;
        }
        else
        {
            $rules = [];
            foreach($v_rules as $section=>$section_rules)
            {
                $rules = array_merge($rules, $section_rules);
            }
            return $rules;
        }
    }

    // ----------------------------------------------------------------

    /**
     * Reset Current Endorsement Record
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
            $record = $this->get_current_endorsement_by_policy($policy_id);

            if(!$record)
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _reset()]: Current TXN record could not be found.");
            }

            /**
             * Task 1: Reset Endorsement Record
             */
            // !!!NOTE: 'amt_sum_insured' can not be emptied as it is updated when policy is updated
            $nullable_fields = ['txn_date', 'amt_sum_insured', 'amt_total_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat', 'premium_computation_table', 'cost_calculation_table', 'txn_details', 'remarks', 'flag_ri_approval'];

            $reset_data = [];

            foreach ($nullable_fields as $field)
            {
                 $reset_data[$field] = NULL;
            }
            $this->db->where('id', $record->id)
                     ->update($this->table_name, $reset_data);

            /**
             * Task 2: Clear Cache (Speciic to this Policy ID)
             */
            $cache_var = 'endrsmnt_' . $policy_id;
            $this->clear_cache($cache_var);
        }

    // --------------------------------------------------------------------

    /**
     * Update Endorsement Status
     *
     * !!! NOTE: We can only change status of current Transaction Record
     *
     * @param integer $policy_id_or_endorsement_record Policy ID or Transaction Record
     * @param alpha $to_status_flag Status Code
     * @return bool
     */
    public function update_status($policy_id_or_endorsement_record, $to_status_flag)
    {
        // Get the Policy Record
        $record = is_numeric($policy_id_or_endorsement_record) ? $this->get_current_endorsement_by_policy( (int)$policy_id_or_endorsement_record ) : $policy_id_or_endorsement_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: update_status()]: Current TXN record could not be found.");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, $to_status_flag) )
        {
            throw new Exception("Exception [Model:Endorsement_model][Method: update_status()]: Current Status does not qualify to upgrade/downgrade.");
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


            default:
                break;
        }

        /**
         * Update Status and Clear Cache Specific to this Policy ID
         */


        if( $this->_to_status($record->id, $data) )
        {
            /**
             * Post Status Update Tasks
             * ------------------------
             *
             * If this is activate status
             *      - Endorsement Record, we need to commit the changes
             *      - Fresh/Renewal - Activate Policy Record
             *
             */
            if( $to_status_flag === IQB_POLICY_TXN_STATUS_ACTIVE )
            {

                if( in_array($record->txn_type, [IQB_POLICY_TXN_TYPE_ET, IQB_POLICY_TXN_TYPE_EG]) )
                {
                    $this->_commit_endorsement_audit($record);
                }
                else
                {
                    $this->policy_model->update_status($record->policy_id, IQB_POLICY_STATUS_ACTIVE);
                }
            }
            /**
             * If FRESH/RENEWAL transaction and status is verified,
             * Update the Sum Insured Value from Policy Object
             */
            else if($to_status_flag === IQB_POLICY_TXN_STATUS_VERIFIED)
            {
                /**
                 * Task 1: FRESH/RENEWAL - Update sum insured amount
                 * Task 2: FRESH/RENEWAL - RI Approval Constraint
                 */
                $amt_sum_insured = $this->policy_model->get_sum_insured_by_policy_object($record->policy_id);
                if( in_array($record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
                {
                    /**
                     * RI Approval Constraint
                     */
                    $this->load->helper('ri');
                    $flag_ri_approval = RI__compute_flag_ri_approval($record->portfolio_id, $amt_sum_insured);
                    $update_data = [
                        'amt_sum_insured' => $amt_sum_insured,
                        'flag_ri_approval' => $flag_ri_approval
                    ];
                    parent::update($record->id, $update_data, TRUE);
                }
            }


            /**
             * Cleare Caches
             *
             *      1. List of transaction by this policy
             *      2. List of installment by this policy
             */
            $cache_var = 'endrsmnt_' . $record->policy_id;
            $this->clear_cache($cache_var);

            $this->load->model('policy_installment_model');
            $cache_var = 'ptxi_bypolicy_' . $record->policy_id;
            $this->policy_installment_model->clear_cache($cache_var);

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
            case IQB_POLICY_TXN_STATUS_DRAFT:
                $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_VERIFIED;
                break;

            case IQB_POLICY_TXN_STATUS_VERIFIED:
                $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_DRAFT;
                break;

            case IQB_POLICY_TXN_STATUS_RI_APPROVED:
                $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_VERIFIED;
                break;

            case IQB_POLICY_TXN_STATUS_VOUCHERED:
                $flag_qualifies = in_array($current_status, [IQB_POLICY_TXN_STATUS_VERIFIED, IQB_POLICY_TXN_STATUS_RI_APPROVED]);
                break;

            case IQB_POLICY_TXN_STATUS_INVOICED:
                $flag_qualifies = $current_status === IQB_POLICY_TXN_STATUS_VOUCHERED;
                break;

            // For non-txnal endorsement, its from approved
            case IQB_POLICY_TXN_STATUS_ACTIVE:
                $flag_qualifies = in_array($current_status, [IQB_POLICY_TXN_STATUS_VERIFIED, IQB_POLICY_TXN_STATUS_RI_APPROVED, IQB_POLICY_TXN_STATUS_INVOICED]);
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
     * Save Endorsement
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function save_endorsement($data)
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
                 * Task 1: Add Txn Data
                 */
                $id = parent::insert($data, TRUE);

                /**
                 * Task 2: Update flag_current
                 */
                $this->_update_flag_current($id, $data['policy_id']);

                /**
                 * Task 3: Log activity
                 */
                $this->log_activity($id, 'C');

                /**
                 * Task 4: Clear Cache
                 */
                $cache_var = 'endrsmnt_'.$data['policy_id'];
                $this->clear_cache($cache_var);

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

        private function _update_flag_current($id, $policy_id)
        {
            return $this->db->set('flag_current', IQB_FLAG_OFF)
                            ->where('id !=', $id)
                            ->where('policy_id', $policy_id)
                            ->update($this->table_name);
        }

    // --------------------------------------------------------------------

    /**
     * Save Endorsement Audit Data
     * @param int $id   Transaction ID
     * @param string $audit_field Audit Field Name
     * @param string $audit_data JSON Encoded Data
     * @return bool
     */
    public function save_endorsement_audit($id, $audit_field, $audit_data)
    {
        if( !in_array($audit_field, ['audit_policy', 'audit_object', 'audit_customer']))
        {
            return FALSE;
        }
        return $this->db->set($audit_field, $audit_data)
                        ->where('id', $id)
                        ->update($this->table_name);
    }

    // --------------------------------------------------------------------

    /**
     * Commit Endorsement Audit Information
     *
     * On final activation of the status on endorsement of any kind, we need
     * to update changes made on policy, object or customer from audit data
     * hold by this txn record
     *
     * @param object $record
     * @return void
     */
    private function _commit_endorsement_audit($record)
    {

        /**
         * Get Customer ID and Object ID
         */
        $obj_cust = $this->policy_model->get_customer_object_id($record->policy_id);

        /**
         * Task 1: Object Changes
         */
        $audit_object = $record->audit_object ? json_decode($record->audit_object) : NULL;
        if( $audit_object )
        {
            $data = (array)$audit_object->new;
            $this->object_model->commit_endorsement($obj_cust->object_id, $data);
        }

        /**
         * Task 2: Customer Changes
         */
        $audit_customer = $record->audit_customer ? json_decode($record->audit_customer) : NULL;
        if( $audit_customer )
        {
            $this->load->model('customer_model');
            $data = (array)$audit_customer->new;
            $this->customer_model->commit_endorsement($obj_cust->customer_id, $data);
        }

        /**
         * Task 3: Policy Changes
         */
        $audit_policy = $record->audit_policy ? json_decode($record->audit_policy) : NULL;
        $policy_data = [];
        if( $audit_policy )
        {
            $policy_data = (array)$audit_policy->new;
        }

        // Update Policy Table
        if( $policy_data )
        {
            $this->policy_model->commit_endorsement($record->policy_id, $policy_data);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Save Cost Reference Data and Transactional Data
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function save($id, $txn_data)
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
                 * Task 1: Update TXN Data
                 */
                parent::update($id, $txn_data, TRUE);

                /**
                 * Task 2: Log activity
                 */
                $this->log_activity($id, 'E');

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
     * Get Current Endorsement Record for Supplied Policy
     *
     * @param int $policy_id
     * @return object
     */
    public function get_current_endorsement_by_policy($policy_id)
    {
        $where = [
            'ENDRSMNT.policy_id'    => $policy_id,
            'ENDRSMNT.flag_current' => IQB_FLAG_ON
        ];
        return $this->db->select('ENDRSMNT.*, P.branch_id, P.portfolio_id')
                        ->from($this->table_name . ' AS ENDRSMNT')
                        ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                        ->where($where)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Endorsement Record
     *
     * @param int $id
     * @return object
     */
    public function get($id)
    {
        return $this->db->select('ENDRSMNT.*, P.branch_id, P.portfolio_id, C.full_name as transfer_customer_name')
                        ->from($this->table_name . ' AS ENDRSMNT')
                        ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                        ->join('dt_customers C', 'C.id = ENDRSMNT.transfer_customer_id', 'left')
                        ->where('ENDRSMNT.id', $id)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Endorsement Record(s)
     *
     * This function is mainly used to get all the active records
     * for Endorsement Printing.
     *
     * @param int $id
     * @return array
     */
    public function get_many_by($where)
    {
        $this->db->select('ENDRSMNT.*, P.branch_id, P.code')
                    ->from($this->table_name . ' AS ENDRSMNT')
                    ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                    ->where($where);

        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('P');

        // Get the damn result
        return $this->db->order_by('ENDRSMNT.id', 'DESC')
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Get All Endorsements Rows for Supplied Policy
     *
     * @param int $policy_id
     * @return type
     */
    public function rows($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'endrsmnt_'.$policy_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows($policy_id);

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
         * @param integer $policy_id
         * @return mixed
         */
        private function _rows($policy_id)
        {
            // Data Selection
            $this->db->select('ENDRSMNT.id, ENDRSMNT.policy_id, ENDRSMNT.txn_type, ENDRSMNT.txn_date, ENDRSMNT.flag_ri_approval, ENDRSMNT.flag_current, ENDRSMNT.status, P.branch_id')
                            ->from($this->table_name . ' AS ENDRSMNT')
                            ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                            ->where('P.id', $policy_id);

            /**
             * Apply User Scope
             */
            $this->dx_auth->apply_user_scope('P');


            // Get the damn result
            return $this->db->order_by('ENDRSMNT.id', 'DESC')
                            ->get()->result();
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
            throw new Exception("Exception [Model:Endorsement_model][Method: get_fresh_renewal_by_policy()]: Invalid Transaction Type.");
        }
        $where = [
            'ENDRSMNT.policy_id'    => $policy_id,
            'ENDRSMNT.txn_type'     => $txn_type
        ];
        return $this->db->select('ENDRSMNT.*')
                        ->from($this->table_name . ' AS ENDRSMNT')
                        ->where($where)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get RI-Approval Flag for Given Policy
     *
     * @param integer $policy_id
     * @return integer
     */
    public function get_flag_ri_approval_by_policy($policy_id)
    {
        return $this->db->select('ENDRSMNT.flag_ri_approval')
                        ->from($this->table_name . ' AS ENDRSMNT')
                        ->where('ENDRSMNT.policy_id', $policy_id)
                        ->limit(1)
                        ->get()->row()->flag_ri_approval;
    }

    // ----------------------------------------------------------------


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
                'endrsmnt_*'
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

    public function delete($id_or_record = NULL)
    {
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;
        if(!$record)
        {
            return FALSE;
        }

        // Safe to Delete?
        if( !safe_to_delete( get_class(), $record->id ) )
        {
            return FALSE;
        }


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        /**
         * Start Transaction
         */
        $this->db->trans_start();

            /**
             * Task 1: Delete Draft Policy Txn Record
             */
            parent::delete($record->id);

            /**
             * Task 2: Update Activity Log
             */
            $this->log_activity($record->id, 'D');

            /**
             * Task 3: Update Current Flag to Heighest ID of txn for this policy
             */
            $this->_set_flag_current($record->policy_id);

            /**
             * Task 4: Clear Cache for this Policy (List of txn for this policy)
             */
            $cache_var = 'endrsmnt_'.$record->policy_id;
            $this->clear_cache($cache_var);

        /**
         * Complete Transaction
         */
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

        private function _set_flag_current($policy_id)
        {
            // How it works?
            //
            // UPDATE wins
            // SET prevmonth_top=1
            // ORDER BY month_wins DESC
            // LIMIT 1

            $sql = "UPDATE {$this->table_name} " .
                    "SET flag_current = ? " .
                    "WHERE policy_id = ? " .
                    "ORDER BY id DESC " .
                    "LIMIT 1";

            return $this->db->query($sql, array(IQB_FLAG_ON, $policy_id));
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
            'module' => 'endorsements',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}