<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_portfolio_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaty_portfolios';

    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = TRUE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'treaty_id', 'portfolio_id', 'ac_basic', 'treaty_distribution_for', 'flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amt', 'comp_cession_comm_ri', 'comp_cession_tax_ri', 'comp_cession_tax_ib', 'treaty_max_capacity_amt', 'qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3', 'eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];

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
    }

    // --------------------------------------------------------------------

    /**
     * Validation Rules
     *
     * @param int $treaty_id
     * @param int $treaty_type_id
     * @return array
     */
    public function v_rules($treaty_id, $treaty_type_id)
    {
        $treaty_type_id = (int)$treaty_type_id;

        $v_rules = $this->_v_rules();

        // Format Rules
        $portfolio_dropdown = $this->portfolio_dropdown($treaty_id);
        $v_rules_formatted  = $v_rules['portfolios_common'];

        // First rule is 'portfolio_ids[]', update validation rule
        $v_rules_formatted[1]['rules'] = 'trim|required|integer|max_length[8]|in_list['.implode(',',array_keys($portfolio_dropdown)).']';


        if( $treaty_type_id === IQB_RI_TREATY_TYPE_SP )
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $v_rules['portfolios_sp']);
        }
        else if( $treaty_type_id === IQB_RI_TREATY_TYPE_QT )
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $v_rules['portfolios_qt']);
        }
        else if( $treaty_type_id === IQB_RI_TREATY_TYPE_QS )
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $v_rules['portfolios_qs']);
        }
        else if( $treaty_type_id === IQB_RI_TREATY_TYPE_EOL )
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $v_rules['portfolios_eol']);
        }

        return $v_rules_formatted;
    }

        private function _v_rules()
        {
            return $v_rules = [
                // Treaty Portfolios: Common Fields
                'portfolios_common' => [
                    [
                        'field' => 'ids[]',
                        'label' => 'ID',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_field'        => 'id',
                        '_type'         => 'hidden',
                        '_show_label'   => false,
                        '_required'     => true
                    ],
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
                    // [
                    //     'field' => 'treaty_distribution_for[]',
                    //     'label' => 'Treaty Distribution For',
                    //     'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(IQB_PORTFOLIO_LIABILITY_OPTION__LIST) ) . ']',
                    //     '_field'        => 'treaty_distribution_for',
                    //     '_type'         => 'dropdown',
                    //     '_show_label'   => false,
                    //     '_data'         => IQB_BLANK_SELECT + IQB_PORTFOLIO_LIABILITY_OPTION__LIST,
                    //     '_required'     => true
                    // ],
                    [
                        'field' => 'flag_claim_recover_from_ri[]',
                        'label' => 'Claim Recover From RI',
                        'rules' => 'trim|required|integer|in_list[0,1]',
                        '_field'            => 'flag_claim_recover_from_ri',
                        '_type'             => 'dropdown',
                        '_show_label'       => false,
                        '_data'             => IQB_BLANK_SELECT + _FLAG_on_off_dropdown(),
                        '_required'         => true
                    ],
                    [
                        'field' => 'flag_comp_cession_apply[]',
                        'label' => 'Apply Compulsory Cession',
                        'rules' => 'trim|required|integer|in_list[0,1]',
                        '_field'            => 'flag_comp_cession_apply',
                        '_type'             => 'dropdown',
                        '_show_label'       => false,
                        '_data'             => IQB_BLANK_SELECT + _FLAG_on_off_dropdown(),
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
                        'field' => 'comp_cession_max_amt[]',
                        'label' => 'Compulsory Max Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_field'            => 'comp_cession_max_amt',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'comp_cession_comm_ri[]',
                        'label' => 'Compulsory Cession RI Commission (%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'comp_cession_comm_ri',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'comp_cession_tax_ri[]',
                        'label' => 'Compulsory Cession RI Tax (%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'comp_cession_tax_ri',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'comp_cession_tax_ib[]',
                        'label' => 'Compulsory Cession IB Tax (%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'comp_cession_tax_ib',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'treaty_max_capacity_amt[]',
                        'label' => 'Treaty Maximum Capacity',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_field'            => 'treaty_max_capacity_amt',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],
                ],

                // Treaty Portfolios: "Quota" Only Fields
                'portfolios_qt' => [

                    [
                        'field' => 'qs_retention_percent[]',
                        'label' => 'Quota Retention(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'qs_retention_percent',
                        '_type'             => 'text',
                        '_show_label'   => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'qs_quota_percent[]',
                        'label' => 'Quota Distribution(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
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
                    /**
                     * Apply flat retention ?
                     */
                    [
                        'field' => 'flag_qs_def_ret_apply[]',
                        'label' => 'Apply defined retention?',
                        'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(_FLAG_on_off_dropdown(false)) ) . ']',
                        '_field'        => 'flag_qs_def_ret_apply',
                        '_type'         => 'dropdown',
                        '_show_label'   => false,
                        '_data'         => IQB_BLANK_SELECT + _FLAG_on_off_dropdown(),
                        '_required'     => true
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

                    /**
                     * Quota Share & Surplus Common Part
                     */
                    [
                        'field' => 'qs_max_ret_amt[]',
                        'label' => 'Maximum Quota/Retention Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_field'            => 'qs_max_ret_amt',
                        '_type'             => 'text',
                        '_show_label'   => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'qs_def_ret_amt[]',
                        'label' => 'Defined Quota/Retention Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|less_than[qs_max_ret_amt[]]',
                        '_field'            => 'qs_def_ret_amt',
                        '_type'             => 'text',
                        '_show_label'       => false,
                        '_required'         => true
                    ],

                    /**
                     * Apply flat retention ?
                     */
                    [
                        'field' => 'flag_qs_def_ret_apply[]',
                        'label' => 'Apply defined retention?',
                        'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(_FLAG_on_off_dropdown(false)) ) . ']',
                        '_field'        => 'flag_qs_def_ret_apply',
                        '_type'         => 'dropdown',
                        '_show_label'   => false,
                        '_data'         => IQB_BLANK_SELECT + _FLAG_on_off_dropdown(),
                        '_required'     => true
                    ],

                    /**
                     * Quota Share  Part
                     */
                    [
                        'field' => 'qs_retention_percent[]',
                        'label' => 'Quota Retention(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'qs_retention_percent',
                        '_type'             => 'text',
                        '_show_label'   => false,
                        '_required'         => true
                    ],
                    [
                        'field' => 'qs_quota_percent[]',
                        'label' => 'Quota Distribution(%)',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                        '_field'            => 'qs_quota_percent',
                        '_type'             => 'text',
                        '_show_label'   => false,
                        '_required'         => true
                    ],



                    /**
                     * Surplus Part
                     */
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

                // Treaty Portfolios: "Excell of Loss" only Fields
                'portfolios_eol' => [
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
                    /**
                     * Apply flat retention ?
                     */
                    [
                        'field' => 'flag_qs_def_ret_apply[]',
                        'label' => 'Apply defined retention?',
                        'rules' => 'trim|required|integer|exact_length[1]|in_list[' . implode( ',', array_keys(_FLAG_on_off_dropdown(false)) ) . ']',
                        '_field'        => 'flag_qs_def_ret_apply',
                        '_type'         => 'dropdown',
                        '_show_label'   => false,
                        '_data'         => IQB_BLANK_SELECT + _FLAG_on_off_dropdown(),
                        '_required'     => true
                    ],
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

    // --------------------------------------------------------------------

    /**
     * Add Portfolio for a Treaty
     *
     * @param int $treaty_id Treaty ID
     * @param int $portfolio_ids Portfolio IDs
     * @param int $fiscal_yr_id Treaty's Fiscal Year
     * @return bool
     */
    public function add_portfolios($treaty_id, $portfolio_ids, $fiscal_yr_id)
    {
        $done  = TRUE;

        $this->load->model('portfolio_setting_model');

        // Insert Individual - No batch-insert - because of Audit Log Requirement
        foreach($portfolio_ids as $portfolio_id )
        {
            // Get the portfolio Settings
            $pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);

            // Find the RI Liability Options for this portfolio
            $ri_liability_options = explode(',', $pfs_record->ri_liability_options ?? []);

            // Insert Only if RI distribution is set to at least one liability
            if($ri_liability_options)
            {
                foreach($ri_liability_options as $treaty_distribution_for)
                {
                    $single_data = [
                        'treaty_id'                 => $treaty_id,
                        'portfolio_id'              => $portfolio_id,
                        'treaty_distribution_for'   => $treaty_distribution_for
                    ];
                    parent::insert($single_data, TRUE);
                }
            }
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Save Treaty Portfolio Configuration
     *
     * All transactions must be carried out, else rollback.
     * Since the number of portfolios are fixed (which are added during treaty add/edit),
     * we will only update the configuration for existing portfolios
     *
     * @param int $treaty_id Treaty ID
     * @param array $data
     * @return mixed
     */
    public function save_portfolio_config($treaty_id, $data)
    {
        $status                     = TRUE;
        $treaty_portfolio_fillables = ['ac_basic', 'flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amt', 'comp_cession_comm_ri', 'comp_cession_tax_ri', 'comp_cession_tax_ib', 'treaty_max_capacity_amt', 'qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3', 'eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];

        $total_rows = count($data['ids']);

        // echo '<pre>'; print_r($total_rows); print_r($data);exit;
        // -----------------------------------------------------------------------------

        // Use automatic transaction
        $this->db->trans_start();

            for($i = 0; $i < $total_rows; $i++)
            {
                $id                         = $data['ids'][$i];
                $portfolio_id               = $data['portfolio_ids'][$i];
                $treaty_portfolio_data      = [];

                // Prepare Update Data
                foreach($treaty_portfolio_fillables as $column)
                {
                    $treaty_portfolio_data[$column] = $data[$column][$i] ?? NULL; // Reset to Default
                }


                parent::update($id, $treaty_portfolio_data, TRUE);

                // // Old Data - For Audit Record
                // $where = ['treaty_id' => $treaty_id, 'portfolio_id' => $portfolio_id, 'treaty_distribution_for' => $treaty_distribution_for];
                // $this->audit_old_record = parent::find_by($where);

                // // Save Database without MY Model ( as it can not find audit_old_record where there is no PK as id)
                // $this->db->where($where)
                //          ->set($treaty_portfolio_data)
                //          ->update($this->table_name);

                //  // Save Audit Log
                //  $this->save_audit_log([
                //     'method' => 'update',
                //     'id'     => NULL,
                //     'fields' => $treaty_portfolio_data
                // ],$where);
            }

            // Clear Cache
            $this->clear_cache();

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // -----------------------------------------------------------------------------

        // return result/status
        return $status;
    }

    // --------------------------------------------------------------------

    public function get_many_by_treaty($treaty_id)
    {
        return $this->_select()
                    ->where('T.id', $treaty_id)
                    ->get()->result();
    }

        // --------------------------------------------------------------------

        private function _select()
        {
            return $this->db->select(
                                // Treaty Details
                                'T.name as treaty_name, T.category, T.fiscal_yr_id, T.treaty_type_id, T.treaty_effective_date, ' .

                                // Treaty Portfolio Config
                                'TP.id, TP.treaty_id, TP.portfolio_id, TP.ac_basic, TP.treaty_distribution_for, TP.flag_claim_recover_from_ri, TP.flag_comp_cession_apply, TP.comp_cession_percent, TP.comp_cession_max_amt, TP.comp_cession_comm_ri, TP.comp_cession_tax_ri, TP.comp_cession_tax_ib, TP.treaty_max_capacity_amt, TP.qs_max_ret_amt, TP.qs_def_ret_amt, TP.flag_qs_def_ret_apply, TP.qs_retention_percent, TP.qs_quota_percent, TP.qs_lines_1, TP.qs_lines_2, TP.qs_lines_3, TP.eol_layer_amount_1, TP.eol_layer_amount_2, TP.eol_layer_amount_3, TP.eol_layer_amount_4, ' .

                                // Portfolio Detail
                                'P.code as portfolio_code, P.name_en AS portfolio_name_en, P.name_np AS portfolio_name_np, ' .
                                'PP.code as protfolio_parent_code, PP.name_en as portfolio_parent_name_en, PP.name_np as portfolio_parent_name_np'
                                )
                            ->from($this->table_name . ' AS TP')
                            ->join('ri_setup_treaties T', 'T.id = TP.treaty_id' )
                            ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                            ->join('master_portfolio PP', 'P.parent_id = PP.id', 'left');
        }

    // --------------------------------------------------------------------

    /**
     * Get Portfolio Treaty for Given Fiscal Year for Given Category
     *
     * @param int $portfolio_id
     * @param int $fiscal_yr_id
     * @param int $category
     * @return object
     */
    public function get_portfolio_treaty($portfolio_id, $fiscal_yr_id, $category)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ri_pt_' . $portfolio_id . '_' . $fiscal_yr_id . '_' . $category;
        $row        = $this->get_cache($cache_var);
        if(!$row)
        {
            $row = $this->_select()
                        ->where('T.category', $category)
                        ->where('T.fiscal_yr_id', $fiscal_yr_id)
                        ->where('P.id', $portfolio_id)
                        ->get()->row();
            if($row)
            {
                $this->write_cache($row, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $row;
    }

    // --------------------------------------------------------------------

    public function portfolio_dropdown($treaty_id)
    {
        $list = $this->db->select(
                                // Treaty Portfolio Config
                                'TP.treaty_id, TP.portfolio_id, ' .

                                // Portfolio Detail
                                'P.name_en AS portfolio_name_en'
                                )
                            ->from($this->table_name . ' AS TP')
                            ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                            ->where('TP.treaty_id', $treaty_id)
                            ->get()->result();
        $portfolios = [];
        foreach($list as $record)
        {
            $portfolios["{$record->portfolio_id}"] = $record->portfolio_name_en;
        }
        return $portfolios;
    }


	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'ri_pt_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_portfolios($treaty_id, $portfolio_ids)
    {
        $status         = FALSE;
        $old_records    = [];
        $old_records    = $this->db->where('treaty_id', $treaty_id)
                                    ->where_in('portfolio_id', $portfolio_ids)
                                    ->get($this->table_name)
                                    ->result();

        foreach($old_records as $single)
        {
            parent::delete($single->id);
        }

        return $status;
    }
}