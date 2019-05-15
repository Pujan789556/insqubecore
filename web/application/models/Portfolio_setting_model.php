<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portfolio_setting_model extends MY_Model
{
    protected $table_name = 'master_portfolio_settings';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'fiscal_yr_id', 'portfolio_id', 'ri_liability_options', 'agent_commission', 'bs_service_charge', 'direct_discount', 'pool_premium', 'stamp_duty', 'amt_default_basic_premium', 'amt_default_pool_premium', 'flag_default_duration', 'default_duration', 'flag_short_term', 'flag_short_term_apply_for', 'short_term_policy_rate', 'flag_apply_vat_on_premium', 'flag_installment', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
    }

    // ----------------------------------------------------------------

    public function get_validation_rules($action, $record = NULL)
    {
        $rules = [];
        if($action == 'add' || $action == 'duplicate')
        {
            $rules = [
               [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]|callback__cb_settings_check_duplicate',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_required' => true
                ],
            ];
        }
        else if($action == 'edit')
        {
            $existing_ri_liability_options = explode(',', $record->ri_liability_options ?? '');
            $rules = [
                [
                    'field' => 'agent_commission',
                    'label' => 'Agent Commission(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_key'      => 'agent_commission',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'ri_liability_options[]',
                    'label' => 'RI Liability Options(%)',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',',array_keys(IQB_PORTFOLIO_LIABILITY_OPTION__LIST)).']',
                    '_key'      => 'ri_liability_options',
                    '_type'     => 'checkbox-group',
                    '_data'     => IQB_PORTFOLIO_LIABILITY_OPTION__LIST,
                    '_list_inline' => false,
                    '_checkbox_value' => $existing_ri_liability_options,
                    '_required' => true
                ],
                [
                    'field' => 'bs_service_charge',
                    'label' => 'Beema Samiti Service Charge(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'bs_service_charge',
                    '_required' => true
                ],
                [
                    'field' => 'direct_discount',
                    'label' => 'Direct Discount(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'direct_discount',
                    '_required' => true
                ],
                [
                    'field' => 'pool_premium',
                    'label' => 'Pool Premium(%)',
                    'rules' => 'trim|required|prep_decimal4|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_key'      => 'pool_premium',
                    '_required' => true
                ],
                [
                    'field' => 'stamp_duty',
                    'label' => 'Stamp Duty(Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'stamp_duty',
                    '_required' => true
                ],
                [
                    'field' => 'amt_default_basic_premium',
                    'label' => 'Default Basic Premium( Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'amt_default_basic_premium',
                    '_required' => true
                ],
                [
                    'field' => 'amt_default_pool_premium',
                    'label' => 'Default Pool Premium (Rs)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                    '_type'     => 'text',
                    '_key'      => 'amt_default_pool_premium',
                    '_required' => true
                ],
                [
                    'field' => 'flag_default_duration',
                    'label' => 'Default Duration Applies?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_default_duration',
                    '_required' => true
                ],
                [
                    'field' => 'default_duration',
                    'label' => 'Default Duration (Days)',
                    'rules' => 'trim|required|integer|max_length[5]',
                    '_default'  => 365,
                    '_type'     => 'text',
                    '_key'      => 'default_duration',
                    '_required' => true
                ],
                [
                    'field' => 'flag_short_term',
                    'label' => 'Has short term Policy?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_short_term',
                    '_required' => true
                ],
                [
                    'field' => 'flag_short_term_apply_for',
                    'label' => 'Short term apply for?',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',', array_keys(IQB_PFS_FLAG_SHORT_TERM_APPLY_FOR__LIST)).']',
                    '_data' => IQB_BLANK_SELECT + IQB_PFS_FLAG_SHORT_TERM_APPLY_FOR__LIST,
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_short_term_apply_for',
                    '_required' => true
                ],
                [
                    'field' => 'flag_apply_vat_on_premium',
                    'label' => 'Apply VAT on Policy Premium?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_apply_vat_on_premium',
                    '_required' => true
                ],
                [
                    'field' => 'flag_installment',
                    'label' => 'Allow payment in installment?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(_FLAG_yes_no_dropdown(FALSE))).']',
                    '_data' => _FLAG_yes_no_dropdown(),
                    '_type'     => 'dropdown',
                    '_key'      => 'flag_installment',
                    '_required' => true
                ]
            ];
        }
        return $rules;
    }

    // ----------------------------------------------------------------

    /**
     * Add Blank Portfolio Settings Record for given fiscal year
     *
     * @param int $fiscal_yr_id
     * @return bool
     */
    public function add($fiscal_yr_id)
    {
        $this->load->model('portfolio_model');
        $children_portfolios = $this->portfolio_model->dropdown_children();


        $done  = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            // Insert Individual - No batch-insert - because of Audit Log Requirement
            foreach($children_portfolios as $portfolio_id => $portfolio_name)
            {
                $single_data = [
                    'fiscal_yr_id'              => $fiscal_yr_id,
                    'portfolio_id'              => $portfolio_id
                ];
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
        $source_settings = $this->get_src_list_by_fiscal_year($source_fiscal_year_id);


        $done  = TRUE;
        if($source_settings)
        {
            // Use automatic transaction
            $this->db->trans_start();

                foreach($source_settings as $src)
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
     * Import Missing Portfolios for given Fiscal Year
     *
     * @param int $fiscal_yr_id
     * @return bool
     */
    public function import_missing($fiscal_yr_id)
    {
        $this->load->model('portfolio_model');

        // Valid Record ?
        $fiscal_yr_id   = (int)$fiscal_yr_id;
        $existing_child_portfolios  = $this->get_portfolios_by_fiscal_year($fiscal_yr_id);
        $all_child_portfolios       = $this->portfolio_model->get_children();

        $existing = [];
        $all = [];
        foreach($existing_child_portfolios as $e)
        {
            $existing[] = $e->portfolio_id;
        }
        foreach($all_child_portfolios as $a)
        {
            $all[] = $a->id;
        }

        $existing   = array_values($existing);
        $all        = array_values($all);

        asort($existing);
        asort($all);

        $missing = array_diff($all, $existing);

        $done  = TRUE;
        if( count($missing) )
        {
            // Use automatic transaction
            $this->db->trans_start();

                foreach($missing as $portfolio_id)
                {
                    $single_data = [
                        'fiscal_yr_id' => $fiscal_yr_id,
                        'portfolio_id' => $portfolio_id,
                    ];
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
            $row = $this->db->select('PS.*, P.name_en as portfolio_name')
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

    /**
     * Get Shortterm Flag from Portfolio Settings
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @return char
     */
    public function get_short_term_flag($fiscal_yr_id, $portfolio_id)
    {
        $record = $this->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);

        if(!$record)
        {
            $fy_record = $this->fiscal_year_model->get($fiscal_yr_id);
            throw new Exception("Exception [Model: Portfolio_setting_model][Method: get_short_term_flag()]: No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en})");
        }

        return $record->flag_short_term;
    }

    // ----------------------------------------------------------------

    /**
     * Compute Short Term Flag for Policy For Given Duration
     *
     * If portfolio setting has no short term flag set, return NO
     * else compute if the duration falls on short term
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @param date $start_date
     * @param date $end_date
     * @return char
     */
    public function compute_short_term_flag($fiscal_yr_id, $portfolio_id, $start_date, $end_date)
    {
        $goodies = $this->compute_short_term_goodies($fiscal_yr_id, $portfolio_id, $start_date, $end_date);

        return $goodies['flag'];
    }

    // ----------------------------------------------------------------

    /**
     * Compute Short Term Rate for Policy For Given Duration
     *
     * If portfolio setting has no short term flag set, return NO
     * else compute if the duration falls on short term
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @param date $start_date
     * @param date $end_date
     * @return char
     */
    public function compute_short_term_rate($fiscal_yr_id, $portfolio_id, $start_date, $end_date)
    {
        $goodies    = $this->compute_short_term_goodies($fiscal_yr_id, $portfolio_id, $start_date, $end_date);
        $spr        = $goodies['spr_record'];

        if(!$spr)
        {
            /**
             * If we do not have any sort term rate, the policy falls under default duration.
             * So we return full rate.
             */
            $spr = (object)['rate' => 100, 'duration' => $goodies['pfs_record']->default_duration, 'title' => 'Full-term Rate'];
        }

        return $spr->rate;
    }

    // ----------------------------------------------------------------

    /**
     * Compute Short Term Goodies for Policy For Given Duration
     *
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @param date $start_date
     * @param date $end_date
     * @return char
     */
    public function compute_short_term_goodies($fiscal_yr_id, $portfolio_id, $start_date, $end_date)
    {
        $record     = $this->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);
        $fy_record  = $this->fiscal_year_model->get($fiscal_yr_id);

        if(!$record)
        {
            throw new Exception("Exception [Model: Portfolio_setting_model][Method: compute_short_term_flag()]: No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en}).");
        }

        // FLAG NOT SET???
        if( empty($record->flag_short_term) )
        {
            throw new Exception("Exception [Model: Portfolio_setting_model][Method: compute_short_term_flag()]: Short term flag has not been configured for this Portfolio Setting for specified fiscal year {$fy_record->code_np}({$fy_record->code_en}).");
        }

        // Portfolio does not support short term policy? Return NO
        if( $record->flag_short_term !== IQB_FLAG_YES )
        {
            return [
                'flag'          => $record->flag_short_term,
                'spr_record'    => NULL
            ];
        }

        /**
         * Durations
         */
        $default_duration   = (int)$record->default_duration;
        $policy_duration    = _POLICY_duration($start_date, $end_date, 'd');

        /**
         * Policy Duration > Default Duration
         */
        if( $policy_duration > $default_duration )
        {
            throw new Exception("Exception [Model: Portfolio_setting_model][Method: compute_short_term_flag()]: 'Policy Duration' is greater than 'Defualt Duration' for this Portfolio Setting for specified fiscal year {$fy_record->code_np}({$fy_record->code_en}).");
        }


        $rates = json_decode($record->short_term_policy_rate ?? NULL);
        if( !$rates )
        {
            throw new Exception("Exception [Model: Portfolio_setting_model][Method: compute_short_term_flag()]: No Short Term Policy Rates found for the supplied portfolio for specified fiscal year {$fy_record->code_np}({$fy_record->code_en}).");
        }

        $rate_list = [];
        foreach($rates as $r)
        {
            $rate_list[$r->duration] = $r;
        }
        ksort($rate_list);

        $flag_found_short_term_rate = FALSE;
        $spr_record                 = NULL;
        foreach($rate_list as $duration=>$spr)
        {
            if( $policy_duration <= $duration )
            {
                $spr_record = $spr; // {rate:xxx, duration:xxx, title:xxx}
                $flag_found_short_term_rate  = TRUE;
                break;
            }
        }

        if( $flag_found_short_term_rate )
        {
            $flag_short_term = IQB_FLAG_YES;
        }
        else
        {
            $flag_short_term = IQB_FLAG_NO;
        }

        // Return the goodies
        return [
            'flag'          => $flag_short_term,
            'spr_record'    => $spr_record,
            'pfs_record'    => $record
        ];
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