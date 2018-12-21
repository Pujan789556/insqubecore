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

    protected $fields = ['id', 'policy_id', 'customer_id', 'agent_id', 'sold_by', 'start_date', 'end_date', 'txn_type', 'issued_date', 'amt_sum_insured_object', 'amt_sum_insured_net', 'gross_amt_basic_premium', 'gross_amt_pool_premium', 'gross_amt_commissionable', 'gross_amt_agent_commission', 'gross_amt_ri_commission', 'gross_amt_direct_discount', 'refund_amt_basic_premium', 'refund_amt_pool_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_ri_commission', 'refund_amt_direct_discount', 'net_amt_basic_premium', 'net_amt_pool_premium', 'net_amt_commissionable', 'net_amt_agent_commission', 'net_amt_ri_commission', 'net_amt_direct_discount', 'net_amt_stamp_duty', 'net_amt_transfer_fee', 'net_amt_transfer_ncd', 'net_amt_cancellation_fee', 'net_amt_vat', 'percent_ri_commission', 'rc_ref_basic', 'pc_ref_basic', 'premium_computation_table', 'cost_calculation_table', 'txn_details', 'remarks', 'transfer_customer_id', 'flag_ri_approval', 'flag_current', 'flag_refund_on_terminate', 'flag_short_term', 'short_term_config', 'short_term_rate', 'status', 'ri_approved_at', 'ri_approved_by', 'created_at', 'created_by', 'verified_at', 'verified_by', 'updated_at', 'updated_by'];

    // Resetable Fields on Policy/Object Edit, Endorsement Edit
    protected static $nullable_fields = [
        'gross_amt_basic_premium',
        'gross_amt_pool_premium',
        'gross_amt_commissionable',
        'gross_amt_agent_commission',
        'gross_amt_ri_commission',
        'gross_amt_direct_discount',
        'refund_amt_basic_premium',
        'refund_amt_pool_premium',
        'refund_amt_commissionable',
        'refund_amt_agent_commission',
        'refund_amt_ri_commission',
        'refund_amt_direct_discount',
        'net_amt_basic_premium',
        'net_amt_pool_premium',
        'net_amt_commissionable',
        'net_amt_agent_commission',
        'net_amt_ri_commission',
        'net_amt_direct_discount',
        'net_amt_transfer_fee',
        'net_amt_transfer_ncd',
        'net_amt_cancellation_fee',
        'net_amt_vat',
        'percent_ri_commission',
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

        // Models
        $this->load->model('policy_model');
        $this->load->model('policy_installment_model');

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
                'field' => 'net_amt_basic_premium',
                'label' => 'Net Basic Premium (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'net_amt_pool_premium',
                'label' => 'Net Pool Premium (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_default'  => 0,
                '_required' => true
            ],
            [
                'field' => 'net_amt_stamp_duty',
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
                'field' => 'gross_amt_basic_premium',
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

    /**
     * Get Validation rules on Various Endorsements
     *
     * @param int $txn_type
     * @param int $portfolio_id
     * @param object $policy_record
     * @param bool $formatted
     * @return array
     */
    public function get_v_rules( $txn_type, $portfolio_id, $policy_record, $formatted = FALSE)
    {
        $txn_type                   = (int)$txn_type;
        $computation_basis_dropdown = _ENDORSEMENT_compute_reference_dropdown(FALSE);
        $v_rules                    = [];

        // Basic Rules (Txn Details, Remarks with Template Reference)
        $basic = $this->_v_rules_basic( $txn_type, $portfolio_id, $policy_record );

        /**
         * Up/Down Grade Compute Reference
         */
        if( $this-> is_endorsement_manual( $portfolio_id, $txn_type ) )
        {
            $updown_compute_reference = [
                [
                    'field' => 'net_amt_stamp_duty',
                    'label' => 'Stamp Duty (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_default'  => 0,
                    '_required' => true
                ]
            ];
        }
        else
        {
            $updown_compute_reference = [
                [
                    'field' => 'rc_ref_basic',
                    'label' => 'Refund Compute Reference',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $computation_basis_dropdown ) ) .']',
                    '_type'     => 'dropdown',
                    '_default'  => IQB_POLICY_ENDORSEMENT_CB_PRORATA,
                    '_data'     => IQB_BLANK_SELECT + $computation_basis_dropdown,
                    '_required' => true
                ],
                [
                    'field' => 'pc_ref_basic',
                    'label' => 'Premium Compute Reference',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $computation_basis_dropdown ) ) .']',
                    '_type'     => 'dropdown',
                    '_default'  => IQB_POLICY_ENDORSEMENT_CB_PRORATA,
                    '_data'     => IQB_BLANK_SELECT + $computation_basis_dropdown,
                    '_required' => true
                ],
                [
                    'field' => 'net_amt_stamp_duty',
                    'label' => 'Stamp Duty (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_default'  => 0,
                    '_required' => true
                ]
            ];
        }


        switch ($txn_type)
        {
            case IQB_POLICY_ENDORSEMENT_TYPE_GENERAL:
            case IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED:
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
                    'fees' => $this->_v_rules_OT_FEE($portfolio_id)
                ];
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                $v_rules = [
                    'basic'                     => $basic,
                    'updown_compute_reference'  => $updown_compute_reference,
                ];
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE:
                $flag_refund_on_terminate = $this->input->post('flag_refund_on_terminate');
                $required = $flag_refund_on_terminate == IQB_FLAG_YES ? 'required|' : '';
                $v_rules = [

                    'basic' => $basic,

                    /**
                     * Options To Terminate
                     */
                    'terminate' => [
                        [
                            'field' => 'flag_refund_on_terminate',
                            'label' => 'Refund on Termination?',
                            'rules' => 'trim|alpha|in_list['.IQB_FLAG_YES.']',
                            '_type'             => 'checkbox',
                            '_checkbox_value'   => IQB_FLAG_YES,
                            '_required'         => true
                        ],
                        [
                            'field' => 'net_amt_cancellation_fee',
                            'label' => 'Cancellation Charge (Rs.)',
                            'rules' => 'trim|'.$required.'prep_decimal|decimal|max_length[20]',
                            '_type'     => 'text',
                            '_required' => true
                        ],
                        [
                            'field' => 'rc_ref_basic',
                            'label' => 'Refund Compute Reference',
                            'rules' => 'trim|'.$required.'integer|exact_length[1]|in_list['. implode( ',', array_keys( $computation_basis_dropdown ) ) .']',
                            '_type'     => 'dropdown',
                            '_default'  => IQB_POLICY_ENDORSEMENT_CB_STR,
                            '_data'     => IQB_BLANK_SELECT + $computation_basis_dropdown,
                            '_required' => true
                        ]
                    ]
                ];
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

        private function _v_rules_OT_FEE($portfolio_id)
        {

            $motor_portfolio_ids = array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR);

            $rules = [
                [
                    'field' => 'net_amt_transfer_fee',
                    'label' => 'Transfer Fee (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_default'  => 100,
                    '_required' => true
                ],
                [
                    'field' => 'net_amt_stamp_duty',
                    'label' => 'Stamp Duty (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_default'  => 0,
                    '_required' => true
                ]
            ];

            if(in_array($portfolio_id, $motor_portfolio_ids))
            {
                $rules[] = [
                    'field' => 'net_amt_transfer_ncd',
                    'label' => 'NCD Return (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_default'  => 0.00,
                    '_help_text' => '<strong>No Claim Discount Return:</strong> This applies only in <strong class="text-red">MOTOR</strong> portfoliios.',
                    '_required' => true
                ];
            }

            return $rules;
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



            $v_rules = [
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
                    'rules' => 'trim|required|valid_date|callback__cb_valid_issued_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ]
            ];

            /**
             * Dates
             * ------
             *
             * 1. For NIL, UP/DOWN, Ownership Transfer (AUTOMATIC)
             *      END DATE = POLICY END DATE
             *
             * 2. FOR Terminate  (AUTOMATIC)
             *      END DATE = TODAY
             *
             * 3. FOR Time Extended (EDITABLE)
             *      END DATE > POLICY END DATE
             *
             */
            if( $txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED )
            {

                $st_date_obj = new DateTime($policy_record->end_date);
                $st_date_obj->modify('+1 day');
                $start_date = $st_date_obj->format('Y-m-d');

                $v_rules = array_merge($v_rules, [
                    [
                        'field' => 'end_date',
                        'label' => 'Endorsement End Date',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_end_date',
                        '_type'             => 'date',
                        '_default'          => '',
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_extra_html_below' => '<div class="text-warning"><strong>Endorsement Start Date</strong>:' . $start_date . '</div>',
                        '_required' => true
                    ]
                ]);
            }
            else
            {
                // Show End date right after startdate
                if($txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE)
                {
                    $end_date = date('Y-m-d');
                }
                else
                {
                    $end_date = $policy_record->end_date;
                }
                $v_rules = array_merge($v_rules, [
                    [
                        'field' => 'start_date',
                        'label' => 'Endorsement Start Date',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_start_date', // Cannot be earlier than Policy Start date
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d'),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_extra_html_below' => '<div class="text-warning"><strong>Endorsement End Date</strong>:' . $end_date . '</div>',
                        '_required' => true
                    ]
                ]);
            }


            $v_rules = array_merge($v_rules, [
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
     *
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
        $done = parent::update($id, $reset_data, TRUE);

        /**
         * Task 2: Clear Cache (Speciic to this Policy ID)
         */
        $cache_var = 'endrsmnt_' . $policy_id;
        $this->clear_cache($cache_var);

        return $done;

    }

    // --------------------------------------------------------------------

    /**
     * Nullify Premium Related Data for an endorsement
     *
     * We should nullify the fields on premium computable endorsement i.e.
     *  - Fresh/Renewal
     *  - Premium Upgrade
     *  - Premium Refund
     */
    public function nullify_premium_data($txn_type, $data)
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
        // Get the Endorsement Record
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
                $flag_ri_approval = RI__compute_flag_ri_approval($record->portfolio_id, $record->amt_sum_insured_net);
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
    public function add($data, $policy_record)
    {
        /**
         * Last Premium Computation Data
         */
        $data['premium_computation_table'] = $this->_last_premium_computation_table($data['txn_type'], $data['policy_id']);

        /**
         * END DATE
         */
        $data = $this->_assign_dates($data, $policy_record->end_date);

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;
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
                 * Task 3: Refund on Terminate???
                 */
                if( $data['txn_type'] == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE )
                {
                    $this->_update_terminate_refund_data($id);
                }


                /**
                 * Task 4: Clear Cache
                 */
                $cache_var = 'endrsmnt_' . $policy_record->id;
                $this->clear_cache($cache_var);

        /**
         * Complete transactions or Rollback
         */
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            return FALSE;
        }

        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $id;
    }

            /**
             * Get the latest premium computation
             * if endorsement is Premium Upgrade/Downgrade
             */
            private function _last_premium_computation_table($txn_type, $policy_id)
            {
                $pct = NULL;
                if( in_array($txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND, IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED]) )
                {

                    $where_in = [
                        IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
                        IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
                        IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND
                    ];
                    $row = $this->db->select('E.premium_computation_table')
                                    ->from($this->table_name . ' AS E')
                                    ->join('dt_policies P', 'P.id = E.policy_id')
                                    ->where('E.policy_id', $policy_id)
                                    ->where_in('E.txn_type', $where_in)
                                    ->order_by('E.id', 'desc')
                                    ->get()
                                    ->row();

                    $pct = $row->premium_computation_table ?? NULL;
                }
                return $pct;
            }

    // --------------------------------------------------------------------

    /**
     * Edit Endorsement
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit($id, $data, $policy_record)
    {
        $record = parent::find($id);

        /**
         * Reset Data by Type
         */
        $data = $this->nullify_premium_data($record->txn_type, $data);

        /**
         * END DATE
         */
        $data = $this->_assign_dates($data, $policy_record->end_date);

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;
        $this->db->trans_start();


                /**
                 * Task 1: Update Data
                 */
                parent::update($id, $data, TRUE);


                /**
                 * Task 2: Refund on Terminate???
                 */
                if( $data['txn_type'] == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE )
                {
                    $this->_update_terminate_refund_data($id);
                }

                /**
                 * Task 2: Clear Cache
                 */
                $cache_var = 'endrsmnt_' . $policy_record->id;
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
         * ==================== TRANSACTIONS END =========================
         */

        return $transaction_status;
    }

        // --------------------------------------------------------------------

        private function _assign_dates($data, $policy_end_date)
        {
            $txn_type   = $data['txn_type'];


            /**
             * Dates
             * ------
             *
             * 1. For NIL, UP/DOWN, Ownership Transfer
             *      START DATE =  DYNAMIC (Form Input)
             *      END DATE = POLICY END DATE
             *
             * 2. FOR Terminate
             *      START DATE = DYNAMIC (Form Input)
             *      END DATE = START DATE
             *
             * 3. FOR Time Extended (EDITABLE)
             *      START DATE = POLIY END DATE + 1 DAY
             *      END DATE =  DYNAMIC (Form Input) > POLICY END DATE
             *
             */
            if( $txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED )
            {
                $st_date_obj = new DateTime($policy_end_date);
                $st_date_obj->modify('+1 day');
                $start_date = $st_date_obj->format('Y-m-d');

                $data['start_date'] = $start_date;
            }
            else
            {
                if($txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE)
                {
                    $start_date = $data['start_date'];
                    $end_date   = date('Y-m-d');
                    if( strtotime($start_date) > strtotime($end_date) )
                    {
                        $end_date = $start_date;
                    }
                }
                else
                {
                    $end_date = $policy_end_date;
                }
                $data['end_date'] = $end_date;
            }

            return $data;
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
     * Save Data
     *
     * @param object|int $record OR Endorsement ID
     * @param array $data
     * @return bool
     */
    public function save($record, $data)
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        $transaction_status = TRUE;
        $this->db->trans_start();

                /**
                 * Task 1: Update TXN Data
                 */
                parent::update($record->id, $data, TRUE);


                /**
                 * Task2: Clear Cache for This Policy
                 */
                $this->clear_cache( 'endrsmnt_' . $record->policy_id );


        /**
         * Complete transactions or Rollback
         */
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $transaction_status = FALSE;
        }

        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $transaction_status;
    }

    // --------------------------------------------------------------------

    /**
     * Save Endorsement Premium
     *
     * @param object|int $record Current Endorsement Record | ID
     * @param object|int $policy_record Policy Record | ID
     * @param array $premium_data Premium data
     * @param array $post_data Original Post Data
     * @param array $cc_table Cost Calculation Table
     * @return bool
     */
    public function save_premium($record, $policy_record, $premium_data, $post_data, $cc_table = [])
    {
        // Get the Endorsement Record
        $record         = is_numeric($record) ? $this->get( (int)$record ) : $record;
        $policy_record  = is_numeric($policy_record) ? $this->policy_model->get( (int)$policy_record ) : $policy_record;

        /**
         * ==================== BUILD DATA =========================
         */

        /**
         * NON-FIRST ENDORSEMENT
         * ---------------------
         */
        if( !$this->is_first( $record->txn_type) )
        {
            /**
             * Manual or Automatic ???
             */
            if( $this->is_endorsement_manual( $record->portfolio_id, $record->txn_type ) )
            {
                $premium_data = $this->_build_updowngrade_premium_data_manual( $premium_data, $record, $policy_record );
            }
            else
            {
                $premium_data = $this->_build_updowngrade_premium_data( $premium_data, $record, $policy_record );
            }
        }

        /**
         * FIRST ENDORSEMENT - FRESH/RENEWAL
         * ---------------------------------
         */
        else
        {
            $premium_data   = $this->_build_fresh_premium_data( $premium_data, $policy_record );
        }

        // --------------------------------------------------------------------------

        /**
         * SUM Insured Data
         */
        $premium_data = $this->_update_si_on_premium_data($premium_data, $record, $policy_record);

        // --------------------------------------------------------------------------


        /**
         * Compute VAT
         */
        $premium_data['net_amt_stamp_duty'] = $post_data['net_amt_stamp_duty'] ?? 0.00;
        $premium_data = $this->_update_vat_on_net_premium_data( $premium_data, $policy_record->fiscal_yr_id, $policy_record->portfolio_id );

        // --------------------------------------------------------------------------

        /**
         * Prepare Other Data
         */
        $premium_data = array_merge($premium_data, [
            'premium_computation_table' => json_encode($post_data['premium'] ?? NULL),
            'cost_calculation_table'    => json_encode($cc_table)
        ]);

        // --------------------------------------------------------------------------


        // echo '<pre>'; print_r($premium_data);exit;

        /**
         * ==================== SAVE PREMIUM =========================
         */
        return $this->save($record, $premium_data);
    }



        // --------------------------------------------------------------------

        /**
         * Build UP/Down Grade Premium Data for current Endorsement
         *
         * @param array $premium_data
         * @param object $record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return array
         */
        private function _build_updowngrade_premium_data( $premium_data, $record, $policy_record )
        {
            /**
             * Task 1: Build Premium For Current Endorsement
             *
             *  - Apply `pc_ref_basic` on the Annual/Full Premium Data
             */
            // echo 'FULL NEW: <pre>'; print_r($premium_data);
            // Get the Premium Compute Reference Rate
            $rate = $this->_compute_reference_rate( $record->pc_ref_basic, $record, $policy_record );


            // Apply the computation rate on GROSS premium Data
            $premium_data = $this->_apply_rate_on_gross_premium_data( $premium_data, $rate, TRUE );
            // echo 'Rated NEW: <pre> Rate: ', $rate; print_r($premium_data);
            // --------------------------------------------------------------------


            /**
             * Task 2: Build Refund For Previous Endorsement
             *
             *  - Apply `rc_ref_basic` on the Previous Endorsement's GROSS Premium Data
             *
             */

            $refund_data = $this->_build_refund_data($record, $policy_record);
            // echo 'Refund : <pre>'; print_r($refund_data);
            // --------------------------------------------------------------------


            /**
             * Task 3: Add refund cols on main $premium_data
             */
            $premium_data = array_merge($premium_data, $refund_data);
            // echo 'Refund with Gross : <pre>'; print_r($premium_data);
            // --------------------------------------------------------------------

            /**
             * Task 4: Compute NET Premium Data = GROSS - REFUND, DO not refund POOL
             */
            $premium_data = $this->_compute_net_premium_data($premium_data);
            // echo 'GROSS, REFUND, NET : <pre>'; print_r($premium_data); exit;
            // --------------------------------------------------------------------


            /**
             * Task 5: Update the txn_type based on NET Premium
             *
             * NOTE: Type must be either UP/DOWN
             */
            if( in_array($record->txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND]) )
            {
                $premium_data = $this->_assign_txn_type_on_premium_data( $premium_data );
            }
            else
            {
                $premium_data['txn_type'] = $record->txn_type;
            }


            // --------------------------------------------------------------------

            /**
             * Task 4: Claimed Policy?
             *
             * We can not refund a claimed policy!
             */
            $this->load->model('claim_model');
            if(
                $premium_data['txn_type'] == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND
                    &&
                $this->claim_model->has_policy_claim($record->policy_id)
            )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _build_updowngrade_premium_data()]: The premium can not be refund on a policy having CLAIM.");
            }


            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Build UP/Down Grade Premium Data for current Endorsement - For MANUAL ENDORSEMENT UP/DOWN
         *
         * @param array $premium_data
         * @param object $record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return array
         */
        private function _build_updowngrade_premium_data_manual( $premium_data, $record, $policy_record, $refund_pool = FALSE  )
        {
            /**
             * $premium_data has net_amt_basic_premium and net_amt_pool_premium
             */

            // --------------------------------------------------------------------

            /**
             * Agent Commission??
             */
            $this->load->model('portfolio_setting_model');
            $pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio(
                                $policy_record->fiscal_yr_id,
                                $policy_record->portfolio_id
                            );
            $commission_rate = floatval($pfs_record->agent_commission);
            if( $record->agent_id &&  $commission_rate != 0.00 )
            {
                // Commission = Basic Premium X Commision / 100
                $net_amt_agent_commission   = bcdiv(
                    bcmul( $premium_data['net_amt_basic_premium'], $commission_rate, IQB_AC_DECIMAL_PRECISION),
                    100.00,
                    IQB_AC_DECIMAL_PRECISION
                );

                // Commissionable = Basic Premium
                $net_amt_commissionable = $premium_data['net_amt_basic_premium'];

                $premium_data = array_merge($premium_data, [
                    'net_amt_commissionable'        => $net_amt_commissionable,
                    'net_amt_agent_commission'      => $net_amt_agent_commission,
                ]);
            }
            else
            {
                $premium_data = array_merge($premium_data, [
                    'net_amt_commissionable'        => NULL,
                    'net_amt_agent_commission'      => NULL,
                ]);
            }

            // --------------------------------------------------------------------

            /**
             * Pool Refund ??
             */
            if( !$refund_pool && $premium_data['net_amt_pool_premium'] < 0.00 )
            {
                $premium_data['net_amt_pool_premium'] = 0.00;
            }

            // --------------------------------------------------------------------

            /**
             * NULLIFY GROSS & REFUND & Other Data
             */
            $premium_data = array_merge($premium_data, [

                // Gross, refund basic
                'gross_amt_basic_premium' => NULL,
                'refund_amt_basic_premium' => NULL,

                // Gross, refund pool
                'gross_amt_pool_premium' => NULL,
                'refund_amt_pool_premium' => NULL,

                // Gross, refund commission
                'gross_amt_commissionable'      => NULL,
                'refund_amt_commissionable'     => NULL,
                'gross_amt_agent_commission'    => NULL,
                'refund_amt_agent_commission'   => NULL,

                // NO Direct Discount
                'gross_amt_direct_discount'     => NULL,
                'refund_amt_direct_discount'    => NULL,
                'net_amt_direct_discount'       => NULL,

                // NO Transfer fee, NCD or Cancellation fee
                'net_amt_transfer_fee'      => NULL,
                'net_amt_transfer_ncd'      => NULL,
                'net_amt_cancellation_fee'  => NULL,

                // Percent RI Commission
                'percent_ri_commission'     => NULL,
                'gross_amt_ri_commission'   => NULL,
                'refund_amt_ri_commission'  => NULL,
                'net_amt_ri_commission'     => NULL,

                // Compute Reference - Set Both To Manual
                'pc_ref_basic' => IQB_POLICY_ENDORSEMENT_CB_MANUAL,
                'rc_ref_basic'  => IQB_POLICY_ENDORSEMENT_CB_MANUAL
            ]);


            // --------------------------------------------------------------------


            /**
             * Task 5: Update the txn_type based on NET Premium
             *
             * NOTE: Type must be either UP/DOWN
             */
            if( in_array($record->txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND]) )
            {
                $premium_data = $this->_assign_txn_type_on_premium_data( $premium_data );
            }
            else
            {
                $premium_data['txn_type'] = $record->txn_type;
            }



            // --------------------------------------------------------------------

            /**
             * Task 4: Claimed Policy?
             *
             * We can not refund a claimed policy!
             */
            $this->load->model('claim_model');
            if(
                $premium_data['txn_type'] == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND
                    &&
                $this->claim_model->has_policy_claim($record->policy_id)
            )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _build_updowngrade_premium_data_manual()]: The premium can not be refund on a policy having CLAIM.");
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Get the rate based on the compute_reference
         *
         *
         * @param int $compute_reference
         * @param object $record Endorsement Record
         * @param object $policy_record Policy Record
         * @return decimal
         */
        private function _compute_reference_rate( $compute_reference, $record, $policy_record )
        {
            $rate               = FALSE;
            $compute_reference  = (int)$compute_reference;

            switch ($compute_reference)
            {
                /**
                 * No computation needed. The whole amount is used.
                 */
                case IQB_POLICY_ENDORSEMENT_CB_ANNUAL:
                    $rate = 1;
                    break;

                /**
                 * Short Term Rate
                 */
                case IQB_POLICY_ENDORSEMENT_CB_STR:
                    $rate_percent = $this->portfolio_setting_model->compute_short_term_rate(
                        $policy_record->fiscal_yr_id,
                        $policy_record->portfolio_id,
                        $record->start_date,
                        $record->end_date
                    );
                    $rate           = $rate_percent / 100;
                    break;

                /**
                 * Prorata Rate
                 */
                case IQB_POLICY_ENDORSEMENT_CB_PRORATA:
                    $endorsement_duration   = _POLICY_duration($record->start_date, $record->end_date, 'd');
                    $policy_duration        = _POLICY_duration($policy_record->start_date, $policy_record->end_date, 'd');
                    $rate                   = $endorsement_duration / $policy_duration;
                    break;

                default:
                    # code...
                    break;
            }

            if( $rate === FALSE )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _compute_reference_rate()]: Invalid Premium Computation Reference.");
            }

            return $rate;
        }

        // --------------------------------------------------------------------

        /**
         * Get the rate based on the compute_reference for Premium Refund
         *
         *
         * @param int $compute_reference
         * @param object $prev_record Previous Endorsement Record
         * @param object $cur_record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return decimal
         */
        private function _compute_reference_rate_on_refund( $compute_reference, $prev_record, $cur_record, $policy_record )
        {
            $refund_rate               = FALSE;
            $compute_reference  = (int)$compute_reference;
            switch ($compute_reference)
            {
                /**
                 * No computation needed. The whole amount is used.
                 */
                case IQB_POLICY_ENDORSEMENT_CB_ANNUAL:
                    $refund_rate = 1;
                    break;

                /**
                 * Short Term Rate
                 *
                 * Refund Rate = (1 - Charged Rate)
                 *
                 * Charged Rate = Short Term Rate for Previous Endorsement's Consumed period
                 *                  i.e. Current Endorsement Start Date - Prev Endorsement Start Date
                 *
                 */
                case IQB_POLICY_ENDORSEMENT_CB_STR:
                    // Short Term Duration (Consumed) = Current Endosrement Start - Prev Endorsement Start
                    // So, we charge Short Term Rate on Consumed Duration and retrun the rest
                    $rate_percent_charged = $this->portfolio_setting_model->compute_short_term_rate(
                        $policy_record->fiscal_yr_id,
                        $policy_record->portfolio_id,
                        $prev_record->start_date,
                        $cur_record->start_date
                    );
                    $refund_rate            = 1 - $rate_percent_charged / 100;
                    break;

                /**
                 * Prorata Rate
                 *
                 * Refund Rate = Prev-Endorsement's Left Duration / Prev Endorsement Total Duration
                 */
                case IQB_POLICY_ENDORSEMENT_CB_PRORATA:
                    $total_duration   = _POLICY_duration($prev_record->start_date, $prev_record->end_date, 'd');
                    $left_duration    = _POLICY_duration($cur_record->start_date, $prev_record->end_date, 'd');
                    $refund_rate      = $left_duration / $total_duration;
                    break;

                default:
                    # code...
                    break;
            }

            if( $refund_rate === FALSE )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _compute_reference_rate_on_refund()]: Invalid Premium Computation Reference.");
            }

            return $refund_rate;
        }

        // --------------------------------------------------------------------

        /**
         * Rebuild Premium Data Applying - Short Term Rate or Prorata Rate on GROSS Premium Data
         *
         *
         * @param array $premium_data
         * @param float $rate  short term rate or prorata rate
         * @param type|bool $apply_on_pool
         * @return array
         */
        private function _apply_rate_on_gross_premium_data( $premium_data, $rate, $apply_on_pool = TRUE )
        {
            /**
             * APPLY COMPUTATION REFERENCE TO PREMIUM DATA
             * --------------------------------------------
             *
             * gross_amt_basic_premium
             * gross_amt_commissionable
             * gross_amt_agent_commission
             * gross_amt_direct_discount
             * gross_amt_pool_premium
             */
            $keys = [ 'gross_amt_basic_premium', 'gross_amt_commissionable', 'gross_amt_agent_commission', 'gross_amt_direct_discount'];
            foreach($keys as $key)
            {
                $premium_data[$key] = bcmul( floatval($premium_data[$key]), $rate, IQB_AC_DECIMAL_PRECISION);
            }

            /**
             * Apply on Pool Premium?
             */
            if( $apply_on_pool )
            {
                $premium_data['gross_amt_pool_premium'] = bcmul($premium_data['gross_amt_pool_premium'], $rate, IQB_AC_DECIMAL_PRECISION);
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Rebuild Premium Data Applying - Short Term Rate or Prorata Rate on REFUND Premium Data
         *
         *
         * @param array $premium_data
         * @param float $rate  short term rate or prorata rate
         * @return array
         */
        private function _apply_rate_on_refund_premium_data( $premium_data, $rate )
        {
            /**
             * APPLY COMPUTATION REFERENCE TO PREMIUM DATA
             * --------------------------------------------
             *
             * refund_amt_basic_premium
             * refund_amt_pool_premium
             * refund_amt_commissionable
             * refund_amt_agent_commission
             * refund_amt_direct_discount
             * refund_amt_pool_premium
             */
            $keys = [ 'refund_amt_basic_premium', 'refund_amt_pool_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_direct_discount'];
            foreach($keys as $key)
            {
                $premium_data[$key] = bcmul( $premium_data[$key], $rate, IQB_AC_DECIMAL_PRECISION);
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------


        /**
         * Build and get the Refund Data From Previous Endorsement
         * after applyig computation reference rate
         *
         * @param   object  $record  Current Endorsement Record
         * @param   object  $policy_record Policy Record
         * @return  array
         */
        private function _build_refund_data( $record, $policy_record )
        {
            $refund_data = [];
            $keys        = [ 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];

            // Previous Endorsement Record
            $p_endorsement  = $this->get_prev_premium_record_by_policy($policy_record->id, $record->id);
            if($p_endorsement)
            {
                foreach($keys as $col)
                {
                    $gross_col  = 'gross_'.$col;
                    $refund_col = 'refund_'.$col;
                    $refund_data[$refund_col] = $p_endorsement->{$gross_col};
                }
                // echo '<pre> REFUND FULL: '; print_r($refund_data);


                /**
                 * Apply Compute Reference Rate
                 */
                // Get the Refund Compute Reference Rate
                $rate = $this->_compute_reference_rate_on_refund( $record->rc_ref_basic, $p_endorsement, $record, $policy_record );

                // Apply the computation rate
                $refund_data = $this->_apply_rate_on_refund_premium_data( $refund_data, $rate );
                // echo '<pre> Refund Rate: ', $rate; print_r($refund_data); exit;
            }
            else
            {
                foreach($keys as $col)
                {
                    $refund_col = 'refund_'.$col;
                    $refund_data[$refund_col] = NULL;
                }
            }

            return $refund_data;
        }

        // --------------------------------------------------------------------

        /**
         * Assign txn_type based on total premium +ve or -ve
         *
         * @param array $premium_data
         * @return array
         */
        private function _assign_txn_type_on_premium_data( $premium_data )
        {
            $total_premium = floatval( $premium_data['net_amt_basic_premium'] ?? 0) + floatval($premium_data['net_amt_pool_premium'] ?? 0);
            if($total_premium > 0 )
            {
                $premium_data['txn_type'] = IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE;
            }
            elseif( $total_premium < 0)
            {
                $premium_data['txn_type'] = IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND;
            }
            else
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _assign_txn_type_on_premium_data()]: The total computed premium is ZERO.");
            }
            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Update SI on Premium Data
         *
         * @param array $premium_data
         * @param object $record
         * @param object $policy_record
         * @return array
         */
        private function _update_si_on_premium_data($premium_data, $record, $policy_record)
        {
            $this->load->model('object_model');

             /**
             * Object Sum Insured, Net Sum Insured
             *
             * FRESH EDNORSEMENT:
             *      GROSS SI = OBJECT'S SI
             *      NET SI = GROSS SI
             *
             * ENDORSEMENT
             *      GROSS SI = Latest Object SI
             *      NET SI = (Latest Object SI) - (OLD Object SI)
             *
             */
            $policy_object  = $this->object_model->get($policy_record->object_id);
            if( $this->is_first($record->txn_type) )
            {
                $gross_si  = $policy_object->amt_sum_insured;
                $net_si    = $policy_object->amt_sum_insured;
            }
            else
            {
                // Object Changed?
                if($record->audit_object)
                {
                    // Get Policy Object from Endorsement's Object's Audit data
                    $audit_object   = _OBJ__get_from_audit($record->audit_object, 'new');
                    $gross_si       = $audit_object->amt_sum_insured;
                    $net_si         = bcsub($gross_si, $policy_object->amt_sum_insured, IQB_AC_DECIMAL_PRECISION);
                }
                else
                {
                    // NO Audit Object - SUM Insured Unchanged
                    $gross_si  = $policy_object->amt_sum_insured;
                    $net_si    = 0.00;
                }

            }

            $premium_data['amt_sum_insured_object'] = $gross_si;
            $premium_data['amt_sum_insured_net']    = $net_si;

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Build Fresh/Renewal Premium Data
         *
         * If short term Policy, apply short term rate.
         *
         * @param array $premium_data
         * @param object $policy_record
         * @return array
         */
        private function _build_fresh_premium_data( $premium_data, $policy_record )
        {
            /**
             * Short-Term Policy???
             */
            if($policy_record->flag_short_term == IQB_FLAG_YES)
            {
                $rate_percent = $this->portfolio_setting_model->compute_short_term_rate(
                    $policy_record->fiscal_yr_id,
                    $policy_record->portfolio_id,
                    $policy_record->start_date,
                    $policy_record->end_date
                );

                $rate           = $rate_percent / 100.00;
                $premium_data   = $this->_apply_rate_on_gross_premium_data( $premium_data, $rate, TRUE);

                // Update Short Term Related Info on Endorsement as well
                $premium_data['flag_short_term']    = IQB_FLAG_YES;
                $premium_data['short_term_config']  = IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BOTH;
                $premium_data['short_term_rate']    = $rate_percent;
            }

            /**
             * REFUND on FRESH is ALL ZERO/NULL
             */
            $premium_data = $this->_reset_refund_premium_data($premium_data);


            /**
             * Compute NET Premium Data = GROSS - REFUND
             */
            $premium_data = $this->_compute_net_premium_data($premium_data);

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Reset all Refund Fields to NULL
         *
         * @param array $premium_data
         * @return array
         */
        private function _reset_refund_premium_data($premium_data)
        {
            $keys = [ 'refund_amt_basic_premium', 'refund_amt_pool_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_direct_discount'];

            foreach($keys as $key)
            {
                $premium_data[$key] = NULL;
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Compute NET Premium Data
         *
         * NET = GROSS - REFUND
         *
         * @param array $premium_data MUST have gross_* and refund_* fields
         * @param bool $refund_pool
         * @return array
         */
        private function _compute_net_premium_data($premium_data, $refund_pool = FALSE)
        {
            $keys = [ 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];

            foreach($keys as $key)
            {
                $gross_key  = 'gross_' . $key;
                $refund_key = 'refund_' . $key;
                $net_key    = 'net_' . $key;

                $premium_data[$net_key] = bcsub(
                    $premium_data[$gross_key],
                    floatval($premium_data[$refund_key]),
                    IQB_AC_DECIMAL_PRECISION
                );
            }

            /**
             * Pool Refund ??
             */
            if( !$refund_pool && $premium_data['net_amt_pool_premium'] < 0.00 )
            {
                $premium_data['net_amt_pool_premium'] = 0.00;
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Update Endorsement VAT on Premium data
         *
         * @param array $premium_data
         * @param int $fiscal_yr_id
         * @param int $portfolio_id
         * @return array
         */
        private function _update_vat_on_net_premium_data( $premium_data, $fiscal_yr_id, $portfolio_id )
        {
            $this->load->model('portfolio_setting_model');
            $pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);

            if( $pfs_record->flag_apply_vat_on_premium === NULL )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _update_vat_on_net_premium_data()]: No VAT configuration (Apply Vat on Premium) found on portfolio settings for this portfolio. Please contact administrator to update the portfolio settings.");
            }

            $net_amt_vat = 0.00;
            if( $pfs_record->flag_apply_vat_on_premium === IQB_FLAG_YES )
            {
                $this->load->helper('account');
                $taxable_amount = $this->_compute_taxable_amount($premium_data);
                $net_amt_vat     = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);
            }
            $premium_data['net_amt_vat'] = $net_amt_vat;

            return $premium_data;
        }

        // --------------------------------------------------------------------

        private function _compute_taxable_amount($premium_data)
        {
            return  ac_bcsum([
                floatval($premium_data['net_amt_basic_premium'] ?? 0.00),
                floatval($premium_data['net_amt_pool_premium'] ?? 0.00),
                floatval($premium_data['net_amt_stamp_duty'] ?? 0.00),
                floatval($premium_data['net_amt_transfer_fee'] ?? 0.00),
                floatval($premium_data['net_amt_transfer_ncd'] ?? 0.00),
                floatval($premium_data['net_amt_cancellation_fee'] ?? 0.00)
            ],IQB_AC_DECIMAL_PRECISION);
        }


    // --------------------------------------------------------------------

    /**
     * Update Refund Data on Termination
     *
     * @param int $id
     * @return bool
     */
    private function _update_terminate_refund_data($id)
    {
        // Get the Endorsement Record
        $record          = $this->get($id);
        $policy_record   = $this->policy_model->get( $record->policy_id );

        /**
         * NO REFUND???
         *
         * Reset premium fields
         */
        if($record->flag_refund_on_terminate !== IQB_FLAG_YES)
        {
            return $this->_reset($record->id, $record->policy_id);
        }

        // ------------------------------------------------------------

        // ZEROs all gross
        $premium_data = [
            'gross_amt_basic_premium'       => 0.00,
            'gross_amt_commissionable'      => 0.00,
            'gross_amt_agent_commission'    => 0.00,
            'gross_amt_direct_discount'     => 0.00,
            'gross_amt_pool_premium'        => 0.00,
        ];

        /**
         * Task 1: Build Refund For Previous Endorsement
         *
         *  - Apply `rc_ref_basic` on the Previous Endorsement's GROSS Premium Data
         *
         */

        $refund_data = $this->_build_refund_data($record, $policy_record);
        // echo 'Refund : <pre>'; print_r($refund_data);
        // --------------------------------------------------------------------


        /**
         * Task 1: Add refund cols on main $premium_data
         */
        $premium_data = array_merge($premium_data, $refund_data);
        // echo 'Refund with Gross : <pre>'; print_r($premium_data);
        // --------------------------------------------------------------------

        /**
         * Task 4: Compute NET Premium Data = GROSS - REFUND
         */
        $premium_data = $this->_compute_net_premium_data($premium_data);
        // echo 'GROSS, REFUND, NET : <pre>'; print_r($premium_data); exit;
        // --------------------------------------------------------------------


        /**
         * SUM Insured Data
         */
        $premium_data = $this->_update_si_on_premium_data($premium_data, $record, $policy_record);

        // --------------------------------------------------------------------------


        /**
         * Compute VAT
         */
        $premium_data['net_amt_stamp_duty'] = 0.00;
        $premium_data['net_amt_cancellation_fee'] = $record->net_amt_cancellation_fee;
        $premium_data = $this->_update_vat_on_net_premium_data( $premium_data, $policy_record->fiscal_yr_id, $policy_record->portfolio_id );


        // --------------------------------------------------------------------------

        /**
         * Update Refund Data
         */
        parent::update($id, $premium_data, TRUE);


        // --------------------------------------------------------------------------

        /**
         * Save Installment
         */
        // Single Installment
        $installment_data = [
            'dates'             => [date('Y-m-d')], // Today
            'percents'          => [100],
            'installment_type'  => _POLICY_INSTALLMENT_type_by_endorsement_type( $record->txn_type )
        ];
        $this->policy_installment_model->build($record, $installment_data);
    }

    // --------------------------------------------------------------------

    /**
     * Check if given endorsement type is First- Fresh/Renewal
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_first($txn_type)
    {
        $txn_type = (int)$txn_type;
        return $txn_type === IQB_POLICY_ENDORSEMENT_TYPE_FRESH;
    }

    // --------------------------------------------------------------------

    /**
     * Is this Endorsement (UP/DOWN) Manual or Automatic?
     *
     * @TODO: Put this in Portfolio Configuration
     *
     * @param int $portfolio_id
     * @param int $txn_type
     * @return bool
     */
    public function is_endorsement_manual( $portfolio_id, $txn_type )
    {
        // DISABLE FOR NOW
        return FALSE;


        $portfolio_id   = (int)$portfolio_id;
        $txn_type       = (int)$txn_type;

        // List of Portfolio Having Manual Premium Computation
        $manual_portolios = [IQB_SUB_PORTFOLIO_ENG_CAR_ID, IQB_SUB_PORTFOLIO_ENG_EAR_ID, IQB_SUB_PORTFOLIO_MISC_TMI_ID];

        // Allowed Txn Types
        $txn_types = [IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND];

        return in_array($portfolio_id, $manual_portolios) && in_array($txn_type, $txn_types);
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
            'E.policy_id'    => $policy_id,
            'E.flag_current' => IQB_FLAG_ON
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
            'E.policy_id'    => $policy_id,
            'E.status'       => IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE
        ];

        $this->_single_select();

        return $this->db->where($where)
                        ->order_by('E.id', 'desc') // latest active
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

        return $this->db->where('E.id', $id)
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Latest Endorsement Record
     *
     * @param int $id
     * @return object
     */
    public function get_prev_premium_record_by_policy($policy_id, $id = NULL)
    {
        $txn_types = _ENDORSEMENT_premium_only_types();
        $where = [
            'E.id !='       => $id,
            'E.policy_id'   => $policy_id,
            'E.status'      => IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE
        ];

        $this->_single_select();

        return $this->db->where($where)
                        ->where_in('E.txn_type', $txn_types)
                        ->order_by('E.id', 'desc') // latest active
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    private function _single_select()
    {
        $select =   "E.*, " .

                    // Branch and Portfolio
                    "P.category as policy_category, P.insurance_company_id, P.code as policy_code, P.branch_id, P.portfolio_id, P.customer_id, P.object_id, P.status AS policy_status, " .

                    // Transfer Customer Name
                    "C.full_name_en as transfer_customer_name_en, C.full_name_np as transfer_customer_name_np, " .


                    // Endorsement Audit
                    "AE.id AS audit_endorsement_id, AE.object_id AS audit_object_id, AE.customer_id AS audit_customer_id, AE.audit_policy, AE.audit_object, AE.audit_customer, " .

                    /**
                     * User Table - Sales Staff Info ( username, code)
                     */
                    "SU.username as sold_by_username, SU.code AS sold_by_code, " .

                    /**
                     * Agent Name
                     */
                    "A.name as agent_name";

        $this->db->select($select)
                ->from($this->table_name . ' AS E')
                ->join('dt_policies P', 'P.id = E.policy_id')
                ->join('audit_endorsements AE', 'AE.endorsement_id = E.id', 'left')
                ->join('dt_customers C', 'C.id = E.transfer_customer_id', 'left')
                ->join('auth_users SU', 'SU.id = E.sold_by', 'left')
                ->join('master_agents A', 'E.agent_id = A.id', 'left');
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
                        "E.*, " .

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
                    ->from($this->table_name . ' AS E')
                    ->join('dt_policies P', 'P.id = E.policy_id')
                    ->join('master_branches B', 'B.id = P.branch_id')
                    ->join('dt_customers C', 'C.id = P.customer_id')
                    ->join('dt_customers COT', 'COT.id = E.transfer_customer_id', 'left')
                    ->join('master_agents A', 'E.agent_id = A.id', 'left')
                    ->where($where);

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
        return $this->db->order_by('E.id', 'DESC')
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Get the Total Amount of an Endorsement
     *
     * @param int|object $record Endorsement Record or ID
     * @return float
     */
    public function total_amount($record)
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;

        // @TODO: SUM Based on Txn_Type
        return  floatval($record->net_amt_basic_premium) +
                floatval($record->net_amt_pool_premium) +
                floatval($record->net_amt_stamp_duty) +
                floatval($record->net_amt_transfer_fee) +
                floatval($record->net_amt_transfer_ncd) +
                floatval($record->net_amt_cancellation_fee) +
                floatval($record->net_amt_vat);

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
                            "E.id, E.policy_id, E.txn_type, E.issued_date, E.start_date, E.end_date, E.flag_ri_approval, E.flag_current, E.flag_refund_on_terminate, E.status, " .

                            // Branch and Portfolio
                            "P.category as policy_category, P.insurance_company_id, P.code as policy_code, P.branch_id, P.portfolio_id, P.customer_id, P.object_id, P.status AS policy_status, " .

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
     * Get First/Fresh Endorsement Record by Policy
     *
     *
     * @param int $policy_id
     * @return object
     */
    public function get_first_by_policy($policy_id)
    {
        $where = [
            'E.policy_id'    => $policy_id,
            'E.txn_type'     => IQB_POLICY_ENDORSEMENT_TYPE_FRESH
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
        return $this->db->select('E.flag_ri_approval')
                        ->from($this->table_name . ' AS E')
                        ->where('E.policy_id', $policy_id)
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