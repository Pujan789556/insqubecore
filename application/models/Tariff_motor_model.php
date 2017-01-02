<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tariff_motor_model extends MY_Model
{
    protected $table_name = 'master_tariff_motor';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ["id", "sub_portfolio", "cvc_type", "fiscal_yr_id", "ownership", "tariff", "no_claim_discount", "dr_disabled_friendly", "dr_voluntary_excess", "pramt_compulsory_excess", "additional_premium", "riks_group", "active", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Valication Rule
        $this->validation_rules();
        $this->insert_validate_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $this->validation_rules = [

            /**
             * JSON : Tarrif
             * --------------
             *
             * Structure:
             *  [{
             *      ec_min: xxx,
             *      ec_max: yyy,
             *      rate: {
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
             *
             *      },
             *      third_party: 4000               // Third party Premium
             *  },{
             *      ...
             *  }]
             */
            'tariff' => [

                [
                    'field' => 'tariff[ec_min][]',
                    'label' => 'Min Engine Capacity (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[ec_max][]',
                    'label' => 'Max Engine Capacity (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],

                // Default Rate
                [
                    'field' => 'tariff[rate][age][]',
                    'label' => 'Default Age Max(years)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][rate][]',
                    'label' => 'Rate on Total Price(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][minus_amount][]',
                    'label' => 'To Minus Amount (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][ec_threshold][]',
                    'label' => 'Engine Capacity Threshold (CC|KW|TON)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][cost_per_ec_above][]',
                    'label' => 'Cost per Engine Capacity Above Threshold (Rs)',
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
            'dr_disabled_friendly' => [
                [
                    'field' => 'dr_disabled_friendly',
                    'label' => 'Disable Friendly Discount (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
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
             * JSON : Additional Premium
             * -------------------------
             *
             * Structure:
             *  {
             *      pramt_driver_accident: 500,
             *      pramt_accident_per_staff: 700,
             *      pramt_accident_per_passenger: 500
             *  }
             */
            'additional_premium' => [
                [
                    'field' => 'additional_premium[pramt_driver_accident]',
                    'label' => 'Additional Premium: Driver Accident (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'additional_premium[pramt_accident_per_staff]',
                    'label' => 'Additional Premium: Per Staff Accident (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'additional_premium[pramt_accident_per_passenger]',
                    'label' => 'Additional Premium: Per Passenger Accident (Rs.)',
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
                    'field' => 'riks_group[rate_mob]',
                    'label' => 'Risk: Pool (%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'riks_group[rate_terrorism]',
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
            ]
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
        $list = $this->get_cache('pf_tarrif_motor_list');
        if(!$list)
        {
            $list = $this->db->select('PTM.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTM')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                                ->group_by('PTM.fiscal_yr_id')
                                ->get()->result();
            $this->write_cache($list, 'pf_tarrif_motor_list', CACHE_DURATION_DAY);
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
        return $this->db->select('PTM.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np')
                        ->from($this->table_name . ' PTM')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTM.fiscal_yr_id')
                        ->where('PTM.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
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
            'pf_tarrif_motor_list'
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
        else
        {
            $this->log_activity($id, 'D');
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
            'module' => 'portfolio_tarrif_motor',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}