<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tariff_property_model extends MY_Model
{
    protected $table_name = 'master_tariff_property';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'fiscal_yr_id', 'code', 'name_en', 'name_np', 'risks', 'tariff', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'code',
            'label' => 'Risk Code',
            'rules' => 'trim|required|max_length[20]|callback_check_duplicate',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[150]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[150]',
            '_type'     => 'text',
            '_required' => true
        ],
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 10; // Prevent first 12 records from deletion.

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

    public function v_rules_add_fy( $for_validation = FALSE)
    {
        $v_rules = [
            'fiscal_year' => [
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]|callback_check_duplicate_fiscal_year',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_default'  => '',
                    '_required' => true
                ]
            ],
            'risk_categories' => [
                [
                    'field' => 'ids[]',
                    '_key'  => 'id',
                    'label' => 'ID',
                    'rules' => 'trim|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_show_label' => false,
                    '_required' => false
                ],
                [
                    'field' => 'code[]',
                    '_key'  => 'code',
                    'label' => 'Risk Code',
                    'rules' => 'trim|required|max_length[20]|strtoupper|callback_check_duplicate_codes',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'name_en[]',
                    '_key'  => 'name_en',
                    'label' => 'Name (EN)',
                    'rules' => 'trim|required|max_length[150]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'name_np[]',
                    '_key'  => 'name_np',
                    'label' => 'Name (NP)',
                    'rules' => 'trim|required|max_length[150]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
            ]
        ];

        // return formatted?
        $fromatted_v_rules = [];
        if($for_validation === TRUE)
        {
            foreach($v_rules as $section=>$rules)
            {
                $fromatted_v_rules = array_merge($fromatted_v_rules, $rules);
            }
            return $fromatted_v_rules;
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

    public function v_rules_duplicate_fy()
    {
        return [
            [
                'field' => 'fiscal_yr_id',
                'label' => 'Fiscal Year',
                'rules' => 'trim|required|integer|max_length[3]|callback_check_duplicate_fiscal_year',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                '_default'  => '',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function v_rules_risks()
    {
        return [
            [
                'field' => 'risks[code][]',
                '_key'  => 'code',
                'label' => 'Risk Code',
                'rules' => 'trim|required|max_length[20]|callback__cb_risk_duplicate',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'risks[name_en][]',
                '_key'  => 'name_en',
                'label' => 'Name (EN)',
                'rules' => 'trim|required|max_length[500]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'risks[name_np][]',
                '_key'  => 'name_np',
                'label' => 'Name (NP)',
                'rules' => 'trim|required|max_length[500]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function v_rules_tariff()
    {
        $portfolios = $this->portfolio_model->dropdown_children(IQB_MASTER_PORTFOLIO_PROPERTY_ID);
        return [
            [
                'field' => 'tariff[portfolio_id][]',
                '_key'  => 'portfolio_id',
                'label' => 'Portfolio',
                'rules' => 'trim|required|integer|max_length[11]|in_list['.implode(',', array_keys($portfolios)).']',
                '_type'     => 'hidden',
                '_show_label' => false,
                '_required' => true
            ],

            /**
             * Basic Premium Computation Options
             */
            [
                'field' => 'tariff[basic_apply_si_range][]',
                '_key'    => 'basic_apply_si_range',
                'label'     => 'Apply SI Range (Basic)',
                'rules'     => 'trim|required|alpha|exact_length[1]',
                '_type'     => 'dropdown',
                '_data'     => _FLAG_yes_no_dropdown(),
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[basic_si_min][]',
                '_key'    => 'basic_si_min',
                'label'     => 'SI Minimum (Basic)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[basic_si_min_rate][]',
                '_key'    => 'basic_si_min_rate',
                'label'     => 'Rate on SI Minimum (Basic)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[basic_si_overflow_rate][]',
                '_key'    => 'basic_si_overflow_rate',
                'label'     => 'Rate on SI Exceeding Min (Basic)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[basic_default_rate][]',
                '_key'    => 'basic_default_rate',
                'label'     => 'Default Rate (Basic)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],

            /**
             * Pool Premium Computation Options
             */
            [
                'field' => 'tariff[pool_apply_si_range][]',
                '_key'    => 'pool_apply_si_range',
                'label'     => 'Apply SI Range (Pool)',
                'rules'     => 'trim|required|alpha|exact_length[1]',
                '_type'     => 'dropdown',
                '_data'     => _FLAG_yes_no_dropdown(),
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[pool_si_min][]',
                '_key'    => 'pool_si_min',
                'label'     => 'SI Minimum (Pool)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[pool_si_min_rate][]',
                '_key'    => 'pool_si_min_rate',
                'label'     => 'Rate on SI Minimum (Pool)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[pool_si_overflow_rate][]',
                '_key'    => 'pool_si_overflow_rate',
                'label'     => 'Rate on SI Exceeding Min (Pool)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
            [
                'field' => 'tariff[pool_default_rate][]',
                '_key'    => 'pool_default_rate',
                'label'     => 'Default Rate (Pool)',
                'rules'     => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_type'     => 'text',
                '_show_label' => false,
                '_required' => true
            ],
        ];
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function check_duplicate_fiscal_year($fiscal_yr_id, $ids = NULL)
    {
        if( $ids )
        {
            $this->db->where_not_in('id', $ids);
        }
        // $where is array ['key' => $value]
        return $this->db->where(['fiscal_yr_id' => $fiscal_yr_id])
                        ->count_all_results($this->table_name);
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
        $list = $this->get_cache('property_index_list');
        if(!$list)
        {
            $list = $this->db->select('PRPTTRF.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PRPTTRF')
                                ->join('master_fiscal_yrs FY', 'FY.id = PRPTTRF.fiscal_yr_id')
                                ->group_by('PRPTTRF.fiscal_yr_id')
                                ->order_by('PRPTTRF.fiscal_yr_id', 'DESC')
                                ->get()->result();
            $this->write_cache($list, 'trfagr_index_list', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year_rows($fiscal_yr_id)
    {
        return $this->db->select('PRPTTRF.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np')
                        ->from($this->table_name . ' PRPTTRF')
                        ->join('master_fiscal_yrs FY', 'FY.id = PRPTTRF.fiscal_yr_id')
                        ->where('PRPTTRF.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get_fiscal_year_row($fiscal_yr_id)
    {
        return $this->db->select('PTAGR.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTAGR')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTAGR.fiscal_yr_id')
                                ->where('PTAGR.fiscal_yr_id', $fiscal_yr_id)
                                ->get()->row();
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('PTAGR.*')
                        ->from($this->table_name . ' PTAGR')
                        ->where('PTAGR.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'property_risk_cat_' . $id;
        $record = $this->get_cache($cache_var);
        if(!$record)
        {
            $record = $this->db->select('PRPTTRF.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np')
                        ->from($this->table_name . ' PRPTTRF')
                        ->join('master_fiscal_yrs FY', 'FY.id = PRPTTRF.fiscal_yr_id')
                        ->where('PRPTTRF.id', $id)
                        ->get()->row();
            $this->write_cache($record, $cache_var, CACHE_DURATION_DAY);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    public function get_all_fy($fiscal_yr_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'property_risk_cat_fy_' . $fiscal_yr_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('`id`, `code`, `name_en`, `name_np`')
                        ->from($this->table_name)
                        ->where('fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
            $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function risk_category_dropdown($fiscal_yr_id, $lang = 'both')
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all_fy($fiscal_yr_id);
        $list = [];
        foreach($records as $record)
        {
            if($lang == 'both')
            {
                $label = $record->code . ' - ' . $record->name_en . '(' . $record->name_np . ')';
            }
            elseif ($lang == 'np')
            {
                $label = $record->code . ' - ' . $record->name_np;
            }
            else
            {
                $label = $record->code . ' - ' . $record->name_en;
            }
            $list["{$record->id}"] =  $label;
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List - Risks
     */
    public function risk_dropdown($id, $lang = 'both')
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $record = $this->get($id);
        $risks = json_decode($record->risks ?? NULL);
        $list = [];
        if($risks)
        {
            foreach($risks as $single)
            {
                if($lang == 'both')
                {
                    $label = $single->name_en . '(' . $single->name_np . ')';
                }
                elseif ($lang == 'np')
                {
                    $label = $single->name_np;
                }
                else
                {
                    $label = $single->name_en;
                }
                $list["{$single->code}"] =  $label;
            }
        }

        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'property_index_list',
            'property_risk_cat_fy_*',
            'property_risk_cat_*'
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
        return FALSE;
    }
}