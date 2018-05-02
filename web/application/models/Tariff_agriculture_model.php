<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class tariff_agriculture_model extends MY_Model
{
    protected $table_name = 'master_tariff_agriculture';

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
             * Default Configurations
             */
            'defaults' => [
                [
                    'field' => 'active',
                    'label' => 'Activate Tariff',
                    'rules' => 'trim|integer|in_list[1]',
                    '_type' => 'switch',
                    '_checkbox_value' => '1'
                ]
            ],

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
                    'field' => 'tariff[code][]',
                    '_key'  => 'code',
                    'label' => 'Type/Category Code',
                    'rules' => 'trim|required|alpha|strtoupper|max_length[20]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[name][]',
                    '_key'  => 'name',
                    'label' => 'Type/Category Name',
                    'rules' => 'trim|required|htmlspecialchars|max_length[100]',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'tariff[rate][]',
                    '_key'  => 'rate',
                    'label' => 'Premium Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'     => 'text',
                    '_required' => true
                ],
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
                'rules' => 'trim|required|integer|max_length[3]|callback__cb_tariff_agriculture_check_duplicate',
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
        $list = $this->get_cache('trfagr_index_list');
        if(!$list)
        {
            $list = $this->db->select('PTAGR.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PTAGR')
                                ->join('master_fiscal_yrs FY', 'FY.id = PTAGR.fiscal_yr_id')
                                ->group_by('PTAGR.fiscal_yr_id')
                                ->order_by('PTAGR.fiscal_yr_id', 'DESC')
                                ->get()->result();
            $this->write_cache($list, 'trfagr_index_list', CACHE_DURATION_DAY);
        }
        return $list;
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

    public function get_list_by_fiscal_year_rows($fiscal_yr_id)
    {
        return $this->db->select('PTAGR.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np, PRT.name_en as portfolio_name_en')
                        ->from($this->table_name . ' PTAGR')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTAGR.fiscal_yr_id')
                        ->join('master_portfolio PRT', 'PRT.id = PTAGR.portfolio_id')
                        ->where('PTAGR.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function get($id)
    {
        return $this->db->select('PTAGR.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np, PRT.name_en as portfolio_name_en')
                        ->from($this->table_name . ' PTAGR')
                        ->join('master_fiscal_yrs FY', 'FY.id = PTAGR.fiscal_yr_id')
                        ->join('master_portfolio PRT', 'PRT.id = PTAGR.portfolio_id')
                        ->where('PTAGR.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Type Dropdown for Given Fiscal Year/Portfolio
     *
     * @param int $fiscal_yr_id
     * @param int $portfolio_id
     * @return array
     */
    public function type_dropdown($fiscal_yr_id, $portfolio_id)
    {
        $dropdown   = [];
        $row        = $this->get_by_fy_portfolio($fiscal_yr_id, $portfolio_id);
        $tariff     = json_decode($row->tariff ?? '[]');
        foreach($tariff as $st)
        {
            $dropdown[$st->code] = $st->name;
        }
        return $dropdown;
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
        $cache_name = 'trfagr_bfp_' . $fiscal_yr_id . '_' . $portfolio_id;
        $row        = $this->get_cache($cache_name);

        if(!$row)
        {
            $where = [
                'portfolio_id'  => $portfolio_id,
                'fiscal_yr_id'  => $fiscal_yr_id,
            ];
            $row = $this->db->select('PTAGR.*')
                        ->from($this->table_name . ' PTAGR')
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
            'trfagr_index_list',
            'trfagr_bfp_*'
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