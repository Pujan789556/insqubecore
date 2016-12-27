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


    protected $fields = ["id", "fiscal_yr_id", "portfolio_id", "agent_commission", "direct_discount", "policy_base_no", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'fiscal_yr_id',
            'label' => 'Fiscal Year',
            'rules' => 'trim|required|integer|max_length[3]|callback__cb_settings_check_duplicate',
            '_type'     => 'dropdown',
            '_default'  => '',
            '_required' => true
        ],
        [
            'field' => 'portfolio_id[]',
            'label' => 'Portfolio',
            'rules' => 'trim|required|integer|max_length[11]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'agent_commission[]',
            'label' => 'Agent Commission(%)',
            'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'direct_discount[]',
            'label' => 'Direct Discount(%)',
            'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'policy_base_no[]',
            'label' => 'Policy Base Number',
            'rules' => 'trim|required|integer|max_length[11]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'setting_ids[]',
            'label' => 'Settings',
            'rules' => 'trim|integer|max_length[11]',
            '_type'     => 'hidden',
            '_required' => true
        ]
    ];


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

    public function get_row_list()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('portfolio_setting_list');
        if(!$list)
        {
            $list = $this->db->select('PS.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' PS')
                                ->join('master_fiscal_yrs FY', 'FY.id = PS.fiscal_yr_id')
                                ->group_by('PS.fiscal_yr_id')
                                ->get()->result();
            $this->write_cache($list, 'portfolio_setting_list', CACHE_DURATION_DAY);
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
        return $this->db->select('PS.id, PS.fiscal_yr_id, PS.portfolio_id, PS.agent_commission, PS.direct_discount, PS.policy_base_no, P.name_en as portfolio_name')
                        ->from($this->table_name . ' PS')
                        ->join('master_portfolio P', 'P.id = PS.portfolio_id')
                        ->where('PS.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $setting_ids=NULL)
    {
        if( $setting_ids )
        {
            $this->db->where_not_in('id', $setting_ids);
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
            'branch_targets_all',
            'portfolio_setting_list'
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
            'module' => 'portfolio_setting',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}