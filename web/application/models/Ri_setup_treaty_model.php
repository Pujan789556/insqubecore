<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaties';

    /**
     * Other dependent tables
     */
    protected static $table_treaty_types                    = 'ri_setup_treaty_types';
    protected static $table_treaty_brokers                  = 'ri_setup_treaty_brokers';
    protected static $table_treaty_distribution             = 'ri_setup_treaty_distribution';
    protected static $table_treaty_portfolios               = 'ri_setup_treaty_portfolios';
    protected static $table_treaty_tax_and_commission       = 'ri_setup_treaty_tax_and_commission';
    protected static $table_treaty_commission_scale         = 'ri_setup_commission_scale';

    /**
     * Other table Fillables
     */
    // Tax and Commission Tables
    protected static $tnc_fillables = ['qs_comm_ri_quota', 'qs_comm_ri_surplus_1', 'qs_comm_ri_surplus_2', 'qs_comm_ri_surplus_3', 'qs_tax_ri_quota', 'qs_tax_ri_surplus_1', 'qs_tax_ri_surplus_2', 'qs_tax_ri_surplus_3', 'qs_comm_ib_quota', 'qs_comm_ib_surplus_1', 'qs_comm_ib_surplus_2', 'qs_comm_ib_surplus_3', 'qs_piop_quota', 'qs_piop_surplus_1', 'qs_piop_surplus_2', 'qs_piop_surplus_3', 'qs_piol_quota', 'qs_piol_surplus_1', 'qs_piol_surplus_2', 'qs_piol_surplus_3', 'qs_pio_ib_cp_quota', 'qs_pio_ib_cp_surplus_1', 'qs_pio_ib_cp_surplus_2', 'qs_pio_ib_cp_surplus_3', 'qs_profit_comm_quota', 'qs_profit_comm_surplus_1', 'qs_profit_comm_surplus_2', 'qs_profit_comm_surplus_3', 'flag_qs_comm_scale_quota', 'flag_qs_comm_scale_surplus_1', 'flag_qs_comm_scale_surplus_2', 'flag_qs_comm_scale_surplus_3', 'eol_min_n_deposit_amt_l1', 'eol_min_n_deposit_amt_l2', 'eol_min_n_deposit_amt_l3', 'eol_min_n_deposit_amt_l4', 'eol_premium_mode_l1', 'eol_premium_mode_l2', 'eol_premium_mode_l3', 'eol_premium_mode_l4', 'eol_min_rate_l1', 'eol_min_rate_l2', 'eol_min_rate_l3', 'eol_min_rate_l4', 'eol_max_rate_l1', 'eol_max_rate_l2', 'eol_max_rate_l3', 'eol_max_rate_l4', 'eol_fixed_rate_l1', 'eol_fixed_rate_l2', 'eol_fixed_rate_l3', 'eol_fixed_rate_l4', 'eol_loading_factor_l1', 'eol_loading_factor_l2', 'eol_loading_factor_l3', 'eol_loading_factor_l4', 'eol_tax_ri_l1', 'eol_tax_ri_l2', 'eol_tax_ri_l3', 'eol_tax_ri_l4', 'eol_comm_ib_l1', 'eol_comm_ib_l2', 'eol_comm_ib_l3', 'eol_comm_ib_l4', 'flag_eol_rr_l1', 'flag_eol_rr_l2', 'flag_eol_rr_l3', 'flag_eol_rr_l4'];

    protected $set_created = true;
    protected $set_modified = true;
    protected $log_user = true;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields = ['id', 'name', 'fiscal_yr_id', 'treaty_type_id', 'estimated_premium_income', 'treaty_effective_date', 'file', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

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

        $this->load->model('ri_setup_treaty_type_model');
        $this->load->model('company_model');
        $this->load->model('portfolio_model');

        // Set validation rule
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules (Sectioned)
     *
     * @return void
     */
    public function validation_rules()
    {
        $broker_dropdown        = $this->company_model->dropdown_brokers();
        $reinsurer_dropdown     = $this->company_model->dropdown_reinsurers();
        $portfolio_dropdown     = $this->portfolio_model->dropdown_children();

        $this->validation_rules = [

            // Master Table (Treaty Setup)
            'basic' => [
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'treaty_type_id',
                    'label' => 'Treaty Type',
                    'rules' => 'trim|required|integer|exact_length[1]|callback__cb_treaty_type__check_duplicate',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->ri_setup_treaty_type_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'name',
                    'label' => 'Treaty Title',
                    'rules' => 'trim|required|max_length[100]|callback__cb_name__check_duplicate',
                    '_type'     => 'text',
                    '_required' => true
                ],

                [
                    'field' => 'estimated_premium_income',
                    'label' => 'Estimated Premium Income',
                    'rules' => 'trim|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'treaty_effective_date',
                    'label' => 'Treaty Effective Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'     => 'date',
                    '_required' => true
                ],
            ],

            // Broker List
            'brokers' => [
                [
                    'field' => 'broker_ids[]',
                    'label' => 'Re-insurance Broker',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($broker_dropdown) ) . ']',
                    '_type'     => 'checkbox',
                    '_data'     => $broker_dropdown,
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],

            // Portfolio List
            'portfolios' => [
                [
                    'field' => 'portfolio_ids[]',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($portfolio_dropdown) ) . ']|callback__cb_portfolio__check_duplicate',
                    '_type'     => 'checkbox',
                    '_data'     => $portfolio_dropdown,
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],

            // RI Distribution
            'reinsurers' => [
                [
                    'field' => 'reinsurer_ids[]',
                    'label' => 'Reinsurer',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($reinsurer_dropdown) ) . ']',
                    '_field'        => 'company_id',
                    '_type'         => 'dropdown',
                    '_show_label'   => false,
                    '_data'         => IQB_BLANK_SELECT + $reinsurer_dropdown,
                    '_required'     => true
                ],
                [
                    'field' => 'distribution_percent[]',
                    'label' => 'Distribution %',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]|callback__cb_distribution__complete',
                    '_field'        => 'distribution_percent',
                    '_type'         => 'text',
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],

            // Commission Scale
            'commission_scale' => [
                [
                    'field' => 'name[]',
                    'label' => 'Title',
                    'rules' => 'trim|required|max_length[100]',
                    '_field'        => 'name',
                    '_type'         => 'text',
                    '_show_label'   => false,
                    '_required'     => true
                ],
                [
                    'field' => 'scale_min[]',
                    'label' => 'Minimum Scale(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'scale_min',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'scale_max[]',
                    'label' => 'Maximum Scale(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'scale_max',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'rate[]',
                    'label' => 'Commission Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'rate',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ]
            ],

            // Treaty Portfolios: Common Fields
            'portfolios_common' => [
                [
                    'field' => 'portfolio_ids[]',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[8]',
                    '_field'        => 'portfolio_id',
                    '_type'         => 'hidden',
                    '_show_label'   => false,
                    '_required'     => true
                ],
                [
                    'field' => 'ac_basic[]',
                    'label' => 'Account Basic',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(IQB_RI_SETUP_AC_BASIC_TYPES) ) . ']',
                    '_field'        => 'ac_basic',
                    '_type'         => 'dropdown',
                    '_show_label'   => false,
                    '_data'         => IQB_BLANK_SELECT + IQB_RI_SETUP_AC_BASIC_TYPES,
                    '_required'     => true
                ],
                [
                    'field' => 'flag_claim_recover_from_ri[]',
                    'label' => 'Claim Recover From RI',
                    'rules' => 'trim|required|integer|in_list[0,1]',
                    '_field'            => 'flag_claim_recover_from_ri',
                    '_type'             => 'dropdown',
                    '_show_label'       => false,
                    '_data'             => IQB_BLANK_SELECT + _FLAG_on_off_dropdwon(),
                    '_required'         => true
                ],
                [
                    'field' => 'flag_comp_cession_apply[]',
                    'label' => 'Apply Compulsory Cession',
                    'rules' => 'trim|required|integer|in_list[0,1]',
                    '_field'            => 'flag_comp_cession_apply',
                    '_type'             => 'dropdown',
                    '_show_label'       => false,
                    '_data'             => IQB_BLANK_SELECT + _FLAG_on_off_dropdwon(),
                    '_required'         => true
                ],
                [
                    'field' => 'comp_cession_percent[]',
                    'label' => 'Compulsory Cession(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_field'            => 'comp_cession_percent',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'comp_cession_max_amount[]',
                    'label' => 'Compulsory Max Amount',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'comp_cession_max_amount',
                    '_type'             => 'text',
                    '_show_label'       => false,
                    '_required'         => true
                ],
            ],

            // Treaty Portfolios: "Quota" Only Fields
            'portfolios_qt' => [
                [
                    'field' => 'qs_max_ret_amt[]',
                    'label' => 'Maximum Retention Amount',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'qs_max_ret_amt',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_def_ret_amt[]',
                    'label' => 'Defined Retention Amount',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|less_than[qs_max_ret_amt[]]',
                    '_field'            => 'qs_def_ret_amt',
                    '_type'             => 'text',
                    '_show_label'       => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_retention_percent[]',
                    'label' => 'Quota Retention(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_field'            => 'qs_retention_percent',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_quota_percent[]',
                    'label' => 'Quota Distribution(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_field'            => 'qs_quota_percent',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
            ],

            // Treaty Portfolios: "Surplus" Only Fields
            'portfolios_sp' => [
                [
                    'field' => 'qs_max_ret_amt[]',
                    'label' => 'Maximum Retention Amount',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'qs_max_ret_amt',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_def_ret_amt[]',
                    'label' => 'Defined Retention Amount',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|less_than[qs_max_ret_amt[]]',
                    '_field'            => 'qs_def_ret_amt',
                    '_type'             => 'text',
                    '_show_label'       => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_lines_1[]',
                    'label' => '1st Surplus Lines',
                    'rules' => 'trim|required|integer|max_length[4]',
                    '_field'            => 'qs_lines_1',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_lines_2[]',
                    'label' => '2nd Surplus Lines',
                    'rules' => 'trim|required|integer|max_length[4]',
                    '_field'            => 'qs_lines_2',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'qs_lines_3[]',
                    'label' => '3rd Surplus Lines',
                    'rules' => 'trim|required|integer|max_length[4]',
                    '_field'            => 'qs_lines_3',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
            ],

            // Treaty Portfolios: "Quota & Surplus" Only Fields
            // @NOTE: You have to merge [common, qt, qs, sp] together to get full validation list
            'portfolios_qs' => [
                [
                    'field' => 'flag_qs_line[]',
                    'label' => 'Surplus Line Reference',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(ri_qs_surplus_line_reference_dropdown(false)) ) . ']',
                    '_field'        => 'flag_qs_line',
                    '_type'         => 'dropdown',
                    '_show_label'   => false,
                    '_data'         => IQB_BLANK_SELECT + ri_qs_surplus_line_reference_dropdown(),
                    '_required'     => true
                ],
            ],

            // Treaty Portfolios: "Excell of Loss" only Fields
            'portfolios_eol' => [
                [
                    'field' => 'eol_layer_amount_1[]',
                    'label' => 'EOL Amount Layer 1',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'eol_layer_amount_1',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'eol_layer_amount_2[]',
                    'label' => 'EOL Amount Layer 2',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'eol_layer_amount_2',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'eol_layer_amount_3[]',
                    'label' => 'EOL Amount Layer 3',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'eol_layer_amount_3',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'eol_layer_amount_4[]',
                    'label' => 'EOL Amount Layer 4',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_field'            => 'eol_layer_amount_4',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ]
            ],
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules (Sectioned)
     *
     * @param array|string $sections
     * @return array
     */
    public function get_validation_rules($sections)
    {
        // If a single section is supplied, convert it into array
        $sections = is_array($sections) ? $sections : array($sections);
        $v_rules = [];
        foreach( $sections as $section)
        {
            $v_rules[$section] = $this->validation_rules[$section];
        }
        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules Formatted from Supplied sections to run Form Validation
     *
     * @param array  $sectioned_rules
     * @return void
     */
    public function get_validation_rules_formatted($sections)
    {
        $sectioned_rules = $this->get_validation_rules($sections);
        $v_rules_formatted = [];
        foreach($sectioned_rules as $section=>$rules)
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $rules);
        }
        return $v_rules_formatted;
    }

    // ----------------------------------------------------------------

    public function get_tnc_validation_rules($treaty_type_id, $formatted = false)
    {
        if( $treaty_type_id == IQB_RI_TREATY_TYPE_EOL )
        {
            $col_headings = ['Title', 'Layer 1', 'Layer 2', 'Layer 3', 'Layer 4'];
            $tnc_col_postfix = ['l1','l2', 'l3', 'l4'];
            $tnc_val_prefix = [
                'eol_min_n_deposit_amt'    => [
                    'label' => 'Minimum & Deposit Premium',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_premium_mode'    => [
                    'label' => 'Premium Mode',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list[0,1]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + [0 => 'Fixed', 1 => 'Range'],
                    '_required' => true
                ],
                'eol_min_rate'    => [
                    'label' => 'Minimum Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_max_rate'    => [
                    'label' => 'Maximum Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_fixed_rate'    => [
                    'label' => 'Fixed Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_loading_factor'    => [
                    'label' => 'Loading Factor',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_tax_ri'    => [
                    'label' => 'RI Tax(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'eol_comm_ib'    => [
                    'label' => 'IB Commission(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'flag_eol_rr'    => [
                    'label' => 'Reinstatement Required',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list[0,1]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + [0 => 'No', 1 => 'Yes'],
                    '_required' => true
                ]
            ];
        }
        else
        {
            $col_headings = ['Title', 'Quota', '1st Surplus', '2nd Surplus', '3rd Surplus'];
            $tnc_col_postfix = ['quota','surplus_1', 'surplus_2', 'surplus_3'];
            $tnc_val_prefix = [
                'qs_comm_ri'    => [
                    'label' => 'RI Commission(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_tax_ri'    => [
                    'label' => 'RI Tax(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_comm_ib'    => [
                    'label' => 'Insurance Board Commission(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_piop'    => [
                    'label' => 'Portfolio In & Out Premium(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_piol'    => [
                    'label' => 'Portfolio In & Out Loss(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_pio_ib_cp'    => [
                    'label' => 'Portfolio In & Out IB Claim Provision(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'qs_profit_comm'    => [
                    'label' => 'Profit Commission(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                'flag_qs_comm_scale' => [
                    'label' => 'Apply Commission Scale',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list[0,1]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + [0 => 'No', 1 => 'Yes'],
                    '_required' => true
                ]
            ];
        }

        if($formatted)
        {
            $v_rules = [];
            foreach($tnc_val_prefix as $col_prefix => $rule_single)
            {
                foreach($tnc_col_postfix as $col_postfix)
                {
                    $rule_single['field'] = $col_prefix . '_' . $col_postfix;
                    $v_rules[] = $rule_single;
                }
            }

            return $v_rules;
        }
        else
        {
            return [
                'col_headings'      => $col_headings,
                'tnc_val_prefix'    => $tnc_val_prefix,
                'tnc_col_postfix'   => $tnc_col_postfix
            ];
        }
    }

    // ----------------------------------------------------------------

    /**
     * Add New Treaty
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Insert Master Record
     *      b. Insert Broker Relation Records
     *      c. Insert Portfolio Configuration (Empty Record)
     *      d. Insert Reinsurer distribution
     *
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        // Extract All Brokers, Portfolios
        $broker_ids     = $data['broker_ids'];
        $portfolio_ids  = $data['portfolio_ids'];

        // Remove unused fields
        unset($data['broker_ids']);
        unset($data['portfolio_ids']);


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $id                 = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Master Record, No Validation Required as it is performed on Controller
            $id = parent::insert($data, TRUE);

            // Task b. Insert Broker Relations
            if($id)
            {
                // Insert Batch Broker Data
                $this->batch_insert_treaty_brokers($id, $broker_ids);

                // Insert Batch Portfolio Data
                $this->batch_insert_treaty_portfolios($id, $portfolio_ids);

                // Log Activity
                $this->log_activity($id, 'C');
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $id;
    }

    // ----------------------------------------------------------------

    /**
     * Edit Treaty
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Update Master Record
     *      b. Update Broker Relation Records
     *      c. Update Portfolio Configuration
     *          - Old Records (if selected) - leave intact
     *          - New Records - empty
     *
     * @param array $data
     * @param array $old_data   Old reference data, such as old_portfolios, old_brokers etc.
     * @return mixed
     */
    public function edit($id, $data, $old_data)
    {
        // Extract All Brokers, Portfolios
        $broker_ids     = $data['broker_ids'];
        $portfolio_ids  = $data['portfolio_ids'];

        // Remove unused fields
        unset($data['broker_ids']);
        unset($data['portfolio_ids']);

        // Find To Insert/Delete Portfolios
        $to_insert_portfolios = array_diff($portfolio_ids, $old_data['old_portfolios']);
        $to_delete_portfolios = array_diff($old_data['old_portfolios'], $portfolio_ids);


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Master Record, No Validation Required as it is performed on Controller
            $status = parent::update($id, $data, TRUE);

            // Task b. Update Broker Relations
            if($status)
            {
                // Delete Old Brokers Relation
                $this->delete_brokers_by_treaty($id);

                // Insert Batch Broker Data
                $this->batch_insert_treaty_brokers($id, $broker_ids);

                // Delete Old Portfolio Relation
                $this->delete_specific_portfolios_by_treaty($id, $to_delete_portfolios);

                // Insert Batch Portfolio Data
                $this->batch_insert_treaty_portfolios($id, $to_insert_portfolios);

                // Log Activity
                $this->log_activity($id, 'E');
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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
     * Save Tax & Commission Configuration of a Treaty
     *
     * @param integer $id Treaty ID
     * @param array $data
     * @return bool
     */
    public function save_treaty_tnc($id, $data)
    {
        // Get only fillable fields
        $fillable_data = [];
        foreach( self::$tnc_fillables as $col )
        {
            $fillable_data[$col] = $data[$col] ?? NULL;
        }


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Data
            $this->db->where('treaty_id', $id)
                     ->set($fillable_data)
                     ->update(self::$table_treaty_tax_and_commission);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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
     * Save Commission Scale of a Treaty
     *
     * @param integer $id Treaty ID
     * @param array $data
     * @return bool
     */
    public function save_treaty_commission_scale($id, $data)
    {
        // Prepare JSON for commission scale
        $total_count = count($data['name']);
        $scale_data = [];
        $json = [];
        for($i = 0; $i<$total_count; $i++)
        {
            $json[] = [
                'name'      => $data['name'][$i],
                'scale_min' => $data['scale_min'][$i],
                'scale_max' => $data['scale_max'][$i],
                'rate'      => $data['rate'][$i],
            ];
        }
        $scale_data['scales'] = $json ? json_encode($json) : NULL;


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Data
            $this->db->where('treaty_id', $id)
                     ->set($scale_data)
                     ->update(self::$table_treaty_commission_scale);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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
     * Save Treaty Distribution
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Delete old treaty distribution
     *      b. Update New treaty distribution
     *
     * @param array $data
     * @return mixed
     */
    public function save_treaty_distribution($id, $data)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Delete Old Distribution
            $this->delete_distribution_by_treaty($id);

            // Batch Insert distribution data
            $this->batch_insert_treaty_distribution($id, $data);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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

    public function batch_insert_treaty_distribution($id, $data)
    {
        // Extract All Data
        $reinsurer_ids          = $data['reinsurer_ids'];
        $distribution_percent   = $data['distribution_percent'];

        $batch_distribution_data = [];

        if( !empty($reinsurer_ids) && count($reinsurer_ids) === count($distribution_percent) )
        {
            for($i=0; $i < count($reinsurer_ids); $i++)
            {
                $batch_distribution_data[] = [
                    'treaty_id'             => $id,
                    'company_id'            => $reinsurer_ids[$i],
                    'distribution_percent'  => $distribution_percent[$i],
                    'flag_leader'           => IQB_FLAG_OFF
                ];
            }

            // Set First Row as Leader
            $batch_distribution_data[0]['flag_leader'] = IQB_FLAG_ON;
        }

        // Insert Batch Broker Data
        if( $batch_distribution_data )
        {
            return $this->db->insert_batch(self::$table_treaty_distribution, $batch_distribution_data);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function delete_distribution_by_treaty($id)
    {
        return $this->db->where('treaty_id', $id)
                        ->delete(self::$table_treaty_distribution);
    }

    // ----------------------------------------------------------------

    public function batch_insert_treaty_brokers($id, $broker_ids)
    {
        $batch_broker_data = [];
        foreach($broker_ids as $company_id)
        {
            $batch_broker_data[] = [
                'treaty_id'     => $id,
                'company_id'    => $company_id
            ];
        }

        // Insert Batch Broker Data
        if( $batch_broker_data )
        {
            return $this->db->insert_batch(self::$table_treaty_brokers, $batch_broker_data);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function delete_brokers_by_treaty($id)
    {
        return $this->db->where('treaty_id', $id)
                        ->delete(self::$table_treaty_brokers);
    }

    // ----------------------------------------------------------------

    /**
     * Save Treaty Portfolios
     *
     * All transactions must be carried out, else rollback.
     * Since the number of portfolios are fixed (which are added during treaty add/edit),
     * we will only update the configuration for existing portfolios
     *
     * @param array $data
     * @return mixed
     */
    public function save_treaty_portfolios($id, $data)
    {
        $status                     = TRUE;
        $treaty_portfolio_fillables = ['ac_basic','flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amount', 'qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_line', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3', 'eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];

        $total_portfolios           = count($data['portfolio_ids']);
        $treaty_id                  = $id;

         // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            for($i = 0; $i < $total_portfolios; $i++)
            {
                $portfolio_id = $data['portfolio_ids'][$i];
                $treaty_portfolio_data = [];

                foreach($treaty_portfolio_fillables as $column)
                {
                    $treaty_portfolio_data[$column] = $data[$column][$i] ?? NULL; // Reset to Default
                }

                // Update Treaty Portfolio Configuration
                $this->db->where('treaty_id', $treaty_id)
                         ->where('portfolio_id', $portfolio_id)
                         ->set($treaty_portfolio_data)
                         ->update(self::$table_treaty_portfolios);
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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

    public function batch_insert_treaty_portfolios($id, $portfolio_ids)
    {
        $batch_portfolio_data = [];
        foreach($portfolio_ids as $portfolio_id)
        {
            $batch_portfolio_data[] = [
                'treaty_id'         => $id,
                'portfolio_id'      => $portfolio_id
            ];
        }

        // Insert Batch Broker Data
        if( $batch_portfolio_data )
        {
            return $this->db->insert_batch(self::$table_treaty_portfolios, $batch_portfolio_data);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function delete_specific_portfolios_by_treaty($id, $portfolio_ids)
    {
        if( !empty($portfolio_ids) && is_array($portfolio_ids))
        {
            return $this->db
                            ->where('treaty_id', $id)
                            ->where_in('portfolio_id', $portfolio_ids)
                            ->delete(self::$table_treaty_portfolios);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     * Callback Portfolio: Check Duplicate
     *
     * Checks if the supplied portfolio only exists for 1 time per fiscal year.
     * i.e. a portfolio can only be associated with one Treaty per Fiscal Year.
     *
     * @param array $where
     * @param integernull $id
     * @return bool
     */
    public function _cb_portfolio__check_duplicate($fiscal_yr_id, $portfolio_id, $id=NULL)
    {
        $this->db
                ->from($this->table_name . ' AS T')
                ->join(self::$table_treaty_portfolios . ' TP', 'T.id = TP.treaty_id')
                ->where('T.fiscal_yr_id', $fiscal_yr_id)
                ->where('TP.portfolio_id', $portfolio_id);

        if( $id )
        {
            $this->db->where('T.id !=', $id);
        }
        return $this->db->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('T.id', $id)
                 ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {

        $this->_row_select();

        /**
         * Apply Filter
         */
        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['T.id <=' => $next_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['T.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $treaty_type_id = $params['treaty_type_id'] ?? NULL;
            if( $treaty_type_id )
            {
                $this->db->where(['T.treaty_type_id' =>  $treaty_type_id]);
            }
        }

        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('T.id', 'desc')
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('T.id, T.name, T.fiscal_yr_id, T.treaty_type_id, T.estimated_premium_income, T.treaty_effective_date, T.file, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, TT.name AS treaty_type_name')
                ->from($this->table_name . ' AS T')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id');
    }

    // ----------------------------------------------------------------

    /**
     * Get Details of a Single Record
     *
     * @param integer $id
     * @return object
     */
    public function get($id)
    {
        return $this->db->select(

                        // Main table -  all fields
                        'T.*, ' .

                        // Treaty Tax and Commission - all fields except treaty_id
                        'TTNC.*, ' .

                        // Treaty Commission Scale
                        'TCS.scales as commission_scales, ' .

                        // Fiscal year table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

                        // Treaty Type table
                        'TT.name AS treaty_type_name'
                        )
                ->from($this->table_name . ' AS T')
                ->join(self::$table_treaty_tax_and_commission . ' TTNC', 'TTNC.treaty_id = T.id')
                ->join(self::$table_treaty_commission_scale . ' TCS', 'TCS.treaty_id = T.id')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id')
                ->where('T.id', $id)
                ->get()->row();
    }

	// --------------------------------------------------------------------

    public function get_brokers_by_treaty($id)
    {
        return $this->db->select('TB.treaty_id, TB.company_id, C.name, C.picture, C.pan_no, C.active, C.type, C.contact')
                        ->from(self::$table_treaty_brokers . ' AS TB')
                        ->join('master_companies C', 'C.id = TB.company_id')
                        ->where('TB.treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function get_brokers_by_treaty_dropdown($id)
    {
        $list = $this->get_brokers_by_treaty($id);
        $brokers = [];
        foreach($list as $record)
        {
            $brokers["{$record->company_id}"] = $record->name;
        }
        return $brokers;
    }

    // --------------------------------------------------------------------

    public function get_portfolios_by_treaty($id)
    {
        return $this->db->select(
                            // Treaty Portfolio Config
                            'TP.treaty_id, TP.portfolio_id, TP.ac_basic, TP.flag_claim_recover_from_ri, TP.flag_comp_cession_apply, TP.comp_cession_percent, TP.comp_cession_max_amount, TP.qs_max_ret_amt, TP.qs_def_ret_amt, TP.flag_qs_line, TP.qs_retention_percent, TP.qs_quota_percent, TP.qs_lines_1, TP.qs_lines_2, TP.qs_lines_3, TP.eol_layer_amount_1, TP.eol_layer_amount_2, TP.eol_layer_amount_3, TP.eol_layer_amount_4, ' .

                            // Portfolio Detail
                            'P.code as portfolio_code, P.name_en AS portfolio_name_en, P.name_np AS portfolio_name_np, ' .
                            'PP.code as protfolio_parent_code, PP.name_en as portfolio_parent_name_en, PP.name_np as portfolio_parent_name_np'
                            )
                        ->from(self::$table_treaty_portfolios . ' AS TP')
                        ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                        ->join('master_portfolio PP', 'P.parent_id = PP.id', 'left')
                        ->where('TP.treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function get_portfolios_by_treaty_dropdown($id)
    {
        $list = $this->get_portfolios_by_treaty($id);
        $portfolios = [];
        foreach($list as $record)
        {
            $portfolios["{$record->portfolio_id}"] = $record->portfolio_name_en;
        }
        return $portfolios;
    }

    // --------------------------------------------------------------------

    public function get_treaty_distribution_by_treaty($id)
    {
        return $this->db->select('TD.treaty_id, TD.company_id, TD.distribution_percent, TD.flag_leader, C.name, C.picture, C.pan_no, C.active, C.type, C.contact')
                        ->from(self::$table_treaty_distribution . ' TD')
                        ->join('master_companies C', 'C.id = TD.company_id')
                        ->where('TD.treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            ''
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
        // Let's not delete now
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
            'module'    => 'ri_setup_treaty',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}