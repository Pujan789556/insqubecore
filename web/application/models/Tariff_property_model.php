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

    protected $fields = ['id', 'code', 'name_en', 'name_np', 'risks', 'tariff', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('property_risk_cat_all');
        if(!$list)
        {
            $list = $this->db->select('`id`, `code`, `name_en`, `name_np`')
                        ->from($this->table_name)
                        ->get()->result();
            $this->write_cache($list, 'property_risk_cat_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] =  $record->code . ' - ' . $record->name_en . '(' . $record->name_np . ')';
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
            'property_risk_cat_all'
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