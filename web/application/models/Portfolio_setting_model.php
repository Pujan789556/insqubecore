<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portfolio_setting_model extends MY_Model
{
    protected $table_name = 'master_portfolio_settings';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'fiscal_yr_id', 'portfolio_id', 'agent_commission', 'bs_service_charge', 'direct_discount', 'pool_premium', 'stamp_duty', 'amt_default_basic_premium', 'amt_default_pool_premium', 'flag_default_duration', 'default_duration', 'flag_short_term', 'short_term_policy_rate', 'flag_installment', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


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

        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules(  )
    {
        $this->validation_rules = [

            'fiscal_yr' => [
               [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]|callback__cb_settings_check_duplicate',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_required' => true
                ],
            ],

            'basic' => [
                [
                    'field' => 'portfolio_id[]',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_key'      => 'portfolio_id',
                    '_required' => true
                ],
                [
                    'field' => 'agent_commission[]',
                    'label' => 'Agent Commission(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_key'      => 'agent_commission',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'bs_service_charge[]',
                    'label' => 'Beema Samiti Service Charge(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'bs_service_charge',
                    '_required' => true
                ],
                [
                    'field' => 'direct_discount[]',
                    'label' => 'Direct Discount(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'direct_discount',
                    '_required' => true
                ],
                [
                    'field' => 'pool_premium[]',
                    'label' => 'Pool Premium(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'pool_premium',
                    '_required' => true
                ],
                [
                    'field' => 'stamp_duty[]',
                    'label' => 'Stamp Duty(Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'stamp_duty',
                    '_required' => true
                ],
                [
                    'field' => 'amt_default_basic_premium[]',
                    'label' => 'Default Basic Premium( Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'amt_default_basic_premium',
                    '_required' => true
                ],
                [
                    'field' => 'amt_default_pool_premium[]',
                    'label' => 'Default Pool Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'amt_default_pool_premium',
                    '_required' => true
                ],
                [
                    'field' => 'flag_default_duration[]',
                    'label' => 'Default Duration Applies?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_default_duration',
                    '_required' => true
                ],
                [
                    'field' => 'default_duration[]',
                    'label' => 'Default Duration (Days)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_default'  => 365,
                    '_type'     => 'text',
                    '_key'      => 'default_duration',
                    '_required' => true
                ],
                [
                    'field' => 'flag_short_term[]',
                    'label' => 'Has short term Policy?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_short_term',
                    '_required' => true
                ],
                [
                    'field' => 'flag_installment[]',
                    'label' => 'Allow payment in installment?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_installment',
                    '_required' => true
                ],

                /**
                 * !!!NOTE: This MUST be the last element
                 */
                [
                    'field' => 'setting_id[]',
                    'label' => 'Settings',
                    'rules' => 'trim|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_key'      => 'setting_id',
                    '_required' => true
                ]
            ]
        ];

    }

    // ----------------------------------------------------------------

    public function get_validation_rules( $sections = [], $formatted = FALSE )
    {
        $rules = [];

        if( !empty($sections) )
        {
            foreach($sections as $section)
            {
                $rules[$section] = $this->validation_rules[$section];
            }
        }
        else
        {
            $rules = $this->validation_rules;
        }


        /**
         * Formatted Rules
         */
        $v_rules = [];
        if($formatted)
        {
            foreach($rules as $section=>$section_rules)
            {
                $v_rules = array_merge($v_rules, $section_rules);
            }
            return $v_rules;
        }

        // Return Sectioned Rules
        return $rules;
    }

    // ----------------------------------------------------------------

    public function get_portfolio_short_term_flag_by_fiscal_year($fiscal_yr_id, $portfolio_id)
    {
        $record = $this->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);
        return $record->flag_short_term;
    }

    // ----------------------------------------------------------------

    public function get_row_list()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pfs_row_list');
        if(!$list)
        {
            $list = $this->db->select('PS.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PS')
                                ->join('master_fiscal_yrs FY', 'FY.id = PS.fiscal_yr_id')
                                ->group_by('PS.fiscal_yr_id')
                                ->order_by('PS.fiscal_yr_id', 'desc')
                                ->get()->result();
            $this->write_cache($list, 'pfs_row_list', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_row_single($fiscal_yr_id)
    {
        return $this->db->select('PS.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PS')
                                ->join('master_fiscal_yrs FY', 'FY.id = PS.fiscal_yr_id')
                                ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                                ->get()->row();
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('PS.id, PS.fiscal_yr_id, PS.portfolio_id, PS.agent_commission, PS.bs_service_charge, PS.direct_discount, PS.pool_premium, PS.stamp_duty, PS.amt_default_basic_premium, PS.amt_default_pool_premium, PS.flag_short_term, PS.short_term_policy_rate, PS.flag_default_duration, PS.default_duration, PS.flag_installment, P.name_en as portfolio_name, PP.name_en as portfolio_parent_name, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np')
                        ->from($this->table_name . ' PS')
                        ->join('master_fiscal_yrs FY', 'FY.id = PS.fiscal_yr_id')
                        ->join('master_portfolio P', 'P.id = PS.portfolio_id')
                        ->join('master_portfolio PP', 'P.parent_id = PP.id')
                        ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get_src_list_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('PS.*')
                        ->from($this->table_name . ' PS')
                        ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get_portfolios_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('PS.portfolio_id')
                        ->from($this->table_name . ' PS')
                        ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'pfs_' . $fiscal_yr_id . '_' . $portfolio_id;
        $row = $this->get_cache($cache_name);
        if(!$row)
        {
            $row = $this->db->select('PS.id, PS.fiscal_yr_id, PS.portfolio_id, PS.agent_commission, PS.bs_service_charge, PS.direct_discount, PS.pool_premium, PS.stamp_duty, PS.flag_short_term, PS.short_term_policy_rate, PS.flag_default_duration, PS.default_duration, PS.flag_installment, P.name_en as portfolio_name')
                        ->from($this->table_name . ' PS')
                        ->join('master_portfolio P', 'P.id = PS.portfolio_id')
                        ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                        ->where('PS.portfolio_id', $portfolio_id)
                        ->get()->row();
            $this->write_cache($row, $cache_name, CACHE_DURATION_DAY);
        }
        return $row;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $setting_ids=NULL)
    {
        if( $setting_ids )
        {
            $setting_ids = is_array($setting_ids) ? $setting_ids : [$setting_ids];
            $this->db->where_not_in('id', $setting_ids);
        }
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
            'pfs_row_list',
            'pfs_*'
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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}