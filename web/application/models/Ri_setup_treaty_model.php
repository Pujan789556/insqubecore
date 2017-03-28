<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaties';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'name', 'fiscal_yr_id', 'treaty_type_id', 'currency_contract', 'currency_settlement', 'estimated_premium_income', 'treaty_effective_date', 'file', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Set validation rule
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
                'field' => 'currency_contract',
                'label' => 'Contract Currency',
                'rules' => 'trim|required|alpha|max_length[10]|strtoupper',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'currency_settlement',
                'label' => 'Settlement Currency',
                'rules' => 'trim|required|alpha|max_length[10]|strtoupper',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'estimated_premium_income',
                'label' => 'Estimated Premium Income',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'treaty_effective_date',
                'label' => 'Treaty Effective Date',
                'rules' => 'trim|required|valid_date',
                '_type'     => 'date',
                '_required' => true
            ]
        ];
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
        $this->db->select('T.id, T.name, T.fiscal_yr_id, T.treaty_type_id, T.currency_contract, T.currency_settlement, T.estimated_premium_income, T.treaty_effective_date, T.file, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, TT.name AS treaty_type_name')
                ->from($this->table_name . ' as T')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join('ri_setup_treaty_types TT', 'TT.id = T.treaty_type_id');
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

                        // Treaty details table - all fields except treaty_id
                        'TDTL.ac_basic, TDTL.flag_claim_recover_from_ri, TDTL.flag_comp_cession_apply, TDTL.comp_cession_percent, TDTL.comp_cession_max_amount, TDTL.qs_max_ret_amt, TDTL.qs_def_ret_amt, TDTL.flag_qs_line, TDTL.qs_retention_percent, TDTL.qs_quota_percent, TDTL.qs_lines_1, TDTL.qs_lines_2, TDTL.qs_lines_3, TDTL.eol_layer_amount_1, TDTL.eol_layer_amount_2, TDTL.eol_layer_amount_3, TDTL.eol_layer_amount_4, ' .

                        // Treaty Tax and Commission - all fields except treaty_id
                        'TTNC.qs_comm_ri_quota, TTNC.qs_comm_ri_surplus_1, TTNC.qs_comm_ri_surplus_2, TTNC.qs_comm_ri_surplus_3, TTNC.qs_tax_ri_quota, TTNC.qs_tax_ri_surplus_1, TTNC.qs_tax_ri_surplus_2, TTNC.qs_tax_ri_surplus_3, TTNC.qs_comm_ib_quota, TTNC.qs_comm_ib_surplus_1, TTNC.qs_comm_ib_surplus_2, TTNC.qs_comm_ib_surplus_3, TTNC.qs_piop_quota, TTNC.qs_piop_surplus_1, TTNC.qs_piop_surplus_2, TTNC.qs_piop_surplus_3, TTNC.qs_piol_quota, TTNC.qs_piol_surplus_1, TTNC.qs_piol_surplus_2, TTNC.qs_piol_surplus_3, TTNC.qs_pio_ib_cp_quota, TTNC.qs_pio_ib_cp_surplus_1, TTNC.qs_pio_ib_cp_surplus_2, TTNC.qs_pio_ib_cp_surplus_3, TTNC.qs_profit_comm_quota, TTNC.qs_profit_comm_surplus_1, TTNC.qs_profit_comm_surplus_2, TTNC.qs_profit_comm_surplus_3, TTNC.qs_comm_scale_quota, TTNC.qs_comm_scale_surplus_1, TTNC.qs_comm_scale_surplus_2, TTNC.qs_comm_scale_surplus_3, TTNC.eol_min_n_deposit_amt_l1, TTNC.eol_min_n_deposit_amt_l2, TTNC.eol_min_n_deposit_amt_l3, TTNC.eol_min_n_deposit_amt_l4, TTNC.eol_premium_mode_l1, TTNC.eol_premium_mode_l2, TTNC.eol_premium_mode_l3, TTNC.eol_premium_mode_l4, TTNC.eol_min_rate_l1, TTNC.eol_min_rate_l2, TTNC.eol_min_rate_l3, TTNC.eol_min_rate_l4, TTNC.eol_max_rate_l1, TTNC.eol_max_rate_l2, TTNC.eol_max_rate_l3, TTNC.eol_max_rate_l4, TTNC.eol_fixed_rate_l1, TTNC.eol_fixed_rate_l2, TTNC.eol_fixed_rate_l3, TTNC.eol_fixed_rate_l4, TTNC.eol_loading_factor_l1, TTNC.eol_loading_factor_l2, TTNC.eol_loading_factor_l3, TTNC.eol_loading_factor_l4, TTNC.eol_tax_ri_l1, TTNC.eol_tax_ri_l2, TTNC.eol_tax_ri_l3, TTNC.eol_tax_ri_l4, TTNC.eol_comm_ib_l1, TTNC.eol_comm_ib_l2, TTNC.eol_comm_ib_l3, TTNC.eol_comm_ib_l4, TTNC.flag_eol_rr_l1, TTNC.flag_eol_rr_l2, TTNC.flag_eol_rr_l3, TTNC.flag_eol_rr_l4, ' .

                        // Fiscal year table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

                        // Treaty Type table
                        'TT.name AS treaty_type_name'
                        )
                ->from($this->table_name . ' as T')
                ->join('ri_setup_treaty_details TDTL', 'TDTL.treaty_id = T.id')
                ->join('ri_setup_treaty_tax_n_commission TTNC', 'TTNC.treaty_id = T.id')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join('ri_setup_treaty_types TT', 'TT.id = T.treaty_type_id')
                ->where('T.id', $id)
                ->get()->row();
    }

	// --------------------------------------------------------------------

    public function get_brokers_by_treaty($id)
    {
        return $this->db->select('TB.treaty_id, TB.company_id, C.name, C.picture, C.pan_no, C.active, C.type, C.contact')
                        ->from('ri_setup_treaty_broker TB')
                        ->join('master_companies C', 'C.id = TB.company_id')
                        ->where('TB.treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function get_portfolios_by_treaty($id)
    {
        return $this->db->select('TP.treaty_id, TP.portfolio_id, TP.config, P.code, P.name_en, P.name_np')
                        ->from('ri_setup_treaty_portfolio TP')
                        ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                        ->where('TP.treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function get_treaty_distribution_by_treaty($id)
    {
        return $this->db->select('TD.treaty_id, TD.company_id, TD.distribution_percent, TD.flag_leader, C.name, C.picture, C.pan_no, C.active, C.type, C.contact')
                        ->from('ri_setup_treaty_distribution TD')
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