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

    protected $fields = ['id', 'policy_id', 'customer_id', 'agent_id', 'sold_by', 'start_date', 'end_date', 'txn_type', 'issued_date', 'gross_amt_sum_insured', 'net_amt_sum_insured', 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'percent_ri_commission', 'amt_ri_commission', 'amt_direct_discount', 'amt_stamp_duty', 'amt_transfer_fee', 'amt_transfer_ncd', 'amt_cancellation_fee', 'amt_vat', 'computation_basis', 'premium_computation_table', 'cost_calculation_table', 'txn_details', 'remarks', 'transfer_customer_id', 'flag_ri_approval', 'flag_current', 'flag_terminate_on_refund', 'flag_short_term', 'short_term_config', 'short_term_rate', 'status', 'audit_policy', 'audit_object', 'audit_customer', 'ri_approved_at', 'ri_approved_by', 'created_at', 'created_by', 'verified_at', 'verified_by', 'updated_at', 'updated_by'];

    // Resetable Fields on Policy/Object Edit, Endorsement Edit
    protected static $nullable_fields = [
        'gross_amt_sum_insured',
        'net_amt_sum_insured',
        'amt_basic_premium',
        'amt_pool_premium',
        'amt_commissionable',
        'amt_agent_commission',
        'percent_ri_commission',
        'amt_ri_commission',
        'amt_direct_discount',
        'amt_transfer_fee',
        'amt_transfer_ncd',
        'amt_cancellation_fee',
        'amt_vat',
        'cost_calculation_table',
        'flag_ri_approval'
    ];


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
        // $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules of Manual Premium Computation for Endorsement
     *
     * Endorsement Types
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * Portfolios
     *  - FIRE - FIRE
     *  - ENG - CAR
     *  - ENG - EAR
     *  - ENG - TMI
     *
     * @return array
     */
    public function manual_premium_v_rules()
    {
        $rules = [
            [
                'field' => 'gross_amt_sum_insured',
                'label' => 'Gross Sum Insured (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'net_amt_sum_insured',
                'label' => 'Net Sum Insured (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'amt_basic_premium',
                'label' => 'Basic Premium (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'amt_pool_premium',
                'label' => 'Pool Premium (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'amt_agent_commission',
                'label' => 'Agent Commission (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
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

        return $rules;
    }

    // ----------------------------------------------------------------

    /**
     * FAC-IN Premium Validation Rules.
     *
     * Validation rules for policy of Category FAC-Inward.
     *
     * @return array
     */
    public function fac_in_premium_v_rules()
    {
        $rules = [
            [
                'field' => 'amt_basic_premium',
                'label' => 'FAC Premium (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'percent_ri_commission',
                'label' => 'Commission on FAC Accepted(%)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_required' => true
            ]
        ];

        return $rules;
    }

    // ----------------------------------------------------------------

    public function get_v_rules( $txn_type, $portfolio_id, $policy_record, $formatted = FALSE)
    {
        $txn_type                   = (int)$txn_type;
        $computation_basis_dropdown = _ENDORSEMENT_computation_basis_dropdown(FALSE);
        $v_rules                    = [];

        // Basic Rules (Txn Details, Remarks with Template Reference)
        $basic = $this->_v_rules_basic( $txn_type, $portfolio_id, $policy_record );


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
            case IQB_POLICY_ENDORSEMENT_TYPE_GENERAL:
                $v_rules = ['basic' => $basic];
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
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
                            'label' => 'NCD Return (Rs.)',
                            'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                            '_type'     => 'text',
                            '_default'  => 0.00,
                            '_help_text' => '<strong>No Claim Discount Return:</strong> This applies only in <strong class="text-red">MOTOR</strong> portfoliios.',
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

            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
                $v_rules = ['basic' => $basic, 'computation_basis' => $computation_basis];
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                $v_rules = [
                    'basic'             => $basic,
                    'computation_basis' => $computation_basis,

                    /**
                     * Option To Terminate
                     */
                    'terminate' => [
                        [
                            'field' => 'flag_terminate_on_refund',
                            'label' => 'Terminate this policy after refund?',
                            'rules' => 'trim|alpha|in_list['.IQB_FLAG_YES.']',
                            '_type'             => 'checkbox',
                            '_checkbox_value'   => IQB_FLAG_YES,
                            '_required'         => true
                        ],
                        [
                            'field' => 'amt_cancellation_fee',
                            'label' => 'Cancellation Charge (Rs.)',
                            'rules' => 'trim|prep_decimal|decimal|max_length[20]',
                            '_type'     => 'text',
                            '_required' => true
                        ]
                    ]
                ];
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE:
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
     * Get Basic Validation Rules
     * i.e. Txn Details and Remarks with Endorsement Template Reference
     * @param type $txn_type
     * @param type $portfolio_id
     * @param object $policy_record
     * @return type
     */
    private function _v_rules_basic( $txn_type, $portfolio_id, $policy_record )
    {
        $txn_type                   = (int)$txn_type;
        $v_rules                    = [];

        $this->load->model('endorsement_template_model');
        $this->load->model('agent_model');
        $template_dropdown = $this->endorsement_template_model->dropdown( $portfolio_id, $txn_type );

        $v_rules = array_merge(
            [
                [
                    'field' => 'sold_by',
                    'label' => 'Sales Staff',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_id'       => '_marketing-staff',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_default'  => $policy_record->sold_by ?? '',
                    '_data'     => IQB_BLANK_SELECT + $this->user_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'agent_id',
                    'label' => 'Agent Name',
                    'rules' => 'trim|integer|max_length[11]',
                    '_id'       => '_agent-id',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_default'  => $policy_record->agent_id ?? '',
                    '_data'     => IQB_BLANK_SELECT + $this->agent_model->dropdown(true),
                    '_required' => false
                ],
                [
                    'field' => 'issued_date',
                    'label' => 'Endorsement Issued Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Endorsement Start Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'end_date',
                    'label' => 'Endorsement End Date',
                    'rules' => 'trim|required|valid_date|callback__cb_valid_end_date',
                    '_type'             => 'date',
                    '_default'          => $policy_record->end_date,
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'template_reference',
                    'label' => 'Template Reference',
                    'rules' => 'trim|integer|max_length[8]',
                    '_key'      => 'template_reference',
                    '_id'       => 'template-reference',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $template_dropdown,
                    '_required' => false
                ],
                [
                    'field' => 'txn_details',
                    'label' => 'Transaction Details (सम्पुष्टि विवरण)',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_id'       => 'txn-details',
                    '_type'     => 'textarea',
                    '_required' => true
                ],
                [
                    'field' => 'remarks',
                    'label' => 'Remarks/कैफियत',
                    'rules' => 'trim|htmlspecialchars',
                    '_type'     => 'textarea',
                    '_required' => false
                ]
            ]);

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Get Basic Validation Rules
     * i.e. Txn Details and Remarks with Endorsement Template Reference
     * @param type $txn_type
     * @param type $portfolio_id
     * @param type|bool $formatted
     * @return type
     */
    public function get_v_rules_basic_for_debit_note( $txn_type, $portfolio_id, $formatted = FALSE )
    {
        $txn_type                   = (int)$txn_type;
        $v_rules                    = [];

        $this->load->model('endorsement_template_model');
        $template_dropdown = $this->endorsement_template_model->dropdown( $portfolio_id, $txn_type );

        $basic = [
            [
                'field' => 'template_reference',
                'label' => 'Template Reference',
                'rules' => 'trim|integer|max_length[8]',
                '_key'      => 'template_reference',
                '_id'       => 'template-reference',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $template_dropdown,
                '_required' => false
            ],
            [
                'field' => 'txn_details',
                'label' => 'Transaction Details (सम्पुष्टि विवरण)',
                'rules' => 'trim|required|htmlspecialchars',
                '_id'       => 'txn-details',
                '_type'     => 'textarea',
                '_required' => true
            ],
            [
                'field' => 'remarks',
                'label' => 'Remarks/कैफियत',
                'rules' => 'trim|htmlspecialchars',
                '_type'     => 'textarea',
                '_required' => false
            ]
        ];

        $v_rules = ['basic' => $basic];

        if( !$formatted )
        {
            return $v_rules;
        }
        else
        {
            return $basic;
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
    public function reset_by_policy($policy_id)
    {
        $record = $this->get_current_endorsement_by_policy($policy_id);

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: _reset()]: Current TXN record could not be found.");
        }

        return $this->_reset($record->id, $record->policy_id);
    }


    // --------------------------------------------------------------------

    /**
     * Reset Premium & SI Fields of a record to NULL
     */
    private function _reset($id, $policy_id)
    {
        /**
         * Task 1: Reset Endorsement Record
         */
        $reset_data = [];

        foreach (self::$nullable_fields as $field)
        {
             $reset_data[$field] = NULL;
        }

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;
        /**
         * Disable DB Debugging
         */
        $this->db->db_debug = FALSE;
        $this->db->trans_start();

            $this->db->where('id', $id)
                     ->update($this->table_name, $reset_data);

            /**
             * Task 2: Clear Cache (Speciic to this Policy ID)
             */
            $cache_var = 'endrsmnt_' . $policy_id;
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

    /**
     * Reset Data on Edit
     *
     * We should nullify the fields on premium computable endorsement i.e.
     *  - Fresh/Renewal
     *  - Premium Upgrade
     *  - Premium Refund
     */
    public function _reset_on_edit($txn_type, $data)
    {

        $nullable_fields = [];
        if( _ENDORSEMENT_is_premium_computable_by_type($txn_type) )
        {
            $nullable_fields = self::$nullable_fields;
        }

        foreach ($nullable_fields as $field)
        {
             $data[$field] = NULL;
        }

        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * Update Endorsement Status
     *
     * !!! NOTE:
     *      This method must be triggered by controller method
     *      within try-catch block.
     *
     * !!! NOTE:
     *      We can only change status of current Transaction Record
     *
     * @param integer $policy_id_or_endorsement_record Policy ID or Transaction Record
     * @param alpha $to_status_flag Status Code
     * @param bool $terminate_policy Terminate policy on Endorsement Activation
     * @return bool
     */
    public function update_status($policy_id_or_endorsement_record, $to_status_flag, $terminate_policy=FALSE)
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

        $transaction_status = FALSE;
        switch($to_status_flag)
        {
            /**
             * Reset Verified date/user to NULL
             */
            case IQB_POLICY_ENDORSEMENT_STATUS_DRAFT:
                $transaction_status = $this->to_draft($record);
                break;

            /**
             * Update Verified date/user and Reset ri_approved date/user to null
             */
            case IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED:
                $transaction_status = $this->to_verified($record);
                break;

            /**
             * Update RI Approved date/user
             */
            case IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED:
                $transaction_status = $this->to_ri_approved($record);
                break;


            case IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED:
                $transaction_status = $this->to_vouchered($record);
                break;


            case IQB_POLICY_ENDORSEMENT_STATUS_INVOICED:
                $transaction_status = $this->to_invoiced($record);
                break;

            case IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE:
                $transaction_status = $this->to_activated($record);
                break;

            default:
                break;
        }

        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function status_qualifies($current_status, $to_status)
    {
        $flag_qualifies = FALSE;

        switch ($to_status)
        {
            case IQB_POLICY_ENDORSEMENT_STATUS_DRAFT:
                $flag_qualifies = $current_status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED;
                break;

            case IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED:
                $flag_qualifies = $current_status === IQB_POLICY_ENDORSEMENT_STATUS_DRAFT;
                break;

            case IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED:
                $flag_qualifies = $current_status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED;
                break;

            case IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED:
                $flag_qualifies = in_array($current_status, [IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED, IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED]);
                break;

            case IQB_POLICY_ENDORSEMENT_STATUS_INVOICED:
                $flag_qualifies = $current_status === IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED;
                break;

            // For non-txnal endorsement, its from approved
            case IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE:
                $flag_qualifies = in_array($current_status, [IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED, IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED]);
                break;

            default:
                break;
        }
        return $flag_qualifies;
    }

    // ----------------------------------------------------------------

    public function to_draft($id_record)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_draft()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_DRAFT);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_draft()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }


    // ----------------------------------------------------------------

    public function to_verified($id_record)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_verified()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED);

            /**
             * Task 1: FRESH/RENEWAL - RI Approval Constraint
             */
            if( _ENDORSEMENT_is_first($record->txn_type) )
            {
                /**
                 * RI Approval Constraint
                 */
                $this->load->helper('ri');
                $flag_ri_approval = RI__compute_flag_ri_approval($record->portfolio_id, $record->net_amt_sum_insured);
                $update_data = [
                    'flag_ri_approval' => $flag_ri_approval
                ];
                parent::update($record->id, $update_data, TRUE);
            }


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_verified()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }


    // ----------------------------------------------------------------

    public function to_ri_approved($id_record)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_ri_approved()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_ri_approved()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_vouchered($id_record)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_vouchered()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED);

            /**
             * Generate Policy Number if Endorsement FRESH/Renewal
             */
            if( _ENDORSEMENT_is_first($record->txn_type) )
            {
                $this->policy_model->generate_policy_number( $record->policy_id );
            }


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_vouchered()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }


    // ----------------------------------------------------------------

    public function to_invoiced($id_record)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_invoiced()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_invoiced()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_activated($id_record, $terminate_policy = FALSE)
    {
        // Get the Endorsement Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_activated()]: Endorsement record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE);

            if( !_ENDORSEMENT_is_first($record->txn_type) )
            {

                /**
                 * Update Endorsement Changes to Object, Policy, Customer - If any
                 */
                $this->_commit_endorsement_audit($record);

                /**
                 * Policy Termination the following Endorsement
                 *  - Refund and Terminate
                 *  - Simply Terminate
                 */
                $terminate_policy = $terminate_policy || $record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE;
                if($terminate_policy)
                {
                    $policy_record = $this->policy_model->get($record->policy_id);
                    $this->policy_model->to_canceled($record->policy_id);
                }


                /**
                 * Policy Ownership Transfer
                 */
                else if($record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER )
                {
                    $this->transfer_ownership($record);
                }

                /**
                 * Update Policy "END DATE"
                 */
                $this->policy_model->update_end_date($record->policy_id, $record->end_date);
            }

            /**
             * Activate Policy on Fresh/Renewal Endorsement Activation
             */
            else
            {
                $this->policy_model->to_activated($record->policy_id);
            }


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;


        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Endorsement_model][Method: to_activated()]: Endorsement status and post-status update task could not be updated.");
        }

        // Clean Cache
        $this->_clean_cache_by_policy($record->policy_id);

        // return result/status
        return $transaction_status;
    }

        // ----------------------------------------------------------------

        private function _do_status_transaction($record, $status)
        {
            $data = [
                'status'        => $status,
                'updated_by'    => $this->dx_auth->get_user_id(),
                'updated_at'    => $this->set_date()
            ];

            /**
             * Get status Specific Data. Eg. Verified by/at
             */
            $data = $this->_prep_status_data($status, $data);

            /**
             * Process Back Dates on Every Status Transaction
             *
             * This is required because you might have verified/vouchered yesterday and today you are invoicing
             */
            $data  = $this->_backdate($record, $data);

            return $this->_to_status($record->id, $data);
        }

        // ----------------------------------------------------------------

        private function _prep_status_data($status, $data)
        {
            switch($status)
            {
                /**
                 * Reset Verified date/user to NULL
                 */
                case IQB_POLICY_ENDORSEMENT_STATUS_DRAFT:
                    $data['verified_at'] = NULL;
                    $data['verified_by'] = NULL;
                    break;

                /**
                 * Update Verified date/user and Reset ri_approved date/user to null
                 */
                case IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED:
                    $data['verified_at'] = $this->set_date();
                    $data['verified_by'] = $this->dx_auth->get_user_id();
                    $data['ri_approved_at'] = NULL;
                    $data['ri_approved_by'] = NULL;
                break;

                /**
                 * Update RI Approved date/user
                 */
                case IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED:
                    $data['ri_approved_at'] = $this->set_date();
                    $data['ri_approved_by'] = $this->dx_auth->get_user_id();
                break;

                default:
                    break;
            }

            return $data;
        }

        // ----------------------------------------------------------------

        private function _to_status($id, $data)
        {
            return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
        }

        // --------------------------------------------------------------------

        private function _clean_cache_by_policy($policy_id)
        {
            /**
             * Cleare Caches
             *
             *      1. List of transaction by this policy
             *      2. List of installment by this policy
             */
            $cache_var = 'endrsmnt_' . $policy_id;
            $this->clear_cache($cache_var);

            $this->load->model('policy_installment_model');
            $cache_var = 'ptxi_bypolicy_' . $policy_id;
            $this->policy_installment_model->clear_cache($cache_var);
        }

    // --------------------------------------------------------------------

        /**
         * Validate and process Back-date
         *
         * If user has supplied backdate, please make sure that :
         *      1. The user is allowed to enter Backdate
         *      2. If so, the supplied date should be withing backdate limit
         *
         * @param object    $record Policy Record
         * @param array     $data
         * @return array
         */
        public function _backdate($record, $data)
        {

            $old_issued_date = $record->issued_date;
            $old_start_date  = $record->start_date;
            $old_end_date    = $record->end_date;

            $new_issued_date    = backdate_process($old_issued_date);
            $new_start_date     = backdate_process($old_start_date);

            /**
             * If backdate is not allowed, we have to recompute the end date as per new start date
             */
            if( strtotime($old_start_date) != strtotime($new_start_date))
            {
                $days               = date_difference($old_start_date, $new_start_date, 'd');
                $new_end_date       = date('Y-m-d', strtotime($old_end_date. " + {$days} days"));
                $data['end_date']   = $new_end_date;
            }

            // Start and Issued Date
            $data['issued_date']    = $new_issued_date;
            $data['start_date']     = $new_start_date;

            return $data;
        }

    // --------------------------------------------------------------------

    /**
     * Transfer Ownership of Policy
     *
     * @param object $record Endorsement Record
     * @return mixed
     */
    public function transfer_ownership($record)
    {
        /**
         * TASKS:
         *
         *  1. Assign New Customer to this policy
         *  2. Unlock Old Customer
         *  3. Assign New Customer to this Policy Object
         */

        $this->load->model('policy_model');
        $this->policy_model->transfer_ownership($record->policy_id, $record->transfer_customer_id);

        // @TODO : Please Chedk if this customer has another active policy, in that case it should not be unlocked
        $this->load->model('customer_model');
        $this->customer_model->update_lock($record->customer_id, IQB_FLAG_UNLOCKED);

        $this->load->model('object_model');
        $this->object_model->transfer_ownership($record->object_id, $record->customer_id, $record->transfer_customer_id);
    }

    // --------------------------------------------------------------------

    /**
     * Add Endorsement
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function add($data)
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
                 * Task 3: Clear Cache
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

    /**
     * Edit Endorsement
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit($id, $data)
    {
        $record = parent::find($id);

        /**
         * Reset Data by Type
         */
        $data = $this->_reset_on_edit($record->txn_type, $data);

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
                 * Task 1: Update Data
                 */
                parent::update($id, $data, TRUE);


                /**
                 * Task 2: Clear Cache
                 */
                $cache_var = 'endrsmnt_' . $record->policy_id;
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
            $data = $audit_customer->new; // Pass as Object as it contains both customer and address information
            $this->customer_model->commit_endorsement($obj_cust->customer_id, $data);
        }

        /**
         * Task 3: Policy Changes
         *
         * Policy's Current Sold By and Agnet ID From This Endorsement
         */
        $policy_data = [];
        if( $record->agent_id )
        {
            $policy_data['agent_id'] = $record->agent_id;
        }
        $policy_data['sold_by'] = $record->sold_by;

        /**
         * NOTE: The following code is non-functional NOW as we do not edit policy directly
         *
        $audit_policy = $record->audit_policy ? json_decode($record->audit_policy) : NULL;
        $policy_data = [];
        if( $audit_policy )
        {
            $policy_data = (array)$audit_policy->new;
        }
        */

        // Update Policy Table
        if( $policy_data )
        {
            $this->policy_model->commit_endorsement($record->policy_id, $policy_data);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Save Premium Data
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

        $this->_single_select();

        return $this->db->where($where)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Last active Endorsement Record for Supplied Policy
     *
     * @param int $policy_id
     * @return object
     */
    public function get_latest_active_by_policy($policy_id)
    {
        $where = [
            'ENDRSMNT.policy_id'    => $policy_id,
            'ENDRSMNT.status'       => IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE
        ];

        $this->_single_select();

        return $this->db->where($where)
                        ->order_by('ENDRSMNT.id', 'desc') // latest active
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
        $this->_single_select();

        return $this->db->where('ENDRSMNT.id', $id)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    private function _single_select()
    {
        $select =   "ENDRSMNT.*, " .

                    // Branch and Portfolio
                    "P.category as policy_category, P.insurance_company_id, P.code as policy_code, P.branch_id, P.portfolio_id, P.customer_id, P.object_id, P.status AS policy_status, " .

                    // Transfer Customer Name
                    "C.full_name_en as transfer_customer_name_en, C.full_name_np as transfer_customer_name_np, " .

                    // Endorsement Audit
                    "AE.id AS audit_endorsement_id, AE.object_id AS audit_object_id, AE.customer_id AS audit_customer_id, AE.audit_policy, AE.audit_object, AE.audit_customer";

        $this->db->select($select)
                ->from($this->table_name . ' AS ENDRSMNT')
                ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                ->join('audit_endorsements AE', 'AE.endorsement_id = ENDRSMNT.id', 'left')
                ->join('dt_customers C', 'C.id = ENDRSMNT.transfer_customer_id', 'left');
    }

    // --------------------------------------------------------------------

    /**
     * Get Endorsement Record(s) for Schedule Print
     *
     * This function is mainly used to get all the active records
     * for Endorsement Printing.
     *
     * @param int $id
     * @return array
     */
    public function schedule_list($where)
    {
        $this->db->select(

                        /**
                         * Endorsement Table
                         */
                        "ENDRSMNT.*, " .

                        /**
                         * Policy Table
                         */
                        "P.portfolio_id, P.branch_id, P.code AS policy_code, P.flag_on_credit, P.care_of, " .

                        /**
                         * Branch Inofrmation
                         */
                         "B.name_en AS branch_name_en, B.name_np AS branch_name_np, " .

                        /**
                         * Current Customer Info
                         */
                        "C.full_name_en as customer_name_en,  C.full_name_np as customer_name_np, " .

                        /**
                         * Ownership Transferred Customer Info
                         */
                        "COT.full_name_en as cot_customer_name_en, COT.full_name_np as cot_customer_name_np, " .

                        /**
                         * Agent Table (agent_id, name, picture, bs code, ud code, contact, active, type)
                         */
                        "A.name as agent_name, A.bs_code as agent_bs_code, A.ud_code as agent_ud_code"
                    )
                    ->from($this->table_name . ' AS ENDRSMNT')
                    ->join('dt_policies P', 'P.id = ENDRSMNT.policy_id')
                    ->join('master_branches B', 'B.id = P.branch_id')
                    ->join('dt_customers C', 'C.id = P.customer_id')
                    ->join('dt_customers COT', 'COT.id = ENDRSMNT.transfer_customer_id', 'left')
                    ->join('rel_agent__policy RAP', 'RAP.policy_id = P.id', 'left')
                    ->join('master_agents A', 'RAP.agent_id = A.id', 'left')
                    ->where($where)
                    ->where_not_in('ENDRSMNT.txn_type', [IQB_POLICY_ENDORSEMENT_TYPE_FRESH, IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL]);

        /**
         * Customer Address
         */
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADRC',

            // Country Table Alias
            'country' => 'CNTRYC',

            // State Table Alias
            'state' => 'STATEC',

            // Local Body Table Alias
            'local_body' => 'LCLBDC',

            // Type/Module Table Alias
            'module' => 'C'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_customer_');


        /**
         * Ownership Transferred Customer Address
         */
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADRCOT',

            // Country Table Alias
            'country' => 'CNTRYCOT',

            // State Table Alias
            'state' => 'STATECOT',

            // Local Body Table Alias
            'local_body' => 'LCLBDCOT',

            // Type/Module Table Alias
            'module' => 'COT'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_customer_cot_', FALSE);


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
            $this->db->select(
                            // Endorsement
                            "E.id, E.policy_id, E.txn_type, E.issued_date, E.flag_ri_approval, E.flag_current, E.status, " .

                            // Policy
                            "P.branch_id, " .

                            /**
                             * User Table - Sales Staff Info ( username, code)
                             */
                            "SU.username as sold_by_username, SU.code AS sold_by_code, " .

                            /**
                             * Agent Name
                             */
                            "A.name as agent_name"
                        )
                            ->from($this->table_name . ' AS E')
                            ->join('dt_policies P', 'P.id = E.policy_id')
                            ->join('auth_users SU', 'SU.id = E.sold_by', 'left')
                            ->join('master_agents A', 'E.agent_id = A.id', 'left')
                            ->where('P.id', $policy_id);

            /**
             * Apply User Scope
             */
            $this->dx_auth->apply_user_scope('P');


            // Get the damn result
            return $this->db->order_by('E.id', 'DESC')
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
        if( !in_array($txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_FRESH, IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL]) )
        {
            throw new Exception("Exception [Model:Endorsement_model][Method: get_fresh_renewal_by_policy()]: Invalid Transaction Type.");
        }
        $where = [
            'ENDRSMNT.policy_id'    => $policy_id,
            'ENDRSMNT.txn_type'     => $txn_type
        ];
        $this->_single_select();
        return $this->db->where($where)
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
             * Task 2: Update Current Flag to Heighest ID of txn for this policy
             */
            $this->_set_flag_current($record->policy_id);

            /**
             * Task 3: Clear Cache for this Policy (List of txn for this policy)
             */
            $cache_var = 'endrsmnt_'.$record->policy_id;
            $this->clear_cache($cache_var);

            // Installment Cache by Policy
            $cache_var = 'ptxi_bypolicy_' . $record->policy_id;
            $this->delete_cache($cache_var);

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
}