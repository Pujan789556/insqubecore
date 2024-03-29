<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tariff_motor_model extends MY_Model
{
    protected $table_name = 'master_tariff_motor';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'portfolio_id', 'cvc_type', 'fiscal_yr_id', 'ownership', 'default_premium', 'tariff', 'no_claim_discount', 'dr_mcy_disabled_friendly', 'rate_pvc_on_hire', 'dr_cvc_on_personal_use', 'dr_voluntary_excess', 'pramt_compulsory_excess', 'accident_premium', 'riks_group', 'pramt_towing', 'trolly_tariff', 'insured_value_tariff', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

    /**
     * We only insert Fiscal Year To Insert Default Records
     */
    protected $insert_validate_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 28 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('ph_motor');

        // Valication Rule
        // $this->validation_rules();
        $this->insert_validate_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Validation Rules - Motorcycle (Two Wheelers)
     *
     * @param type|bool $formatted
     * @return type
     */
    public function motorcycle_validation_rules($formatted = FALSE)
    {
        $v_rules = $this->_common_validation_rules();
        $v_rules += [
            /**
             * JSON : Tarrif
             * --------------
             */
            'tariff' => [
                [
                    'field' => 'tariff[ec_type][]',
                    'label' => 'Engine Capacity Type',
                    'rules' => 'trim|required|alpha|max_length[2]',
                    '_type'     => 'dropdown',
                    '_show_label' => false,
                    '_key'      => 'ec_type',
                    '_data'     => _OBJ_MOTOR_ec_unit_tariff_dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_min][]',
                    'label' => 'Capacity Min (CC)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_max][]',
                    'label' => 'Capacity Max (CC)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_max',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[default_age_max][]',
                    'label' => 'Default Age Max (yrs)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_age_max',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[default_rate][]',
                    'label' => 'Default Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_rate',
                    '_required' => true
                ],

                // Other Age Range Rate
                [
                    'field' => 'tariff[age1_min][]',
                    'label' => 'Second Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age1_max][]',
                    'label' => 'Second Age Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_max',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate1][]',
                    'label' => 'Rate on Second Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate1',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age2_min][]',
                    'label' => 'Third Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age2_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate2][]',
                    'label' => 'Rate on Third Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate2',
                    '_required' => true
                ],

                // Third Party Amount
                [
                    'field' => 'tariff[third_party][]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'third_party',
                    '_required' => true
                ],
            ],

            // Disable Friendly Discount
            'dr_mcy_disabled_friendly' => [
                [
                    'field' => 'dr_mcy_disabled_friendly',
                    'label' => 'Disable Friendly Discount (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ]
        ];

        // return formatted?
        $fromatted_v_rules = [];
        if($formatted === TRUE)
        {
            foreach ($v_rules as $section => $section_rules)
            {
                $fromatted_v_rules = array_merge($fromatted_v_rules, $section_rules);
            }
            return $fromatted_v_rules;
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Validation Rules - Private Vehicle
     *
     * @param bool $formatted
     * @return type
     */
    public function private_vehicle_validation_rules($formatted = FALSE)
    {
        $v_rules = $this->_common_validation_rules();
        $v_rules += [
            /**
             * JSON : Tarrif
             * --------------
             */
            'tariff' => [
                [
                    'field' => 'tariff[ec_type][]',
                    'label' => 'Engine Capacity Type',
                    'rules' => 'trim|required|alpha|max_length[2]',
                    '_type'     => 'dropdown',
                    '_show_label' => false,
                    '_key'      => 'ec_type',
                    '_data'     => _OBJ_MOTOR_ec_unit_tariff_dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_min][]',
                    'label' => 'Capacity Min (CC|KW|HP)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_max][]',
                    'label' => 'Capacity Max (CC|KW|HP)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_max',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[default_age_max][]',
                    'label' => 'Default Age Max (yrs)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_age_max',
                    '_required' => true
                ],

                // SI ko first 20 lakhs
                [
                    'field' => 'tariff[default_si_amount][]',
                    'label' => 'Basic SI Amount (Rs)',
                    'rules' => 'trim|required|integer|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_si_amount',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[default_si_rate][]',
                    'label' => 'Rate for Basic SI(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_si_rate',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[remaining_si_rate][]',
                    'label' => 'Rate for Remaining SI(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'remaining_si_rate',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[minus_amount][]',
                    'label' => 'Default To-Minus-Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'minus_amount',
                    '_required' => true
                ],

                // Other Age Range Rate
                [
                    'field' => 'tariff[age1_min][]',
                    'label' => 'Second Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age1_max][]',
                    'label' => 'Second Age Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_max',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate1][]',
                    'label' => 'Rate on Second Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate1',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age2_min][]',
                    'label' => 'Third Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age2_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate2][]',
                    'label' => 'Rate on Third Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate2',
                    '_required' => true
                ],

                // Third Party Amount
                [
                    'field' => 'tariff[third_party][]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'third_party',
                    '_required' => true
                ],
            ],

            // Private Vehicle - Private Hire Rate
            'rate_pvc_on_hire' => [
                [
                    'field' => 'rate_pvc_on_hire',
                    'label' => 'Private Hire (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Private/Commercial Vehicle - Towing Premium Amount
            'pramt_towing' => [
                [
                    'field' => 'pramt_towing',
                    'label' => 'Towing Premium Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * Private/Commercial Vehicle - JSON : Trolly/Trailer Tarrif
             * ---------------------------------------------------------
             *
             * Structure:
             *  {
             *      rate: 0.123,
             *      minus_amount: 0.12,
             *      third_party: 500,
             *      compulsory_excess: 400
             *  }
             */
            'trolly_tariff' => [
                [
                    'field' => 'trolly_tariff[rate]',
                    'label' => 'Premium Rate (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[minus_amount]',
                    'label' => 'To Minus Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[third_party]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[compulsory_excess]',
                    'label' => 'Compulsory Excess (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],
        ];

        // return formatted?
        $fromatted_v_rules = [];
        if($formatted === TRUE)
        {
            foreach ($v_rules as $section => $section_rules)
            {
                $fromatted_v_rules = array_merge($fromatted_v_rules, $section_rules);
            }
            return $fromatted_v_rules;
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Validation Rules - Commercial Vehicle
     *
     * @param bool $formatted
     * @return type
     */
    public function commercial_vehicle_validation_rules($formatted = FALSE)
    {
        $v_rules = $this->_common_validation_rules();
        $v_rules += [
            /**
             * JSON : Tarrif
             * --------------
             */
            'tariff' => [
                [
                    'field' => 'tariff[ec_type][]',
                    'label' => 'Engine Capacity Type',
                    'rules' => 'trim|required|alpha|max_length[3]',
                    '_type'     => 'dropdown',
                    '_show_label' => false,
                    '_key'      => 'ec_type',
                    '_data'     => _OBJ_MOTOR_ec_unit_tariff_dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_min][]',
                    'label' => 'Capacity Min (CC|KW|TON|HP|Seat)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_max][]',
                    'label' => 'Capacity Max (CC|KW|TON|HP)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_max',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[default_age_max][]',
                    'label' => 'Default Age Max (yrs)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_age_max',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[default_rate][]',
                    'label' => 'Default Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'default_rate',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[minus_amount][]',
                    'label' => 'Default To-Minus-Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'minus_amount',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[plus_amount][]',
                    'label' => 'Default To-Plus-Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'plus_amount',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_threshold][]',
                    'label' => 'Default Threshold (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'ec_threshold',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[cost_per_ec_above][]',
                    'label' => 'Premium per Above Threshold (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'cost_per_ec_above',
                    '_required' => true
                ],

                // Other Age Range Rate
                [
                    'field' => 'tariff[age1_min][]',
                    'label' => 'Second Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age1_max][]',
                    'label' => 'Second Age Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age1_max',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate1][]',
                    'label' => 'Rate on Second Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate1',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age2_min][]',
                    'label' => 'Third Age Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'age2_min',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate2][]',
                    'label' => 'Rate on Third Age(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'rate2',
                    '_required' => true
                ],

                // Third Party Amount
                [
                    'field' => 'tariff[third_party][]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_key'      => 'third_party',
                    '_required' => true
                ],
            ],

            // Commercial Vehicle - Discount on Personal Use
            'dr_cvc_on_personal_use' => [
                [
                    'field' => 'dr_cvc_on_personal_use',
                    'label' => 'Discount on Personal Use (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Private/Commercial Vehicle - Towing Premium Amount
            'pramt_towing' => [
                [
                    'field' => 'pramt_towing',
                    'label' => 'Towing Premium Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * Private/Commercial Vehicle - JSON : Trolly/Trailer Tarrif
             * ---------------------------------------------------------
             *
             * Structure:
             *  {
             *      rate: 0.123,
             *      minus_amount: 0.12,
             *      third_party: 500,
             *      compulsory_excess: 400
             *  }
             */
            'trolly_tariff' => [
                [
                    'field' => 'trolly_tariff[rate]',
                    'label' => 'Premium Rate (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[minus_amount]',
                    'label' => 'To Minus Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[third_party]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[compulsory_excess]',
                    'label' => 'Compulsory Excess (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],
        ];

        // return formatted?
        $fromatted_v_rules = [];
        if($formatted === TRUE)
        {
            foreach ($v_rules as $section => $section_rules)
            {
                $fromatted_v_rules = array_merge($fromatted_v_rules, $section_rules);
            }
            return $fromatted_v_rules;
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

        private function _common_validation_rules()
        {
            return [

                /**
                 * Default Configurations
                 */
                'defaults' => [
                    [
                        'field' => 'active',
                        'label' => 'Activate Tariff',
                        'rules' => 'trim|integer|in_list[1]',
                        '_type' => 'switch',
                        '_checkbox_value' => '1'
                    ],
                    [
                        'field' => 'default_premium',
                        'label' => 'Default Premium Amount (Rs.)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_type'     => 'text',
                        '_required' => true,
                        '_help_text' => 'This is the default premium amount for "अ. सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत". When the tariff calculation falls below this amount, we use this amount as the premium amount.'
                    ]
                ],

                /**
                 * JSON : No Claim Discount
                 * -------------------------
                 *
                 * Structure:
                 *  [{
                 *      years: 1,
                 *      rate: 20
                 *  },{
                 *      ...
                 *  }]
                 */
                'no_claim_discount' => [
                    [
                        'field' => 'no_claim_discount[years][]',
                        'label' => 'Consecutive Years',
                        'rules' => 'trim|required|alpha_numeric|max_length[20]',
                        '_type'     => 'text',
                        '_key'      => 'years',
                        '_show_label' => false,
                        '_required' => true
                    ],
                    [
                        'field' => 'no_claim_discount[rate][]',
                        'label' => 'Premium Rate(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                        '_type'     => 'text',
                        '_key'      => 'rate',
                        '_show_label' => false,
                        '_required' => true
                    ],
                    [
                        'field' => 'no_claim_discount[label_en][]',
                        'label' => 'Label(EN)',
                        'rules' => 'trim|required|max_length[50]',
                        '_type'     => 'text',
                        '_key'      => 'label_en',
                        '_show_label' => false,
                        '_required' => true
                    ],
                    [
                        'field' => 'no_claim_discount[label_np][]',
                        'label' => 'Label(NP)',
                        'rules' => 'trim|required|max_length[50]',
                        '_type'     => 'text',
                        '_key'      => 'label_np',
                        '_show_label' => false,
                        '_required' => true
                    ],

                ],


                /**
                 * JSON : Voluntary Excess
                 * -------------------------
                 *
                 * Structure:
                 *  [{
                 *      amount: 500,
                 *      rate: 20
                 *  },{
                 *      ...
                 *  }]
                 */
                'dr_voluntary_excess' => [
                    [
                        'field' => 'dr_voluntary_excess[amount][]',
                        'label' => 'Voluntary Excess Amount(Rs)',
                        'rules' => 'trim|required|integer|max_length[10]',
                        '_type'     => 'text',
                        '_key'      => 'amount',
                        '_show_label' => false,
                        '_required' => true
                    ],
                    [
                        'field' => 'dr_voluntary_excess[rate][]',
                        'label' => 'Voluntary Excess Discount(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                        '_type'     => 'text',
                        '_key'      => 'rate',
                        '_show_label' => false,
                        '_required' => true
                    ]
                ],


                /**
                 * JSON : Compulsory Excess
                 * -------------------------
                 *
                 * Structure:
                 *  [{
                 *      min_age: 5,
                 *      max_age: 10,
                 *      amount: 5000
                 *  },{
                 *      ...
                 *  }]
                 */
                'pramt_compulsory_excess' => [
                    [
                        'field' => 'pramt_compulsory_excess[min_age][]',
                        'label' => 'Min Age(years)',
                        'rules' => 'trim|required|integer|max_length[2]',
                        '_type'     => 'text',
                        '_show_label' => false,
                        '_key'      => 'min_age',
                        '_required' => true
                    ],
                    [
                        'field' => 'pramt_compulsory_excess[max_age][]',
                        'label' => 'Max Age(years)',
                        'rules' => 'trim|required|integer|max_length[2]',
                        '_type'     => 'text',
                        '_show_label' => false,
                        '_key'      => 'max_age',
                        '_required' => true
                    ],
                    [
                        'field' => 'pramt_compulsory_excess[amount][]',
                        'label' => 'Amount(Rs.)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_show_label' => false,
                        '_key'      => 'amount',
                        '_required' => true
                    ]
                ],

                /**
                 * JSON : Motor Accident Premium
                 * -------------------------
                 *
                 * Structure:
                 *  {
                 *      pramt_driver_accident: 500,
                 *      pramt_accident_per_staff: 700,
                 *      pramt_accident_per_passenger: 500
                 *  }
                 */
                'accident_premium' => [
                    [
                        'field' => 'accident_premium[pramt_driver_accident]',
                        'label' => 'Motor Accident Premium: Driver Accident (Rs.)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'accident_premium[pramt_accident_per_staff]',
                        'label' => 'Motor Accident Premium: Per Staff Accident (Rs.)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'accident_premium[pramt_accident_per_passenger]',
                        'label' => 'Motor Accident Premium: Per Passenger Accident (Rs.)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ]
                ],

                /**
                 * JSON : Risk Group Rates
                 * -------------------------
                 *
                 * Structure:
                 *  {
                 *      rate_mob: 0.123,
                 *      rate_terrorism: 0.12,
                 *      rate_additionl_per_thousand_on_extra_rate: 0.25
                 *  }
                 */
                'riks_group' => [
                    [
                        'field' => 'riks_group[rate_pool_risk_mob]',
                        'label' => 'Risk: Pool (%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'riks_group[rate_pool_risk_terorrism]',
                        'label' => 'Risk: Terrorism (%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'riks_group[rate_additionl_per_thousand_on_extra_rate]',
                        'label' => 'Risk: Rate Per Thousand on Additional Premium',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ]
                ],

                /**
                 * JSON : Accident Covered Tariff Amounts
                 * --------------------------------------
                 *
                 * Structure:
                 *  {
                 *      rate_mob: 0.123,
                 *      rate_terrorism: 0.12,
                 *      rate_additionl_per_thousand_on_extra_rate: 0.25
                 *  }
                 */
                'insured_value_tariff' => [
                    [
                        'field' => 'insured_value_tariff[driver]',
                        'label' => 'Driver Covered Amount (Rs)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'insured_value_tariff[staff]',
                        'label' => 'Staff Covered Amount (Rs)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                    [
                        'field' => 'insured_value_tariff[passenger]',
                        'label' => 'Passenger Covered Amount (Rs)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                        '_type'     => 'text',
                        '_required' => true
                    ],
                ],
            ];
        }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $this->validation_rules = [

            /**
             * Default Configurations
             */
            'defaults' => [
                [
                    'field' => 'active',
                    'label' => 'Activate Tariff',
                    'rules' => 'trim|integer|in_list[1]',
                    '_type' => 'switch',
                    '_checkbox_value' => '1'
                ],
                [
                    'field' => 'default_premium',
                    'label' => 'Default Premium Amount (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_required' => true,
                    '_help_text' => 'This is the default premium amount for "अ. सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत". When the tariff calculation falls below this amount, we use this amount as the premium amount.'
                ]
            ],

            /**
             * JSON : Tarrif
             * --------------
             *
             * Structure:
             *  [{
             *      ec_min: xxx,
             *      ec_max: yyy,
             *      rate: {
             *          ec_type: [CC  | KW | HP | TON] // Engine Capacity Type
             *          age: nnn,           // Default Max Age
             *          rate: aaa,                // Ghoshit mulya ko x%
             *          minus_amount: bbb,          // Amount to Minus
             *
             *          // For Tanker
             *          ec_threshold: ccc,          // Minimum Threshold capacity eg. 3 TON
             *          cost_per_ec_above: ddd,     // Cost per engine capacity e.g. Rs. 500 Per Ton above 3 Ton
             *
             *          // For: Private Vehicle
             *          //  First 20 lakhs' 1.25 % + Rest's 1.75 - Rx. 5000
             *          fragmented: true | false,
             *          base_fragment: xxx,
             *          base_fragment_rate: yyy,
             *          rest_fragment_rate: zzz
             *      },
             *      age: {
             *          age1_min: xxx,
             *          age1_max: yyy,
             *          rate1: zzz,
             *          age2_min: aa,
             *          age2_max: bb,
             *          rate2:ccc
             *      },
             *      third_party: 4000               // Third party Premium
             *  },{
             *      ...
             *  }]
             */
            'tariff' => [
                [
                    'field' => 'tariff[ec_type][]',
                    'label' => 'Engine Capacity Type',
                    'rules' => 'trim|required|alpha|max_length[2]',
                    '_type'     => 'dropdown',
                    '_data'     => _OBJ_MOTOR_ec_unit_tariff_dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_min][]',
                    'label' => 'Default Min (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_max][]',
                    'label' => 'Default Max (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[rate][age][]',
                    'label' => 'Default Age Max (yrs)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][rate][]',
                    'label' => 'Default Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][minus_amount][]',
                    'label' => 'Default To-Minus-Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][plus_amount][]',
                    'label' => 'Default To-Plus-Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][ec_threshold][]',
                    'label' => 'Default Threshold (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][cost_per_ec_above][]',
                    'label' => 'Premium per Above Threshold (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],

                // Fragmented Cost
                [
                    'field' => 'tariff[rate][fragmented][]',
                    'label' => 'Fragmented Cost',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[Y,N]',
                    '_type'     => 'dropdown',
                    '_default'  => 'N',
                    '_data'     => IQB_BLANK_SELECT + ['Y' => 'Yes', 'N' => 'No' ],
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][base_fragment][]',
                    'label' => 'Fragment Base Amount (Rs)',
                    'rules' => 'trim|required|integer|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][base_fragment_rate][]',
                    'label' => 'Base Fragment Rate (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][rest_fragment_rate][]',
                    'label' => 'Rest Fragment Rate (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],


                // Other Age Range Rate
                [
                    'field' => 'tariff[age][age1_min][]',
                    'label' => 'Ohter Age1 Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age][age1_max][]',
                    'label' => 'Ohter Age1 Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age][rate1][]',
                    'label' => 'Rate on Age1(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age][age2_min][]',
                    'label' => 'Ohter Age2 Min(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age][age2_max][]',
                    'label' => 'Ohter Age2 Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age][rate2][]',
                    'label' => 'Rate on Age2(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],

                // Third Party Amount
                [
                    'field' => 'tariff[third_party][]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
            ],

            /**
             * JSON : No Claim Discount
             * -------------------------
             *
             * Structure:
             *  [{
             *      years: 1,
             *      rate: 20
             *  },{
             *      ...
             *  }]
             */
            'no_claim_discount' => [
                [
                    'field' => 'no_claim_discount[years][]',
                    'label' => 'Consecutive Years',
                    'rules' => 'trim|required|integer|max_length[2]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'no_claim_discount[rate][]',
                    'label' => 'Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Disable Friendly Discount
            'dr_mcy_disabled_friendly' => [
                [
                    'field' => 'dr_mcy_disabled_friendly',
                    'label' => 'Disable Friendly Discount (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Private Vehicle - Private Hire Rate
            'rate_pvc_on_hire' => [
                [
                    'field' => 'rate_pvc_on_hire',
                    'label' => 'Private Hire (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],


            // Commercial Vehicle - Discount on Personal Use
            'dr_cvc_on_personal_use' => [
                [
                    'field' => 'dr_cvc_on_personal_use',
                    'label' => 'Discount on Personal Use (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Private/Commercial Vehicle - Towing Premium Amount
            'pramt_towing' => [
                [
                    'field' => 'pramt_towing',
                    'label' => 'Towing Premium Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],


            /**
             * JSON : Voluntary Excess
             * -------------------------
             *
             * Structure:
             *  [{
             *      amount: 500,
             *      rate: 20
             *  },{
             *      ...
             *  }]
             */
            'dr_voluntary_excess' => [
                [
                    'field' => 'dr_voluntary_excess[amount][]',
                    'label' => 'Voluntary Excess Amount(Rs)',
                    'rules' => 'trim|required|integer|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'dr_voluntary_excess[rate][]',
                    'label' => 'Voluntary Excess Discount(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * JSON : Compulsory Excess
             * -------------------------
             *
             * Structure:
             *  [{
             *      min_age: 5,
             *      max_age: 10,
             *      amount: 5000
             *  },{
             *      ...
             *  }]
             */
            'pramt_compulsory_excess' => [
                [
                    'field' => 'pramt_compulsory_excess[min_age][]',
                    'label' => 'Compulsory Excess Min Age(years)',
                    'rules' => 'trim|required|integer|max_length[2]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'pramt_compulsory_excess[max_age][]',
                    'label' => 'Compulsory Excess Max Age(years)',
                    'rules' => 'trim|required|integer|max_length[2]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'pramt_compulsory_excess[amount][]',
                    'label' => 'Compulsory Excess Amount(Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * JSON : Motor Accident Premium
             * -------------------------
             *
             * Structure:
             *  {
             *      pramt_driver_accident: 500,
             *      pramt_accident_per_staff: 700,
             *      pramt_accident_per_passenger: 500
             *  }
             */
            'accident_premium' => [
                [
                    'field' => 'accident_premium[pramt_driver_accident]',
                    'label' => 'Motor Accident Premium: Driver Accident (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'accident_premium[pramt_accident_per_staff]',
                    'label' => 'Motor Accident Premium: Per Staff Accident (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'accident_premium[pramt_accident_per_passenger]',
                    'label' => 'Motor Accident Premium: Per Passenger Accident (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * JSON : Risk Group Rates
             * -------------------------
             *
             * Structure:
             *  {
             *      rate_mob: 0.123,
             *      rate_terrorism: 0.12,
             *      rate_additionl_per_thousand_on_extra_rate: 0.25
             *  }
             */
            'riks_group' => [
                [
                    'field' => 'riks_group[rate_pool_risk_mob]',
                    'label' => 'Risk: Pool (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'riks_group[rate_pool_risk_terorrism]',
                    'label' => 'Risk: Terrorism (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'riks_group[rate_additionl_per_thousand_on_extra_rate]',
                    'label' => 'Risk: Rate Per Thousand on Additional Premium',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],


            /**
             * Private/Commercial Vehicle - JSON : Trolly/Trailer Tarrif
             * ---------------------------------------------------------
             *
             * Structure:
             *  {
             *      rate: 0.123,
             *      minus_amount: 0.12,
             *      third_party: 500,
             *      compulsory_excess: 400
             *  }
             */
            'trolly_tariff' => [
                [
                    'field' => 'trolly_tariff[rate]',
                    'label' => 'Premium Rate (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[minus_amount]',
                    'label' => 'To Minus Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[third_party]',
                    'label' => 'Third Party Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'trolly_tariff[compulsory_excess]',
                    'label' => 'Compulsory Excess (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * JSON : Accident Covered Tariff Amounts
             * --------------------------------------
             *
             * Structure:
             *  {
             *      rate_mob: 0.123,
             *      rate_terrorism: 0.12,
             *      rate_additionl_per_thousand_on_extra_rate: 0.25
             *  }
             */
            'insured_value_tariff' => [
                [
                    'field' => 'insured_value_tariff[driver]',
                    'label' => 'Driver Covered Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'insured_value_tariff[staff]',
                    'label' => 'Staff Covered Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'insured_value_tariff[passenger]',
                    'label' => 'Passenger Covered Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
            ],
        ];
    }

    // ----------------------------------------------------------------

    public function insert_validate_rules()
    {
        $this->insert_validate_rules = [
            [
                'field' => 'fiscal_yr_id',
                'label' => 'Fiscal Year',
                'rules' => 'trim|required|integer|max_length[3]|callback__cb_tariff_motor_check_duplicate',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                '_default'  => '',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------


    /**
     * Add Blank Tariff Records for given fiscal year
     *
     * @param int $fiscal_yr_id
     * @return bool
     */
    public function add($fiscal_yr_id)
    {
        /**
         * Prepare Batch Data
         */
        $ownership_list     = _OBJ_MOTOR_ownership_dropdown(FALSE);
        $sub_portfolio_list = _OBJ_MOTOR_sub_portfolio_dropdown(FALSE);
        $cvc_type_list      = _OBJ_MOTOR_CVC_type_dropdown(FALSE);

        $batch_data = [];

        // For all Motor Portfolios
        foreach ($sub_portfolio_list as $portfolio_id=>$ptext)
        {
            // CVC Types on Commercial Vehicle
            if( (int)$portfolio_id === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID )
            {
                foreach($cvc_type_list as $cvc_type=>$ctext)
                {
                    foreach($ownership_list as $ownership=>$otext)
                    {
                        $batch_data[] = [
                            'fiscal_yr_id'      => $fiscal_yr_id,
                            'portfolio_id'      => $portfolio_id,
                            'ownership'         => $ownership,
                            'cvc_type'          => $cvc_type
                        ];
                    }
                }
            }
            else
            {
                foreach($ownership_list as $ownership=>$otext)
                {
                    $batch_data[] = [
                        'fiscal_yr_id'      => $fiscal_yr_id,
                        'portfolio_id'      => $portfolio_id,
                        'ownership'         => $ownership,
                        'cvc_type'          => NULL
                    ];
                }
            }
        }



        $done  = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            // Insert Individual - No batch-insert - because of Audit Log Requirement
            foreach($batch_data as $single_data)
            {
                parent::insert($single_data, TRUE);
            }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }
        else
        {
            $this->clear_cache();
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Duplicate all Records of Given Fiscal Year to New Fiscal Year
     *
     * @param int $source_fiscal_year_id
     * @param int $destination_fiscal_year_id
     * @return bool
     */
    public function duplicate($source_fiscal_year_id, $destination_fiscal_year_id)
    {
        $source_tariffs = $this->get_list_by_fiscal_year($source_fiscal_year_id);

        $done  = TRUE;
        if($source_tariffs)
        {
            // Use automatic transaction
            $this->db->trans_start();

                foreach($source_tariffs as $src)
                {
                    $single_data =(array)$src;

                    // Set Fiscal Year
                    $single_data['fiscal_yr_id'] = $destination_fiscal_year_id;

                    // Remoe Unnecessary Fields
                    unset($single_data['id']);
                    unset($single_data['created_at']);
                    unset($single_data['created_by']);
                    unset($single_data['updated_at']);
                    unset($single_data['updated_by']);

                    parent::insert($single_data, TRUE);
                }

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $done = FALSE;
            }
            else
            {
                $this->clear_cache();
            }
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Get Index Rows
     *
     * List of Fiscal Years for which data have been created
     *
     * @return array
     */
    public function get_index_rows()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('tm_index_list');
        if(!$list)
        {
            $list = $this->db->select('PTM.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTM')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                                ->group_by('PTM.fiscal_yr_id')
                                ->order_by('PTM.fiscal_yr_id', 'DESC')
                                ->get()->result();
            $this->write_cache($list, 'tm_index_list', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_fiscal_year_row($fiscal_yr_id)
    {
        return $this->db->select('PTM.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTM')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                                ->where('PTM.fiscal_yr_id', $fiscal_yr_id)
                                ->get()->row();
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('PTM.*')
                        ->from($this->table_name . ' PTM')
                        ->where('PTM.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year_rows($fiscal_yr_id)
    {
        return $this->db->select('PTM.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np, PRT.name_en as portfolio_name_en')
                        ->from($this->table_name . ' PTM')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                        ->join('master_portfolio PRT', 'PRT.id = PTM.portfolio_id')
                        ->where('PTM.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get($id)
    {
        return $this->db->select('PTM.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np, PRT.name_en as portfolio_name_en')
                        ->from($this->table_name . ' PTM')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                        ->join('master_portfolio PRT', 'PRT.id = PTM.portfolio_id')
                        ->where('PTM.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    public function get_single($fiscal_yr_id, $ownership, $portfolio_id, $cvc_type = NULL)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'tms_' . $fiscal_yr_id . '_' . $ownership . '_' . $portfolio_id;
        if($cvc_type)
        {
            $cache_name .= '_' . $cvc_type;
        }

        $row = $this->get_cache($cache_name);
        if(!$row)
        {
            $where = [
                'portfolio_id'  => $portfolio_id,
                'cvc_type'      => $cvc_type ? $cvc_type : NULL,
                'ownership'     => $ownership,
                'fiscal_yr_id'  => $fiscal_yr_id,
            ];
            $row = $this->db->select('PTM.*')
                        ->from($this->table_name . ' PTM')
                        ->where($where)
                        ->get()->row();
            $this->write_cache($row, $cache_name, CACHE_DURATION_DAY);
        }
        return $row;
    }
    // ----------------------------------------------------------------


    public function check_duplicate($where, $tariff_ids=NULL)
    {
        if( $tariff_ids )
        {
            $this->db->where_not_in('id', $tariff_ids);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'tm_index_list',
            'tms_*'
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
        // We do not delete any tariff
        return FALSE;

        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);

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
}