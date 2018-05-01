<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_target_model extends MY_Model
{
    protected $table_name = 'master_branch_targets';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ["id", "fiscal_yr_id", "branch_id", "target_total", "target_details", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'fiscal_yr_id',
            'label' => 'Fiscal Year',
            'rules' => 'trim|required|integer|max_length[3]|callback__cb_targets_check_duplicate',
            '_type'     => 'dropdown',
            '_default'  => '',
            '_required' => true
        ],
        [
            'field' => 'branch_id[]',
            'label' => 'Branch',
            'rules' => 'trim|required|integer|max_length[11]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'target_total[]',
            'label' => 'Total Target',
            'rules' => 'trim|required|prep_decimal|decimal|max_length[14]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'target_ids[]',
            'label' => 'Target IDs',
            'rules' => 'trim|integer|max_length[11]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'target_ids[]',
            'label' => 'Targets',
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
        $list = $this->get_cache('branch_targets_list');
        if(!$list)
        {
            $list = $this->db->select('BT.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' BT')
                                ->join('master_fiscal_yrs FY', 'FY.id = BT.fiscal_yr_id')
                                ->group_by('BT.fiscal_yr_id')
                                ->get()->result();
            $this->write_cache($list, 'branch_targets_list', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_row_single($fiscal_yr_id)
    {
        return $this->db->select('BT.fiscal_yr_id, FY.code_en, FY.code_np')
                                ->from($this->table_name . ' BT')
                                ->join('master_fiscal_yrs FY', 'FY.id = BT.fiscal_yr_id')
                                ->where('BT.fiscal_yr_id', $fiscal_yr_id)
                                ->get()->row();
    }

    // ----------------------------------------------------------------

    public function get_list_by_fiscal_year($fiscal_yr_id)
    {
        return $this->db->select('BT.id, BT.fiscal_yr_id, BT.branch_id, BT.target_total, BT.target_details, B.name as branch_name')
                        ->from($this->table_name . ' BT')
                        ->join('master_branches B', 'B.id = BT.branch_id')
                        ->where('BT.fiscal_yr_id', $fiscal_yr_id)
                        ->get()->result();
    }
    // ----------------------------------------------------------------

    public function check_duplicate($where, $target_ids=NULL)
    {
        if( $target_ids )
        {
            $this->db->where_not_in('id', $target_ids);
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
            'branch_targets_list'
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