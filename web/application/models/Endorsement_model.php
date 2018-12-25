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

    protected $fields = ['id', 'policy_id', 'customer_id', 'agent_id', 'sold_by', 'start_date', 'end_date', 'txn_type', 'issued_date', 'amt_sum_insured_object', 'amt_sum_insured_net', 'gross_full_amt_basic_premium', 'gross_full_amt_pool_premium', 'gross_full_amt_commissionable', 'gross_full_amt_agent_commission', 'gross_full_amt_ri_commission', 'gross_full_amt_direct_discount', 'gross_computed_amt_basic_premium', 'gross_computed_amt_pool_premium', 'gross_computed_amt_commissionable', 'gross_computed_amt_agent_commission', 'gross_computed_amt_ri_commission', 'gross_computed_amt_direct_discount', 'refund_amt_basic_premium', 'refund_amt_pool_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_ri_commission', 'refund_amt_direct_discount', 'net_amt_basic_premium', 'net_amt_pool_premium', 'net_amt_commissionable', 'net_amt_agent_commission', 'net_amt_ri_commission', 'net_amt_direct_discount', 'net_amt_stamp_duty', 'net_amt_transfer_fee', 'net_amt_transfer_ncd', 'net_amt_cancellation_fee', 'net_amt_vat', 'percent_ri_commission', 'rc_ref_basic', 'pc_ref_basic', 'rc_ref_pool', 'pc_ref_pool', 'flag_refund_pool', 'te_compute_ref', 'te_loading_percent', 'premium_compute_options', 'cost_calculation_table', 'txn_details', 'remarks', 'transfer_customer_id', 'flag_ri_approval', 'flag_current', 'flag_short_term', 'short_term_config', 'short_term_rate', 'status', 'ri_approved_at', 'ri_approved_by', 'created_at', 'created_by', 'verified_at', 'verified_by', 'updated_at', 'updated_by'];

    // Resetable Fields on Policy/Object Edit, Endorsement Edit
    protected static $nullable_fields = [
        'gross_full_amt_basic_premium',
        'gross_full_amt_pool_premium',
        'gross_full_amt_commissionable',
        'gross_full_amt_agent_commission',
        'gross_full_amt_ri_commission',
        'gross_full_amt_direct_discount',
        'gross_computed_amt_basic_premium',
        'gross_computed_amt_pool_premium',
        'gross_computed_amt_commissionable',
        'gross_computed_amt_agent_commission',
        'gross_computed_amt_ri_commission',
        'gross_computed_amt_direct_discount',
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
                'field' => 'gross_full_amt_basic_premium',
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
     * Get Validation rules on Various Endorsements
     *
     * @param int $txn_type
     * @param int $portfolio_id
     * @param object $policy_record
     * @param bool $formatted
     * @return array
     */
    public function get_validation_rules( $txn_type, $portfolio_id, $policy_record, $formatted = FALSE)
    {
        $v_rules = [];

        /**
         * 1. Basic Validation Rules
         */
        $_rules_basic = $this->_v_rules_basic( $txn_type, $portfolio_id, $policy_record );

        /**
         * 2. Dates
         */
        $_rules_dates = $this->_v_rules_dates( $txn_type, $portfolio_id, $policy_record );


        /**
         * 3. Premium Computation Reference
         */
        $_rules_premium_compute_references = $this->_v_rules_premium_compute_references( $txn_type, $portfolio_id, $policy_record );

        /**
         * 4. Other Validation Rules
         *
         *     Example: If ownership transfer - Customer Info
         */
        $_rules_other_specific = $this->_v_rules_other_specific( $txn_type, $portfolio_id, $policy_record );


        /**
         * Remove Empty Elements
         */
        $v_rules = array_filter(array_merge(
            $_rules_basic,
            $_rules_dates,
            $_rules_premium_compute_references,
            $_rules_other_specific
        ));


        /**
         * Formatted or Sectioned?
         */
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
         * Get Validation Rules - Basic Elements
         *
         * @param type $txn_type
         * @param type $portfolio_id
         * @param object $policy_record
         * @return array
         */
        private function _v_rules_basic( $txn_type, $portfolio_id, $policy_record )
        {
            $txn_type                   = (int)$txn_type;

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

            return ['basic' => $v_rules];
        }

        // ----------------------------------------------------------------

        /**
         * Get Validation Rules - Dates Elements
         *
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
         * @param type $txn_type
         * @param type $portfolio_id
         * @param object $policy_record
         * @return type
         */
        private function _v_rules_dates( $txn_type, $portfolio_id, $policy_record )
        {
            $txn_type   = (int)$txn_type;
            $v_rules    = [
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

            switch ($txn_type)
            {
                case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                    $v_rules = array_merge($v_rules, $this->_v_rules_dates_time_extended( $txn_type, $portfolio_id, $policy_record ));
                    break;

                default:
                    $v_rules = array_merge($v_rules, $this->_v_rules_dates_others( $txn_type, $portfolio_id, $policy_record ));
                    break;
            }

            return ['dates' => $v_rules];
        }

            private function _v_rules_dates_time_extended( $txn_type, $portfolio_id, $policy_record )
            {
                $v_rules = [];

                /**
                 * IF Compute Reference is IQB_ENDORSEMENT_CB_TE_DURATION_PRORATA
                 *      - Only End Date Required
                 * Else
                 *      - Both start and end date required
                 */
                $te_compute_ref = $this->input->post('te_compute_ref');
                $_extra_html_below = '';
                if( empty($te_compute_ref) || $te_compute_ref == IQB_ENDORSEMENT_CB_TE_NET_DIFF )
                {
                    $v_rules = [
                        [
                            'field' => 'start_date',
                            'label' => 'Endorsement Start Date',
                            'rules' => 'trim|required|valid_date|callback__cb_valid_start_date_te', // Cannot be earlier than Policy Start date
                            '_type'             => 'date',
                            '_default'          => date('Y-m-d'),
                            '_id'               => 'start_date',
                            '_extra_attributes' => 'data-provide="datepicker-inline"',
                            '_required' => true
                        ]
                    ];
                }
                else
                {
                    $st_date_obj = new DateTime($policy_record->end_date);
                    $st_date_obj->modify('+1 day');
                    $start_date = $st_date_obj->format('Y-m-d');

                    // Start date is Polidy END date + 1 Day,
                    // Show this in END date's extra html
                    $_extra_html_below = '<div class="text-warning"><strong>Endorsement Start Date</strong>: ' . $start_date . '</div>';
                }
                $_extra_html_below .= '<div class="text-warning"><strong>Policy End Date</strong>: ' . $policy_record->end_date . '</div>';
                $v_rules = array_merge($v_rules, [
                    [
                        'field' => 'end_date',
                        'label' => 'Endorsement End Date',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_end_date_te',
                        '_type'             => 'date',
                        '_default'          => '',
                        '_id'               => 'end_date',
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_extra_html_below' => $_extra_html_below,
                        '_required' => true
                    ]
                ]);

                return $v_rules;

            }

            private function _v_rules_dates_others( $txn_type, $portfolio_id, $policy_record )
            {
                $v_rules = [];

                // Show End date right after startdate
                if( in_array($txn_type, [IQB_ENDORSEMENT_TYPE_TERMINATE, IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE]) )
                {
                    $end_date = date('Y-m-d');
                }
                else
                {
                    $end_date = $policy_record->end_date;
                }


                $v_rules = [
                    [
                        'field' => 'start_date',
                        'label' => 'Endorsement Start Date',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_start_date', // Cannot be earlier than Policy Start date
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d'),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_extra_html_below' => '<div class="text-warning"><strong>Endorsement End Date</strong>: ' . $end_date . '</div>',
                        '_required' => true
                    ]
                ];

                return $v_rules;
            }

        // ----------------------------------------------------------------

        /**
         * Get Validation Rules - Compute References Elements
         *
         *  CASE 1: ENDORSEMENT - UP/DOWN
         *      rc_ref_basic, pc_ref_basic
         *      rc_ref_pool, pc_ref_pool, flag_refund_pool
         *
         *  CASE 2: TERMINATE (Refund)
         *      net_amt_cancellation_fee, rc_ref_basic, rc_ref_pool, flag_refund_pool
         *
         *  CASE 2: ENDORSEMENT - TIME EXTENSION
         *      te_compute_ref, te_loading_percent
         *
         *
         * @param type $txn_type
         * @param type $portfolio_id
         * @param object $policy_record
         * @return array
         */
        private function _v_rules_premium_compute_references( $txn_type, $portfolio_id, $policy_record )
        {
            $txn_type   = (int)$txn_type;
            $v_rules    = [];

            /**
             * Is endorsement Transactional?
             */
            if( !$this->is_transactional( $txn_type ) )
            {
                return $v_rules;
            }


            /**
             * Up/Down Grade Compute Reference - Provided The Endorsement is NOT MANUAL
             */
            if( !$this-> is_endorsement_manual( $portfolio_id, $txn_type ) )
            {
                $ref_dd = $this->compute_reference_dropdown($txn_type);
                switch ($txn_type)
                {
                    case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
                    case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                        $v_rules = [
                            [
                                'field' => 'rc_ref_basic',
                                'label' => 'Basic Premium Refund Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'rc_ref_pool',
                                'label' => 'Pool Premium Refund Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'pc_ref_basic',
                                'label' => 'Basic Premium Compute Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'pc_ref_pool',
                                'label' => 'Basic Premium Compute Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'flag_refund_pool',
                                'label' => 'Refund Pool Premium?',
                                'rules' => 'trim|required|alpha|exact_length[1]|in_list['. implode( ',', array_keys( _FLAG_yes_no_dropdown(FALSE) ) ) .']',
                                '_type'     => 'radio',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => _FLAG_yes_no_dropdown(FALSE),
                                '_show_label' => true,
                                '_required'     => true
                            ],
                        ];
                        break;

                    case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                        $v_rules = [
                            [
                                'field' => 'rc_ref_basic',
                                'label' => 'Basic Premium Refund Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'rc_ref_pool',
                                'label' => 'Pool Premium Refund Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'flag_refund_pool',
                                'label' => 'Refund Pool Premium?',
                                'rules' => 'trim|required|alpha|exact_length[1]|in_list['. implode( ',', array_keys( _FLAG_yes_no_dropdown(FALSE) ) ) .']',
                                '_type'     => 'radio',
                                '_default'  => IQB_ENDORSEMENT_CB_UPDOWN_PRORATA,
                                '_data'     => _FLAG_yes_no_dropdown(FALSE),
                                '_show_label' => true,
                                '_required' => true
                            ],
                        ];
                        break;

                    case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                        $te_compute_ref = $this->input->post('te_compute_ref');
                        $required = $te_compute_ref == IQB_ENDORSEMENT_CB_TE_DURATION_PRORATA ? 'required|' : '';
                        $v_rules = [
                            [
                                'field' => 'te_compute_ref',
                                'label' => 'Time Extension Reference',
                                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode( ',', array_keys( $ref_dd ) ) .']',
                                '_type'     => 'dropdown',
                                '_id'       => 'te_compute_ref',
                                '_data'     => IQB_BLANK_SELECT + $ref_dd,
                                '_required' => true
                            ],
                            [
                                'field' => 'te_loading_percent',
                                'label' => 'Loading Percent (%)',
                                'rules' => 'trim|'.$required.'prep_decimal|decimal|max_length[6]',
                                '_type'     => 'text',
                                '_required' => true
                            ],
                        ];
                        break;

                    default:
                        # code...
                        break;
                }
            }

            return ['compute_references' => $v_rules];
        }

        // ----------------------------------------------------------------

        /**
         * Validation Rules - Speficic to Endorsement Type
         *
         *  Example - Ownership Transfer - Customer Information
         *
         * @param int $txn_type
         * @param int $portfolio_id
         * @param object $policy_record
         * @return array
         */
        private function _v_rules_other_specific($txn_type, $portfolio_id, $policy_record)
        {
            $txn_type   = (int)$txn_type;
            $v_rules    = [];

            /**
             * Is endorsement Transactional?
             */
            if( !$this->is_transactional( $txn_type ) )
            {
                return $v_rules;
            }


            switch ($txn_type)
            {
                case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                    $v_rules = [

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
                        ]
                    ];
                    break;

                default:
                    # code...
                    break;
            }

            // It has to be sectioned already
            return $v_rules;
        }

    // ----------------------------------------------------------------

    /**
     * Get Premium Validation Rules - Fee Elements
     *
     *
     * @param type $txn_type
     * @param type $portfolio_id
     * @param bool $formatted
     * @return array
     */
    public function get_fee_validation_rules( $txn_type, $portfolio_id, $formatted = FALSE)
    {
        $txn_type   = (int)$txn_type;
        $v_rules    = [];

        /**
         * Is endorsement Transactional?
         */
        if( !$this->is_transactional( $txn_type ) )
        {
            return $v_rules;
        }

        switch ($txn_type)
        {
            case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
            case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
            case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                $v_rules = $this->_v_rules_fee_updown( $txn_type, $portfolio_id );
                break;


            case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                $v_rules = $this->_v_rules_fee_ownership_transfer($portfolio_id);
                break;

            case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                $v_rules = $this->_v_rules_fee_terminate($txn_type, $portfolio_id);
                break;

            default:
                # code...
                break;
        }

        if( $formatted )
        {
            return $v_rules;
        }

        return ['fee' => $v_rules];
    }

        // ----------------------------------------------------------------

        private function _v_rules_fee_updown( $txn_type, $portfolio_id )
        {
            $v_rules = [
                [
                    'field' => 'net_amt_stamp_duty',
                    'label' => 'Stamp Duty (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_default'  => 0,
                    '_required' => true
                ]
            ];

            if( $this-> is_endorsement_manual( $portfolio_id, $txn_type ) )
            {
                $v_rules = array_merge($v_rules, [
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
                ]);
            }

            return $v_rules;
        }

        // ----------------------------------------------------------------


        private function _v_rules_fee_ownership_transfer($portfolio_id)
        {

            $motor_portfolio_ids = array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR);

            $v_rules = [
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
                $v_rules[] = [
                    'field' => 'net_amt_transfer_ncd',
                    'label' => 'NCD Return (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_default'  => 0.00,
                    '_help_text' => '<strong>No Claim Discount Return:</strong> This applies only in <strong class="text-red">MOTOR</strong> portfoliios.',
                    '_required' => true
                ];
            }

            return $v_rules;
        }

        // ----------------------------------------------------------------

        private function _v_rules_fee_terminate( $txn_type, $portfolio_id )
        {
            $v_rules = [
                [
                    'field' => 'net_amt_stamp_duty',
                    'label' => 'Stamp Duty (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_default'  => 0,
                    '_required' => true
                ],
                [
                    'field' => 'net_amt_cancellation_fee',
                    'label' => 'Cancellation Charge (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_required' => true
                ],
            ];

            if( $this-> is_endorsement_manual( $portfolio_id, $txn_type ) )
            {
                $v_rules = array_merge($v_rules, [
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
                    ]
                ]);
            }

            return $v_rules;
        }

    // ----------------------------------------------------------------

    /**
     * Get the Compute Reference Dropdown based on Endorsement Type
     *
     * @param int $txn_type
     * @return array
     */
    public function compute_reference_dropdown($txn_type)
    {
        $txn_type = (int)$txn_type;
        $dropdown = [];

        switch ($txn_type)
        {
            case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
            case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
            case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                $dropdown = [
                    IQB_ENDORSEMENT_CB_UPDOWN_FULL      => 'Full/Complete',
                    IQB_ENDORSEMENT_CB_UPDOWN_STR       => 'Short Term',
                    IQB_ENDORSEMENT_CB_UPDOWN_PRORATA   => 'Prorata',
                ];
                break;

            case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                $dropdown = [
                    IQB_ENDORSEMENT_CB_TE_DURATION_PRORATA  => 'Duration Prorata',
                    IQB_ENDORSEMENT_CB_TE_NET_DIFF          => 'Net Difference'
                ];
                break;

            default:
                # code...
                break;
        }

        return $dropdown;
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
        $record = $this->get_current_endorsement($policy_id);

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
        $reset_data = $this->_nullify_premium_data($reset_data);


        $done = parent::update($id, $reset_data, TRUE);

        /**
         * Task 2: Clear Cache (Speciic to this Policy ID)
         */
        $this->_clean_cache_by_policy($policy_id);

        return $done;

    }

    // --------------------------------------------------------------------

    /**
     * Nullify Premium Related Data for an endorsement
     * On Edit Draft
     *
     * @param array $data
     * @return array $data
     */
    private function _nullify_premium_data($data)
    {
        foreach (self::$nullable_fields as $field)
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
        $record = is_numeric($policy_id_or_endorsement_record) ? $this->get_current_endorsement( (int)$policy_id_or_endorsement_record ) : $policy_id_or_endorsement_record;

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
            case IQB_ENDORSEMENT_STATUS_DRAFT:
                $transaction_status = $this->to_draft($record);
                break;

            /**
             * Update Verified date/user and Reset ri_approved date/user to null
             */
            case IQB_ENDORSEMENT_STATUS_VERIFIED:
                $transaction_status = $this->to_verified($record);
                break;

            /**
             * Update RI Approved date/user
             */
            case IQB_ENDORSEMENT_STATUS_RI_APPROVED:
                $transaction_status = $this->to_ri_approved($record);
                break;


            case IQB_ENDORSEMENT_STATUS_VOUCHERED:
                $transaction_status = $this->to_vouchered($record);
                break;


            case IQB_ENDORSEMENT_STATUS_INVOICED:
                $transaction_status = $this->to_invoiced($record);
                break;

            case IQB_ENDORSEMENT_STATUS_ACTIVE:
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
            case IQB_ENDORSEMENT_STATUS_DRAFT:
                $flag_qualifies = $current_status === IQB_ENDORSEMENT_STATUS_VERIFIED;
                break;

            case IQB_ENDORSEMENT_STATUS_VERIFIED:
                $flag_qualifies = $current_status === IQB_ENDORSEMENT_STATUS_DRAFT;
                break;

            case IQB_ENDORSEMENT_STATUS_RI_APPROVED:
                $flag_qualifies = $current_status === IQB_ENDORSEMENT_STATUS_VERIFIED;
                break;

            case IQB_ENDORSEMENT_STATUS_VOUCHERED:
                $flag_qualifies = in_array($current_status, [IQB_ENDORSEMENT_STATUS_VERIFIED, IQB_ENDORSEMENT_STATUS_RI_APPROVED]);
                break;

            case IQB_ENDORSEMENT_STATUS_INVOICED:
                $flag_qualifies = $current_status === IQB_ENDORSEMENT_STATUS_VOUCHERED;
                break;

            // For non-txnal endorsement, its from approved
            case IQB_ENDORSEMENT_STATUS_ACTIVE:
                $flag_qualifies = in_array($current_status, [IQB_ENDORSEMENT_STATUS_VERIFIED, IQB_ENDORSEMENT_STATUS_RI_APPROVED, IQB_ENDORSEMENT_STATUS_INVOICED]);
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
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_DRAFT);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


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
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_VERIFIED);

            /**
             * Task 1: FRESH/RENEWAL - RI Approval Constraint
             */
            if( $this->is_first($record->txn_type) )
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
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_RI_APPROVED);


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
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_VOUCHERED);

            /**
             * Generate Policy Number if Endorsement FRESH/Renewal
             */
            if( $this->is_first($record->txn_type) )
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
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_INVOICED);


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
            $this->_do_status_transaction($record, IQB_ENDORSEMENT_STATUS_ACTIVE);

            if( !$this->is_first($record->txn_type) )
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
                $terminate_policy = $terminate_policy
                                        ||
                                    in_array( $record->txn_type, [
                                        IQB_ENDORSEMENT_TYPE_TERMINATE,
                                        IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
                                    ]);

                if($terminate_policy)
                {
                    $policy_record = $this->policy_model->get($record->policy_id);
                    $this->policy_model->to_canceled($record->policy_id);
                }


                /**
                 * Policy Ownership Transfer
                 */
                elseif($record->txn_type == IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER )
                {
                    $this->transfer_ownership($record);
                }

                /**
                 * Update Policy "END DATE"
                 *
                 * TERMINATE, TIME EXTEND
                 */
                if( in_array( $record->txn_type, [
                    IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
                    IQB_ENDORSEMENT_TYPE_TERMINATE,
                    IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
                ]) )
                {
                    $this->policy_model->update_end_date($record->policy_id, $record->end_date);
                }
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

    /**
     * Update First Endorsement Dates
     *
     * This function is called to update first endorsement's dates so that
     *
     *      Policy Start Date   = First Endorsement Start Date
     *      Policy Issued Date  = First Endorsement Issued Date
     *      Policy END Date     = First Endorsement END Date
     *
     * @param int|obj $policy_record  Policy Record or Policy ID
     * @param int|NULL $endorsement_id Endorsement ID
     * @return bool
     */
    public function update_first_endorsement_dates($policy_record, $endorsement_id = NULL)
    {
        $policy_record = is_numeric($policy_record) ? $this->policy_model->get( (int)$policy_record ) : $policy_record;

        // First Endorsement ID
        $endorsement_id     = $endorsement_id ? $endorsement_id : $this->first_endorsement_id($policy_record->id);
        $endorsement_data   = [
            'issued_date'   => $policy_record->issued_date,
            'start_date'    => $policy_record->start_date,
            'end_date'      => $policy_record->end_date,
        ];

        /**
         * Task 1: Update TXN Data
         */
        $done = parent::update($endorsement_id, $data, TRUE);


        /**
         * Task2: Clear Cache for This Policy
         */
        $this->_clean_cache_by_policy($policy_record->id);

        return $done;
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
            $data  = $this->_refactor_dates($record, $data);

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
                case IQB_ENDORSEMENT_STATUS_DRAFT:
                    $data['verified_at'] = NULL;
                    $data['verified_by'] = NULL;
                    break;

                /**
                 * Update Verified date/user and Reset ri_approved date/user to null
                 */
                case IQB_ENDORSEMENT_STATUS_VERIFIED:
                    $data['verified_at'] = $this->set_date();
                    $data['verified_by'] = $this->dx_auth->get_user_id();
                    $data['ri_approved_at'] = NULL;
                    $data['ri_approved_by'] = NULL;
                break;

                /**
                 * Update RI Approved date/user
                 */
                case IQB_ENDORSEMENT_STATUS_RI_APPROVED:
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
            $keys = [
                'endrsmnt_' . $policy_id,
                'ptxi_bypolicy_' . $policy_id
            ];
            $cache_var = 'endrsmnt_' . $policy_id;
            $this->clear_cache($keys);
        }

    // --------------------------------------------------------------------

        /**
         * Validate and process Dates on Status Update
         *
         * If user has supplied backdate, please make sure that :
         *      1. The user is allowed to enter Backdate
         *      2. If so, the supplied date should be withing backdate limit
         *
         * @param object    $record Policy Record
         * @param array     $data
         * @return array
         */
        public function _refactor_dates($record, $data)
        {
            $txn_type = (int)$record->txn_type;
            /**
             * First Endorsement's Dates are Updated by Policy Tasks
             *  on
             *      - add/edit policy draft
             *      - policy status change
             *
             *  So, we do not do anything here.
             *
             *
             */
            if( $this->is_first($txn_type) )
            {
                return $data;
            }

            // --------------------------------------------------------------------

            /**
             * Backdate On Issued Date
             */
            $old_issued_date = $record->issued_date;
            $new_issued_date = backdate_process($old_issued_date);

            // --------------------------------------------------------------------

            /**
             * On Termination
             *      Issued Date can be past date,
             *      But Start and End Date can not be past date
             */
            if( in_array($txn_type, [IQB_ENDORSEMENT_TYPE_TERMINATE, IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE]) )
            {
                $start_date = $record->start_date;
                $end_date   = date('Y-m-d');
                if( strtotime($start_date) >= strtotime($end_date) )
                {
                    $end_date = $start_date; // Future Dates - Start and End Date but same
                }
                else
                {
                    $data['start_date'] = $end_date; // Start and End on Today
                }

                $data['issued_date']    = $new_issued_date; // may be past date
                $data['end_date']       = $end_date;

                return $data;
            }

            // --------------------------------------------------------------------

            /**
             * Process Start Date
             */

            $old_start_date  = $record->start_date;
            $new_start_date  = backdate_process($old_start_date);

            // -------------------------------------------------------------------


            /**
             * On Time Extended
             *
             * Start and Issued Date Can not be past dates
             */
            if( $txn_type == IQB_ENDORSEMENT_TYPE_TIME_EXTENDED )
            {
                $data['issued_date']    = $new_issued_date;
                $data['start_date']     = $new_start_date;
                return $data;
            }

            // --------------------------------------------------------------------

            /**
             * For Rest of the Types
             *
             * Let's Get Start and Issued Date.
             * END Date = Policy END Date
             */

            $old_start_date  = $record->start_date;
            $new_start_date     = backdate_process($old_start_date);

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
         * Prepare Before Save
         */
        $data = $this->__draft_prepare_data('add', $data, $policy_record);

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
                 * Task 2: Post Save Tasks
                 */
                $this->__draft_post_save_tasks($id, 'add');

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
        /**
         * Prepare Before Save
         */
        $data = $this->__draft_prepare_data('edit', $data, $policy_record);


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
                 * Task 2: Post Save Tasks
                 */
                $this->__draft_post_save_tasks($id, 'edit');

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
         * Prepare Data Before Saving a Draft
         *
         * @param array $data
         * @return bool
         */
        private function __draft_prepare_data($action, $data, $policy_record, $id = NULL)
        {


            if($action == 'add' )
            {
                /**
                 * Last Premium Compute Options
                 */
                $data['premium_compute_options'] = $this->_last_premium_compute_options($data['txn_type'], $data['policy_id']);

            }

            // --------------------------------------------------------------------

            /**
             * Nullify Premium Data on Edit
             *
             * We need to nullify only on the following types
             *
             *      - Time Extended
             *      - Premium Upgrade
             *      - Premium Downgrade
             *
             */
            $txn_type = (int)$data['txn_type'];
            if( $action == 'edit' && in_array($txn_type, [
                IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
                IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
                IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
                IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
            ]))
            {
                $data = $this->_nullify_premium_data($data);
            }



            // --------------------------------------------------------------------


            /**
             * END DATE
             */
            $data = $this->__draft_dates($data, $policy_record->end_date);

            return $data;

        }



        // --------------------------------------------------------------------

        /**
         * Post Draft Save Tasks
         *
         *  - Update Sum Insured
         *  - Update Flag Current
         *
         * @param array $data
         * @return bool
         */
        private function __draft_post_save_tasks($id, $action)
        {
            $record = $this->get( $id );

            // --------------------------------------------------------------------

            /**
             * Update Sum Insured
             */
            $this->__update_sum_inured($record);

            // --------------------------------------------------------------------

            /**
             * Reset Current Flag
             */
            if($action == 'add')
            {
                $this->__reset_flag_current($id, $record->policy_id);
            }

            // --------------------------------------------------------------------

            /**
             * Clear Cache
             */
            $this->_clean_cache_by_policy($record->policy_id);

            // --------------------------------------------------------------------

            return TRUE;
        }

        // --------------------------------------------------------------------

            /**
             * Draft Pre Save Task - Last Premium Compute Options
             *
             * @param array $data
             * @param date $policy_end_date
             * @return NULL|JSON
             */
            private function _last_premium_compute_options($txn_type, $policy_id)
            {
                $pct        = NULL;
                $txn_type  = (int)$txn_type;
                if( $this->is_transactional($txn_type) )
                {
                    $where_in = $this->transactional_only_types();

                    $row = $this->db->select('E.premium_compute_options')
                                    ->from($this->table_name . ' AS E')
                                    ->join('dt_policies P', 'P.id = E.policy_id')
                                    ->where('E.policy_id', $policy_id)
                                    ->where_in('E.txn_type', $where_in)
                                    ->order_by('E.id', 'desc')
                                    ->get()
                                    ->row();

                    $pct = $row->premium_compute_options ?? NULL;
                }
                return $pct;
            }

            // --------------------------------------------------------------------

            /**
             * Draft Pre Save Task - Refactor Dates
             *
             * @param array $data
             * @param date $policy_end_date
             * @return array
             */
            private function __draft_dates($data, $policy_end_date)
            {
                $txn_type   = (int)$data['txn_type'];


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
                 * 3. FOR Time Extended
                 *      if(Duration Prorata)
                 *          Start Date = Policy End Date + 1
                 *          End Date = Form Input > Policy End Date
                 *      else
                 *          Start Date  = Form Input
                 *          End Date    = Form Input
                 *
                 */
                if( $txn_type == IQB_ENDORSEMENT_TYPE_TIME_EXTENDED )
                {
                    $st_date_obj = new DateTime($policy_end_date);
                    $st_date_obj->modify('+1 day');
                    $start_date = $st_date_obj->format('Y-m-d');

                    $te_compute_ref = $data['te_compute_ref'];
                    if($te_compute_ref == IQB_ENDORSEMENT_CB_TE_DURATION_PRORATA )
                    {
                        $data['start_date'] = $start_date;
                    }
                }
                else
                {
                    if( in_array($txn_type, [IQB_ENDORSEMENT_TYPE_TERMINATE, IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE]) )
                    {
                        $start_date = $data['start_date'];
                        $end_date   = date('Y-m-d');
                        if( strtotime($start_date) >= strtotime($end_date) )
                        {
                            $end_date = $start_date;
                        }
                        else
                        {
                            $data['start_date'] = $end_date; // must be today
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

            /**
             * Draft Pre Save Task - Sum Insured (Object, NET)
             *
             * @param int|object $record
             * @param array $data
             * @return array
             */
            private function __update_sum_inured($record)
            {
                $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
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
                $policy_object  = $this->object_model->get($record->object_id);
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

                $data = [
                    'amt_sum_insured_object'    => $gross_si,
                    'amt_sum_insured_net'       => $net_si,
                ];

                parent::update($record->id, $data, TRUE);
            }

            // --------------------------------------------------------------------

            /**
             * Post Draft Save Task - Reset Current Flag
             *
             * @param int $id
             * @param int $policy_id
             * @return bool
             */
            private function __reset_flag_current($id, $policy_id)
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
     * Update Endosrement Data after Object Information has changed
     *
     * Update SI object and NET SI, Reset Premium Fields
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function post_object_update($id, $data)
    {
        /**
         * Nullify Premium Data
         */
        $data = $this->endorsement_model->_nullify_premium_data($data);

        return $this->save($id, $data);
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
                $this->_clean_cache_by_policy($record->policy_id);


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
        $txn_type       = (int)$record->txn_type;
        /**
         * ==================== BUILD DATA =========================
         */

        /**
         * NON-FIRST ENDORSEMENT
         * ---------------------
         */
        if( !$this->is_first( $txn_type) )
        {

            /**
             * Save Premium Based on Type
             */

            switch ($txn_type)
            {
                case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                    $premium_data = $this->_build_time_extended_premium_data( $premium_data, $record, $policy_record );
                    break;

                case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
                case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                    $premium_data = $this->_build_updowngrade_premium_data( $premium_data, $record, $policy_record );
                    break;

                case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                    // Nothing is required
                    break;


                case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                    $premium_data = $this->_build_terminate_premium_data( $premium_data, $record, $policy_record );
                    break;

                default:
                    throw new Exception("Exception [Model: Endorsement_model][Method: save_premium()]: Invalid Endorsement Type.");
                    break;
            }

            /**
             * Manual or Automatic ???
             */
            // if( $this->is_endorsement_manual( $record->portfolio_id, $record->txn_type ) )
            // {
            //     $premium_data = $this->_build_updowngrade_premium_data_manual( $premium_data, $record, $policy_record );
            // }
            // else
            // {
            //     $premium_data = $this->_build_updowngrade_premium_data( $premium_data, $record, $policy_record );
            // }
        }

        /**
         * FIRST ENDORSEMENT - FRESH/RENEWAL
         * ---------------------------------
         */
        else
        {
            /**
             * FAC-Inward Policy???
             * ----------------------
             * IF Policy is FAC-Inward, Regardless of Portfolio - Common to all portfolio
             */
            if($policy_record->category == IQB_POLICY_CATEGORY_FAC_IN )
            {
                $premium_data = $this->_build_fac_in_premium_data($premium_data, $policy_record);
            }
            else
            {
                $premium_data   = $this->_build_fresh_premium_data( $premium_data, $policy_record );
            }

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
        $premium_data = $this->_compute_vat_on_premium_data( $txn_type, $premium_data, $policy_record->fiscal_yr_id, $policy_record->portfolio_id );

        // --------------------------------------------------------------------------

        /**
         * Prepare Other Data
         */
        $premium_data = array_merge($premium_data, [
            'premium_compute_options'   => json_encode($post_data['premium'] ?? NULL),
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
         * Build Premium Data - Time Extended
         *
         * @param array $premium_data
         * @param object $record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return array
         */
        private function _build_time_extended_premium_data( $premium_data, $record, $policy_record )
        {
            /**
             * CASE 1: DURATION PRORATA
             * ----------------------------
             *
             *  A. Get Latest FRESH/UP/DOWN/TIME EXTENDED
             *
             *  B. Copy GROSS FULL OF "A"  to CURRENT GROSS FULL
             *
             *  C. CURRENT GROSS COMPUTED
             *      p1 = ( "A" GROSS Computed ) X Duration Prorata
             *      p2 = p1 X Loading %
             *      GROSS Computed = p1 + p2
             *
             *  D. CURRENT REFUND = ALL NULL
             *
             *  E. CURRENT NET = "C"
             *
             *
             * CASE 2: NET DIFFERENCE (CAN BE REFUND)
             * --------------------------------------
             *
             *  A. Get Latest FRESH/UP/DOWN/TIME EXTENDED
             *
             *  B. CURRENT GROSS FULL = CURRENT PREMIUM COMPUTE FROM PREMIUM FORM
             *
             *  C. CURRENT GROSS COMPUTED = "B"
             *
             *  D. CURRENT REFUND = "A" GROSS COMPUTED
             *
             *  E. CURRENT NET = "C" - "D"
             *
             */
            if( $record->te_compute_ref == IQB_ENDORSEMENT_CB_TE_DURATION_PRORATA )
            {
                $premium_data = $this->__time_extended_premium_data_DP( $premium_data, $record, $policy_record );
            }
            else
            {
                $premium_data = $this->__time_extended_premium_data_ND( $premium_data, $record, $policy_record );
            }

            return $premium_data;
        }

            /**
             * Build Premium Data - Time Extended - Duration Prorata
             *
             * @param array $premium_data
             * @param object $record Current Endorsement Record
             * @param object $policy_record Policy Record
             * @return array
             */
            private function __time_extended_premium_data_DP( $premium_data, $record, $policy_record )
            {
                /**
                 * CASE 1: DURATION PRORATA
                 * ----------------------------
                 *
                 *  A. Get Latest FRESH/UP/DOWN/TIME EXTENDED
                 *
                 *  B. Copy GROSS FULL OF "A"  to CURRENT GROSS FULL
                 *
                 *  C. CURRENT GROSS COMPUTED
                 *      p1 = ( "A" GROSS Computed ) X Duration Prorata
                 *      p2 = p1 X Loading %
                 *      GROSS Computed = p1 + p2
                 *
                 *  D. CURRENT REFUND = ALL NULL
                 *
                 *  E. CURRENT NET = "C"
                 *
                 */
                $keys = [ 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];

                $p_endorsement  = $this->get_prev_premium_record_by_policy($policy_record->id, $record->id);
                if( !$p_endorsement )
                {
                    throw new Exception("Exception [Model: Endorsement_model][Method: __time_extended_premium_data_DP()]: Previous Endorsement Record not found!");
                }


                // COPY GROSS_FULL_* of Previous to  GROSS_FULL_* of Current
                foreach($keys as $col)
                {
                    $gross_col                  = 'gross_full_' . $col;
                    $premium_data[$gross_col]   = $p_endorsement->{$gross_col};
                }

                /**
                 *  C. CURRENT GROSS COMPUTED
                 *      p1 = ( "A" GROSS Computed ) X Duration Prorata
                 *      p2 = p1 X Loading %
                 *      GROSS Computed = p1 + p2
                 */
                $old_duration   = _POLICY_duration($p_endorsement->start_date, $p_endorsement->end_date, 'd');
                $new_duration   = _POLICY_duration($record->start_date, $record->end_date, 'd');
                $prorata        = $new_duration / $old_duration;
                $loading        = floatval($record->te_loading_percent ?? 0.00) / 100.00;


                $P1 = [];
                foreach($keys as $col)
                {
                    $gross_col  = 'gross_computed_'.$col;
                    $old_value  = floatval( $p_endorsement->{$gross_col} ?? 0.00 );
                    $p1         = bcmul( $old_value, $prorata, IQB_AC_DECIMAL_PRECISION );
                    $p2         = bcmul( $p1, $loading, IQB_AC_DECIMAL_PRECISION );
                    $total      = bcadd( $p1, $p2, IQB_AC_DECIMAL_PRECISION );

                    // Assign to GROSS_COMPUTED
                    $premium_data[$gross_col] = $total;
                }



                /**
                 * REFUND on FRESH is ALL ZERO/NULL
                 */
                $premium_data = $this->_reset_refund_premium_data($premium_data);


                /**
                 * Compute NET Premium Data = GROSS - REFUND
                 */
                $premium_data = $this->_compute_net_premium_data($premium_data);


                // --------------------------------------------------------------------

                return $premium_data;
            }

            // --------------------------------------------------------------------


            /**
             * Build Premium Data - Time Extended - Net Difference
             *
             * @param array $premium_data
             * @param object $record Current Endorsement Record
             * @param object $policy_record Policy Record
             * @return array
             */
            private function __time_extended_premium_data_ND( $premium_data, $record, $policy_record )
            {
                /**
                 * CASE 2: NET DIFFERENCE (CAN BE REFUND)
                 * --------------------------------------
                 *
                 *  A. Get Latest FRESH/UP/DOWN/TIME EXTENDED
                 *
                 *  B. CURRENT GROSS FULL = CURRENT PREMIUM COMPUTE FROM PREMIUM FORM
                 *
                 *  C. CURRENT GROSS COMPUTED = "B"
                 *
                 *  D. CURRENT REFUND = "A" GROSS COMPUTED
                 *
                 *  E. CURRENT NET = "C" - "D"
                 */
                $keys = [ 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];

                $p_endorsement  = $this->get_prev_premium_record_by_policy($policy_record->id, $record->id);
                if( !$p_endorsement )
                {
                    throw new Exception("Exception [Model: Endorsement_model][Method: __time_extended_premium_data_ND()]: Previous Endorsement Record not found!");
                }


                foreach($keys as $col)
                {
                    $gross_full_col         = 'gross_full_' . $col;
                    $gross_computed_col     = 'gross_computed_' . $col;
                    $refund_col             = 'refund_' . $col;

                    // C. CURRENT GROSS COMPUTED = "B"
                    $premium_data[$gross_computed_col]  = $premium_data[$gross_full_col] ?? NULL;

                    // D. CURRENT REFUND = "A" GROSS COMPUTED
                    $premium_data[$refund_col] = $p_endorsement->{$gross_computed_col};
                }


                /**
                 * Compute NET Premium Data = GROSS - REFUND
                 */
                $premium_data = $this->_compute_net_premium_data($premium_data);


                // --------------------------------------------------------------------

                return $premium_data;
            }

        // --------------------------------------------------------------------

        /**
         * Build Fresh Premium Data - Terminate & Refund
         *
         *
         * @param array $premium_data
         * @param object $record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return array
         */
        private function _build_terminate_premium_data( $premium_data, $record, $policy_record )
        {
            if( $this->is_refund_allowed($record->policy_id) )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _build_terminate_premium_data()]: The premium can not be refund on a policy having CLAIM.");
            }

            /**
             * Task 1: Build Refund For Previous Endorsement
             *
             *  - Apply `rc_ref_basic` on the Previous Endorsement's GROSS Premium Data
             *
             */

            $refund_data = $this->_build_refund_data($record, $policy_record);
            // echo 'Refund : <pre>'; print_r($refund_data);exit;
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
            $flag_refund_pool = $record->flag_refund_pool == IQB_FLAG_YES;
            $premium_data = $this->_compute_net_premium_data($premium_data, $flag_refund_pool);
            // echo 'GROSS, REFUND, NET : <pre>'; print_r($premium_data); exit;
            // --------------------------------------------------------------------

            /**
             * NET Total +ve ???
             */
            $premium_data['txn_type'] = $record->txn_type;
            $total_amount = $this->grand_total((object)$premium_data);
            if( $total_amount > 0 )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _build_terminate_premium_data()]: NET REFUND AMOUNT is positive.");
            }

            // --------------------------------------------------------------------

            return $premium_data;
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
             *  - Apply Rates on GROSS_FULL --> GROSS_COMPUTED
             */

            /**
             * Basic and Pool Refund Rate
             */
            $basic_rate = $this->_compute_reference_rate( $record->pc_ref_basic, $record, $policy_record );
            $pool_rate  = $this->_compute_reference_rate( $record->pc_ref_pool, $record, $policy_record );
            $rates      = [
                'basic_rate'    => $basic_rate,
                'pool_rate'     => $pool_rate,
            ];

            // Apply the computation rate on GROSS premium Data
            $premium_data = $this->_apply_rates_on_gross_data( $premium_data, $rates );
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
            $flag_refund_pool = $record->flag_refund_pool == IQB_FLAG_YES;
            $premium_data = $this->_compute_net_premium_data($premium_data, $flag_refund_pool);
            // echo 'GROSS, REFUND, NET : <pre>'; print_r($premium_data); exit;
            // --------------------------------------------------------------------


            /**
             * Task 5: Update the txn_type based on NET Premium
             *
             * NOTE: Type must be either UP/DOWN
             */
            $premium_data = $this->_assign_txn_type_on_premium_data( $premium_data );


            // --------------------------------------------------------------------

            /**
             * Task 4: Claimed Policy?
             *
             * We can not refund a claimed policy!
             */
            $txn_type = $premium_data['txn_type'];
            if( $this->is_txn_type_refundable($txn_type) && !$this->is_refund_allowed($record->policy_id) )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _build_updowngrade_premium_data()]: The premium can not be refund on a policy having CLAIM.");
            }


            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Build Fresh Premium Data - FAC IN
         *
         *
         * @param array $premium_data
         * @param object $policy_record
         * @return array
         */
        private function _build_fac_in_premium_data( $premium_data, $policy_record )
        {
            $gross_full_amt_basic_premium   = $premium_data['gross_full_amt_basic_premium'];
            $percent_ri_commission          = $premium_data['percent_ri_commission'];

            // Compute amt_ri_commission
            $comm_percent                   = bcdiv($percent_ri_commission, 100.00, IQB_AC_DECIMAL_PRECISION);
            $gross_full_amt_ri_commission   = bcmul( $gross_full_amt_basic_premium, $comm_percent, IQB_AC_DECIMAL_PRECISION);

            // RI COMMISSIONs
            $premium_data = array_merge($premium_data,[
                'gross_full_amt_ri_commission'      => $gross_full_amt_ri_commission,
                'gross_computed_amt_ri_commission'  => $gross_full_amt_ri_commission,
                'refund_amt_ri_commission'          => NULL,
                'net_amt_ri_commission'             => $gross_full_amt_ri_commission,
            ]);


            // COPY GROSS_FULL -> GROSS_COMPUTED
            $premium_data = $this->_copy_gross_full_to_gross_computed( $premium_data );

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
                $rates      = [
                    'basic_rate'    => $rate,
                    'pool_rate'     => $rate,
                ];
                $premium_data   = $this->_apply_rates_on_gross_data( $premium_data, $rates);

                // Update Short Term Related Info on Endorsement as well
                $premium_data['flag_short_term']    = IQB_FLAG_YES;
                $premium_data['short_term_config']  = IQB_ENDORSEMENT_SPR_CONFIG_BOTH;
                $premium_data['short_term_rate']    = $rate_percent;
            }
            else
            {
                // COPY GROSS_FULL -> GROSS_COMPUTED
                $premium_data = $this->_copy_gross_full_to_gross_computed( $premium_data );
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
            if( $record->flag_refund_pool == IQB_FLAG_NO )
            {
                $premium_data['net_amt_pool_premium'] = 0.00;
            }

            // --------------------------------------------------------------------

            /**
             * NULLIFY GROSS & REFUND & Other Data
             */
            $premium_data = array_merge($premium_data, [

                // Gross, refund basic
                'gross_full_amt_basic_premium' => NULL,
                'gross_computed_amt_basic_premium' => NULL,
                'refund_amt_basic_premium' => NULL,

                // Gross, refund pool
                'gross_full_amt_pool_premium' => NULL,
                'gross_computed_amt_pool_premium' => NULL,
                'refund_amt_pool_premium' => NULL,

                // Gross, refund commission
                'gross_full_amt_commissionable'      => NULL,
                'gross_computed_amt_commissionable'      => NULL,
                'refund_amt_commissionable'     => NULL,
                'gross_amt_agent_commission'    => NULL,
                'refund_amt_agent_commission'   => NULL,

                // NO Direct Discount
                'gross_full_amt_direct_discount'     => NULL,
                'gross_computed_amt_direct_discount'     => NULL,
                'refund_amt_direct_discount'    => NULL,
                'net_amt_direct_discount'       => NULL,

                // NO Transfer fee, NCD or Cancellation fee
                'net_amt_transfer_fee'      => NULL,
                'net_amt_transfer_ncd'      => NULL,
                'net_amt_cancellation_fee'  => NULL,

                // Percent RI Commission
                'percent_ri_commission'     => NULL,
                'gross_full_amt_ri_commission'   => NULL,
                'gross_computed_amt_ri_commission'   => NULL,
                'refund_amt_ri_commission'  => NULL,
                'net_amt_ri_commission'     => NULL,

                // Compute Reference - Set Both To Manual
                'pc_ref_basic' => IQB_ENDORSEMENT_CB_UPDOWN_MANUAL,
                'rc_ref_basic'  => IQB_ENDORSEMENT_CB_UPDOWN_MANUAL
            ]);


            // --------------------------------------------------------------------


            /**
             * Task 5: Update the txn_type based on NET Premium
             *
             * NOTE: Type must be either UP/DOWN
             */
            if( in_array($record->txn_type, [IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND]) )
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
                $premium_data['txn_type'] == IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND
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
                case IQB_ENDORSEMENT_CB_UPDOWN_FULL:
                    $rate = 1;
                    break;

                /**
                 * Short Term Rate
                 */
                case IQB_ENDORSEMENT_CB_UPDOWN_STR:
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
                case IQB_ENDORSEMENT_CB_UPDOWN_PRORATA:
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
         * Compute and Return Refund/Downgrade Rate for
         * Basic and Pool Premium
         *
         * @param int $compute_reference
         * @param object $prev_record Previous Endorsement Record
         * @param object $cur_record Current Endorsement Record
         * @param object $policy_record Policy Record
         * @return decimal
         */
        private function _compute_refund_rate( $compute_reference, $prev_record, $cur_record, $policy_record )
        {
            $refund_rate               = FALSE;
            $compute_reference  = (int)$compute_reference;
            switch ($compute_reference)
            {
                /**
                 * No computation needed. The whole amount is used.
                 */
                case IQB_ENDORSEMENT_CB_UPDOWN_FULL:
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
                case IQB_ENDORSEMENT_CB_UPDOWN_STR:
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
                case IQB_ENDORSEMENT_CB_UPDOWN_PRORATA:
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
                throw new Exception("Exception [Model: Endorsement_model][Method: _compute_refund_rate()]: Invalid Premium Computation Reference.");
            }

            return $refund_rate;
        }

        // --------------------------------------------------------------------

        /**
         * Rebuild Premium Data Applying - Short Term Rate or Prorata Rate on GROSS Premium Data
         *
         *
         * @param array $premium_data
         * @param array $rates  Basic and Pool Rates
         * @return array
         */
        private function _apply_rates_on_gross_data( $premium_data, $rates )
        {
            /**
             * APPLY COMPUTATION REFERENCE TO PREMIUM DATA
             * --------------------------------------------
             *  gross_full -> gross_computed
             */

            $basic_rate = $rates['basic_rate'];
            $pool_rate  = $rates['pool_rate'];
            $keys       = [ 'amt_basic_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];
            $full_prefix     = 'gross_full_';
            $computed_prefix = 'gross_computed_';
            foreach($keys as $key)
            {
                $full_key       = $full_prefix . $key;
                $computed_key   = $computed_prefix . $key;

                $premium_data[$computed_key] = bcmul( floatval($premium_data[$full_key]), $basic_rate, IQB_AC_DECIMAL_PRECISION);
            }

            // Apply on Pool
            $key            = 'amt_pool_premium';
            $full_key       = $full_prefix . $key;
            $computed_key   = $computed_prefix . $key;
            $premium_data[$computed_key] = bcmul( floatval($premium_data[$full_key]), $pool_rate, IQB_AC_DECIMAL_PRECISION);

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Copy Gross Full Data into Gross Computed
         *
         * @param array $premium_data
         * @return array
         */
        private function _copy_gross_full_to_gross_computed( $premium_data )
        {
            $keys  = [ 'amt_basic_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_direct_discount'];

            $full_prefix     = 'gross_full_';
            $computed_prefix = 'gross_computed_';

            foreach($keys as $key)
            {
                $full_key       = $full_prefix . $key;
                $computed_key   = $computed_prefix . $key;

                $premium_data[$computed_key] = $premium_data[$full_key] ?? NULL;
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Rebuild Premium Data Applying - Short Term Rate or Prorata Rate on REFUND Premium Data
         *
         *
         * @param array $premium_data
         * @param array $rates  Basic and Pool Rates
         * @return array
         */
        private function _apply_rates_on_refund_data( $premium_data, $rates )
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
            $basic_rate = $rates['basic_rate'];
            $pool_rate  = $rates['pool_rate'];
            $keys       = [ 'refund_amt_basic_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_direct_discount'];
            foreach($keys as $key)
            {
                $premium_data[$key] = bcmul( $premium_data[$key], $basic_rate, IQB_AC_DECIMAL_PRECISION);
            }

            // Pool Rate
            $premium_data['refund_amt_pool_premium'] = bcmul( $premium_data['refund_amt_pool_premium'], $pool_rate, IQB_AC_DECIMAL_PRECISION);

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
                // COPY GROSS_COMPUTED_* of Previous Record into REFUND_*
                foreach($keys as $col)
                {
                    $gross_col  = 'gross_computed_'.$col;
                    $refund_col = 'refund_'.$col;
                    $refund_data[$refund_col] = $p_endorsement->{$gross_col};
                }
                // echo '<pre> REFUND FULL: '; print_r($refund_data);


                /**
                 * Apply rates on REFUND_*
                 */
                $basic_rate = $this->_compute_refund_rate( $record->rc_ref_basic, $p_endorsement, $record, $policy_record );
                $pool_rate  = $this->_compute_refund_rate( $record->rc_ref_pool, $p_endorsement, $record, $policy_record );
                $rates      = [
                    'basic_rate'    => $basic_rate,
                    'pool_rate'     => $pool_rate,
                ];
                // echo '<pre> Rates: '; print_r($rates);

                // Apply the computation rate
                $refund_data = $this->_apply_rates_on_refund_data( $refund_data, $rates );
                // echo '<pre> Refund Rate: '; print_r($refund_data); exit;
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
                $premium_data['txn_type'] = IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE;
            }
            elseif( $total_premium < 0)
            {
                $premium_data['txn_type'] = IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND;
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
                $gross_key  = 'gross_computed_' . $key;
                $refund_key = 'refund_' . $key;
                $net_key    = 'net_' . $key;

                $gross_computed = floatval($premium_data[$gross_key] ?? 0); // Can be NULL or NOT SENT (e.g. in Terminate & Refund)
                $refund         = floatval($premium_data[$refund_key] ?? 0);

                $premium_data[$net_key] = bcsub(
                    $gross_computed,
                    $refund,
                    IQB_AC_DECIMAL_PRECISION
                );
            }

            /**
             * Pool Refund ??
             */
            if( !$refund_pool )
            {
                $premium_data['net_amt_pool_premium'] = 0.00;
            }

            return $premium_data;
        }

        // --------------------------------------------------------------------

        /**
         * Update Endorsement VAT on Premium data
         *
         * @param int $txn_type
         * @param array $premium_data
         * @param int $fiscal_yr_id
         * @param int $portfolio_id
         * @return array
         */
        private function _compute_vat_on_premium_data( $txn_type, $premium_data, $fiscal_yr_id, $portfolio_id )
        {
            $this->load->model('portfolio_setting_model');
            $pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);

            if( $pfs_record->flag_apply_vat_on_premium === NULL )
            {
                throw new Exception("Exception [Model: Endorsement_model][Method: _compute_vat_on_premium_data()]: No VAT configuration (Apply Vat on Premium) found on portfolio settings for this portfolio. Please contact administrator to update the portfolio settings.");
            }

            $net_amt_vat = 0.00;
            if( $pfs_record->flag_apply_vat_on_premium === IQB_FLAG_YES )
            {
                $this->load->helper('account');
                $taxable_amount = $this->_compute_taxable_amount($txn_type, $premium_data);
                $net_amt_vat     = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);
            }
            $premium_data['net_amt_vat'] = $net_amt_vat;

            return $premium_data;
        }

        // --------------------------------------------------------------------

        private function _compute_taxable_amount($txn_type, $premium_data)
        {
            $taxable_amount = 0.00;
            $txn_type       = (int)$txn_type;
            switch ($txn_type)
            {
                case IQB_ENDORSEMENT_TYPE_FRESH:
                case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
                case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
                case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                    $taxable_amount = ac_bcsum([
                        floatval($premium_data['net_amt_basic_premium'] ?? 0.00),
                        floatval($premium_data['net_amt_pool_premium'] ?? 0.00),
                        floatval($premium_data['net_amt_stamp_duty'] ?? 0.00),
                    ],IQB_AC_DECIMAL_PRECISION);
                    break;



                case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                    $taxable_amount = ac_bcsum([
                        floatval($premium_data['net_amt_stamp_duty'] ?? 0.00),
                        floatval($premium_data['net_amt_transfer_fee'] ?? 0.00),
                        floatval($premium_data['net_amt_transfer_ncd'] ?? 0.00),
                    ],IQB_AC_DECIMAL_PRECISION);
                    break;


                case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                    $taxable_amount = ac_bcsum([
                        floatval($premium_data['net_amt_basic_premium'] ?? 0.00),
                        floatval($premium_data['net_amt_pool_premium'] ?? 0.00),
                        floatval($premium_data['net_amt_stamp_duty'] ?? 0.00),
                        floatval($premium_data['net_amt_cancellation_fee'] ?? 0.00)
                    ],IQB_AC_DECIMAL_PRECISION);
                    break;

                default:
                    # code...
                    break;
            }

            return $taxable_amount;
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
        return $txn_type === IQB_ENDORSEMENT_TYPE_FRESH;
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
        $txn_types = [IQB_ENDORSEMENT_TYPE_TIME_EXTENDED, IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND];

        return in_array($portfolio_id, $manual_portolios) && in_array($txn_type, $txn_types);
    }

    // --------------------------------------------------------------------

    /**
     * Get Endorsement Type Dropdown
     *
     * @return array
     */
    public function type_dropdown( $exclude_fresh, $flag_blank_select = TRUE)
    {
        $dropdown = [
            IQB_ENDORSEMENT_TYPE_FRESH                 => 'Fresh',
            IQB_ENDORSEMENT_TYPE_TIME_EXTENDED         => 'Time Extended',
            IQB_ENDORSEMENT_TYPE_GENERAL               => 'General (Nil)',
            IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER    => 'Ownership Transfer',
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE       => 'Premium Upgrade',
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND        => 'Premium Downgrade',
            IQB_ENDORSEMENT_TYPE_TERMINATE             => 'Terminate',
            IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE  => 'Refund & Terminate'
        ];

        /**
         * Need the Endorsement Only Dropdown?
         */
        if($exclude_fresh)
        {
            unset($dropdown[IQB_ENDORSEMENT_TYPE_FRESH]);
        }

        /**
         * Add Blank Select?
         */
        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }


    // --------------------------------------------------------------------

    /**
     * Transactional Only Types
     *
     * @param void
     * @return array
     */
    public function transactional_only_types( )
    {
        return [
            IQB_ENDORSEMENT_TYPE_FRESH,
            IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
            IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
        ];
    }


    // --------------------------------------------------------------------

    /**
     * Is this Endorsement Transactional?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_transactional( $txn_type )
    {
        $txn_type  = (int)$txn_type;
        return in_array( $txn_type, $this->transactional_only_types() );
    }


    // --------------------------------------------------------------------

    /**
     * Is Premium Already Computed in this Endorsement?
     *
     * If endorsement is not transactional, it returns NOT_REQUIRED FLAG
     * Else FLAG YES/NO
     *
     * @param int|object $record
     * @return char
     */
    public function is_premium_computed( $record )
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        $txn_type  = (int)$record->txn_type;

        if( !$this->is_transactional($txn_type) )
        {
            return IQB_FLAG_NOT_REQUIRED;
        }

        $flag    = IQB_FLAG_NO;
        $premium = $this->grand_total($record);
        if($premium != 0)
        {
            $flag = IQB_FLAG_YES;
        }

        return $flag;
    }

    // --------------------------------------------------------------------

    /**
     * Is this Endorsement RI-Distributable?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_ri_distributable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_ENDORSEMENT_TYPE_FRESH,
            IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
            IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
        ];

        return in_array($txn_type, $allowed_types);
    }


    // --------------------------------------------------------------------

    /**
     * Is this Endorsement Invoicable?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_invoicable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_ENDORSEMENT_TYPE_FRESH,
            IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
        ];

        return in_array($txn_type, $allowed_types);
    }

    // --------------------------------------------------------------------

    /**
     * Is this Endorsement Refundable?
     *
     * @param int|object $record or ID
     * @return bool
     */
    public function is_refundable( $record )
    {
        $record             = is_numeric($record) ? $this->get( (int)$record ) : $record;
        $txn_type           = (int)$record->txn_type;
        $flag_refundable    = FALSE;

        if( $this->is_txn_type_refundable($txn_type) )
        {
            $flag_refundable = TRUE;
        }

        /**
         * Time Extended
         * In NET DIFFERENCE type, it can be refundable
         */
        else if( $txn_type == IQB_ENDORSEMENT_TYPE_TIME_EXTENDED )
        {
            $flag_refundable = $this->is_time_extended_refundable($record);
        }

        return $flag_refundable;
    }

    // --------------------------------------------------------------------

    /**
     * Is Endorsement Type Refundable?
     *
     * @param type $txn_type
     * @return type
     */
    public function is_txn_type_refundable($txn_type)
    {
        $txn_type           = (int)$txn_type;
        $allowed_types      = [
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
            IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE
        ];

        return in_array($txn_type, $allowed_types);
    }

    // --------------------------------------------------------------------

    /**
     * Is Time Extended Endorsement Refundable?
     *
     * @param int|object $record or ID
     * @return bool
     */
    public function is_time_extended_refundable($record)
    {
        $record     = is_numeric($record) ? $this->get( (int)$record ) : $record;
        $txn_type   = (int)$record->txn_type;

        if( $txn_type != IQB_ENDORSEMENT_TYPE_TIME_EXTENDED )
        {
            throw new Exception("Exception [Model:Endorsement_model][Method: is_time_extended_refundable()]: Invalid 'Endorsement Type'.");
        }

        $total_premium      = $this->total_premium($record);
        if($total_premium < 0 )
        {
            return TRUE;
        }

        return FALSE;
    }


    // --------------------------------------------------------------------

    /**
     * Is Refund Allowed?
     *
     * If policy has been claimed, NO REFUND AT ALL!!!
     *
     * @param int $policy_id
     * @return bool
     */
    public function is_refund_allowed($policy_id)
    {
        $allowed = TRUE;
        $this->load->model('claim_model');
        if( $this->claim_model->has_policy_claim($policy_id) )
        {
            $allowed = FALSE;
        }

        return $allowed;
    }

    // --------------------------------------------------------------------

    /**
     * Is Policy Editable in this Endorsement?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_policy_editable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_ENDORSEMENT_TYPE_GENERAL,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];

        return in_array($txn_type, $allowed_types);
    }

    // --------------------------------------------------------------------

    /**
     * Is Object Editable in this Endorsement?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_object_editable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_ENDORSEMENT_TYPE_GENERAL,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];

        return in_array($txn_type, $allowed_types);
    }

    // --------------------------------------------------------------------

    /**
     * Is Customer Editable in this Endorsement?
     *
     * @param int $txn_type
     * @return bool
     */
    public function is_customer_editable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_ENDORSEMENT_TYPE_GENERAL,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];

        return in_array($txn_type, $allowed_types);
    }

    // --------------------------------------------------------------------

    /**
     * Is Deletable by Type?
     *
     * Only NON-Fresh, Draft Endorsement are deletable
     *
     * @param int $txn_type
     * @param char $status
     * @return bool
     */
    public function is_deletable( $txn_type, $status )
    {
        $txn_type       = (int)$txn_type;

        // ENDORSEMENT ONLY TYPES
        $allowed_types  = $this->type_dropdown( TRUE, FALSE);

        return in_array($txn_type, array_keys($allowed_types)) && $status === IQB_ENDORSEMENT_STATUS_DRAFT;
    }


    // --------------------------------------------------------------------

    /**
     * Get Current Endorsement Record for Supplied Policy
     *
     * @param int $policy_id
     * @return object
     */
    public function get_current_endorsement($policy_id)
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
            'E.status'       => IQB_ENDORSEMENT_STATUS_ACTIVE
        ];

        $this->_single_select();

        return $this->db->where($where)
                        ->order_by('E.id', 'desc') // latest active
                        ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get First Endorsement's ID by Policy
     *
     * @param int $policy_id
     * @return int
     */
    public function first_endorsement_id($policy_id)
    {
        $where = [
            'E.policy_id' => $policy_id,
            'E.txn_type' => IQB_ENDORSEMENT_TYPE_FRESH
        ];
        return $this->db->select('E.id')
                        ->from($this->table_name . ' E')
                        ->where($where)
                        ->get()->row()->id;
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
        $txn_types = [
            IQB_ENDORSEMENT_TYPE_FRESH,
            IQB_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];
        $where = [
            'E.id !='       => $id,
            'E.policy_id'   => $policy_id,
            'E.status'      => IQB_ENDORSEMENT_STATUS_ACTIVE
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
                    "P.category as policy_category, P.insurance_company_id, P.code as policy_code, P.branch_id, P.portfolio_id, P.customer_id, P.object_id, P.status AS policy_status, P.start_date as policy_start_date, P.end_date as policy_end_date, " .

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
     * @param bool $with_vat
     * @return float
     */
    public function grand_total($record, $with_vat = TRUE)
    {
        $record         = is_numeric($record) ? $this->get( (int)$record ) : $record;
        $total_amount   = 0.00;
        $txn_type       = (int)$record->txn_type;

        switch ($txn_type)
        {
            case IQB_ENDORSEMENT_TYPE_FRESH:
            case IQB_ENDORSEMENT_TYPE_TIME_EXTENDED:
            case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
            case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                $total_amount = ac_bcsum([
                    floatval($record->net_amt_basic_premium ?? 0.00),
                    floatval($record->net_amt_pool_premium ?? 0.00),
                    floatval($record->net_amt_stamp_duty ?? 0.00)
                ],IQB_AC_DECIMAL_PRECISION);



                break;



            case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                $total_amount = ac_bcsum([
                    floatval($record->net_amt_stamp_duty ?? 0.00),
                    floatval($record->net_amt_transfer_fee ?? 0.00),
                    floatval($record->net_amt_transfer_ncd ?? 0.00)
                ],IQB_AC_DECIMAL_PRECISION);
                break;


            case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
                $total_amount = ac_bcsum([
                    floatval($record->net_amt_basic_premium ?? 0.00),
                    floatval($record->net_amt_pool_premium ?? 0.00),
                    floatval($record->net_amt_stamp_duty ?? 0.00),
                    floatval($record->net_amt_cancellation_fee ?? 0.00)
                ],IQB_AC_DECIMAL_PRECISION);
                break;

            default:
                # code...
                break;
        }

        if($total_amount != 0 && $with_vat == TRUE )
        {
            $total_amount = bcadd($total_amount, floatval($record->net_amt_vat ?? 0.00), IQB_AC_DECIMAL_PRECISION);
        }

        return $total_amount;
    }

    // --------------------------------------------------------------------

    /**
     * Get the Total Premium Excluding VAT and Stamp Duty
     *
     * @param int|object $record Endorsement Record or ID
     * @param bool $with_vat
     * @return float
     */
    public function total_premium($record)
    {
        $total   = $this->grand_total($record, FALSE);
        $total = bcsub($total, floatval($record->net_amt_stamp_duty ?? 0.00), IQB_AC_DECIMAL_PRECISION );

        return $total;
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
                            "E.id, E.policy_id, E.txn_type, E.issued_date, E.start_date, E.end_date, E.flag_ri_approval, E.flag_current, E.status, E.te_compute_ref, " .

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
    public function get_first($policy_id)
    {
        $where = [
            'E.policy_id'    => $policy_id,
            'E.txn_type'     => IQB_ENDORSEMENT_TYPE_FRESH
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
            $this->__set_flag_current_to_latest($record->policy_id);

            /**
             * Task 3: Clear Cache for this Policy (List of txn for this policy)
             */
            $this->_clean_cache_by_policy($record->policy_id);

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

        private function __set_flag_current_to_latest($policy_id)
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