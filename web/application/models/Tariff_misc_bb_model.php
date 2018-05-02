<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tariff_misc_bb_model extends MY_Model
{
    protected $table_name = 'master_tariff_misc_bb';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'portfolio_id', 'fiscal_yr_id', 'tariff', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

    public function validation_rules($action, $formatted=false)
    {
        if($action == 'add')
        {
            $defaults = [
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]|callback__cb_tariff_misc_bb_check_duplicate',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_default'  => '',
                    '_required' => true
                ],
                [
                    'field' => 'active',
                    'label' => 'Activate Tariff',
                    'rules' => 'trim|integer|in_list[1]',
                    '_type' => 'switch',
                    '_checkbox_value' => '1'
                ]
            ];
        }
        else
        {
            /**
             * No Fiscal Year on Edit
             */
            $defaults = [
                [
                    'field' => 'active',
                    'label' => 'Activate Tariff',
                    'rules' => 'trim|integer|in_list[1]',
                    '_type' => 'switch',
                    '_checkbox_value' => '1'
                ]
            ];
        }



        $rules = [

            /**
             * Default Rules
             */
            'defaults' => $defaults,

            /**
             * JSON : Tarrif
             * --------------
             *
             * Structure:
             *  [{
             *      breed: xyz,
             *      rate: 5.5
             *  },{
             *      ...
             *  }]
             */
            'tariff' => [
                [
                    'field' => 'tariff[basic]',
                    '_key'  => 'basic',
                    'label' => 'Basic Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[cip]',
                    '_key'  => 'cip',
                    'label' => 'Cash in Premises Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[cit]',
                    '_key'  => 'cit',
                    'label' => 'Cash in Transit Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ]
        ];


        if($formatted)
        {
            $formatted_rules = [];
            foreach($rules as $section=>$section_rules)
            {
                $formatted_rules = array_merge($formatted_rules, $section_rules);
            }

            return $formatted_rules;
        }

        return $rules;
    }

    // ----------------------------------------------------------------

    public function duplicate_validation_rules()
    {
        return [
            [
                'field' => 'fiscal_yr_id',
                'label' => 'Fiscal Year',
                'rules' => 'trim|required|integer|max_length[3]|callback__cb_tariff_misc_bb_check_duplicate',
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
        $list = $this->get_cache('trfmisc_bb_index_list');
        if(!$list)
        {
            $list = $this->db->select('PTMISCBB.id, PTMISCBB.fiscal_yr_id, PTMISCBB.active, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTMISCBB')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTMISCBB.fiscal_yr_id')
                                ->group_by('PTMISCBB.fiscal_yr_id')
                                ->order_by('PTMISCBB.fiscal_yr_id', 'DESC')
                                ->get()->result();
            $this->write_cache($list, 'trfmisc_bb_index_list', CACHE_DURATION_DAY);
        }
        return $list;
    }


    // ----------------------------------------------------------------

    public function get($id)
    {
        return $this->db->select('PTMISCBB.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np, PRT.name_en as portfolio_name_en')
                        ->from($this->table_name . ' PTMISCBB')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTMISCBB.fiscal_yr_id')
                        ->join('master_portfolio PRT', 'PRT.id = PTMISCBB.portfolio_id')
                        ->where('PTMISCBB.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Record for Given Fiscal Year/Portfolio
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @return object
     */
    public function get_by_fy_portfolio($fiscal_yr_id, $portfolio_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'trfmisc_bb_bfp_' . $fiscal_yr_id . '_' . $portfolio_id;
        $row        = $this->get_cache($cache_name);

        if(!$row)
        {
            $where = [
                'portfolio_id'  => $portfolio_id,
                'fiscal_yr_id'  => $fiscal_yr_id,
            ];
            $row = $this->db->select('PTMISCBB.*')
                        ->from($this->table_name . ' PTMISCBB')
                        ->where($where)
                        ->get()->row();

            $this->write_cache($row, $cache_name, CACHE_DURATION_DAY);
        }
        return $row;
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

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'trfmisc_bb_index_list',
            'trfmisc_bb_bfp_*'
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