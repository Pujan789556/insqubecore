<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portfolio_model extends MY_Model
{
    protected $table_name = 'master_portfolio';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert_update__defaults'];
    protected $before_update = ['before_insert_update__defaults'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'parent_id', 'code', 'name_en', 'name_np', 'file_toc', 'risk_ids', 'bs_ri_code', 'account_id_dpi', 'account_id_tpc', 'account_id_fpc', 'account_id_rtc', 'account_id_rfc', 'account_id_fpi', 'account_id_fce', 'account_id_pw', 'account_id_pe', 'account_id_ce', 'account_id_cr', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 1000; // Prevent first 1000 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Account Model
        $this->load->model('ac_account_model');

        // Validation Rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $parent_dropdown = $this->dropdown_parent();

        $account_id_dpi_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_DIRECT_PREMIUM_INCOME);
        $account_id_tpc_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_PREMIUM_CEDED);
        $account_id_fpc_dropdown = $account_id_tpc_dropdown;

        $account_id_rtc_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_RCI);
        $account_id_rfc_dropdown = $account_id_rtc_dropdown;

        $account_id_fpi_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_REINSURANCE_PREMIUM_INCOME);
        $account_id_fce_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_RCE);

        $account_id_pw_dropdown     = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_RECEIVABLE_FROM_REINSURER);
        $account_id_cr_dropdown    = $account_id_pw_dropdown;

        $account_id_pe_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_PAYABLE_TO_REINSURER);
        $account_id_ce_dropdown = $this->ac_account_model->dropdown(IQB_AC_ACCOUNT_GROUP_ID_CLAIM_EXPENSE);


        $this->load->model('risk_model');
        $risk_dropdown = $this->risk_model->dropdown();


        $this->validation_rules = [
            'basic' => [
                [
                    'field' => 'parent_id',
                    'label' => 'Parent Portfolio',
                    'rules' => 'trim|integer|max_length[8]|in_list[' . implode(',', array_keys($parent_dropdown)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $parent_dropdown,
                    '_required' => true
                ],
                [
                    'field' => 'name_en',
                    'label' => 'Portfolio Name(EN)',
                    'rules' => 'trim|required|max_length[100]|ucfirst',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'name_np',
                    'label' => 'Portfolio Name (NP)',
                    'rules' => 'trim|max_length[100]',
                    '_type'     => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'code',
                    'label' => 'Portfolio Code',
                    'rules' => 'trim|required|alpha|max_length[15]|strtoupper|callback_check_duplicate',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'bs_ri_code',
                    'label' => 'Beema Samiti Business Code',
                    'rules' => 'trim|required|integer|max_length[4]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            /**
             * Risk Validation Rules
             */
            'risks' => [
                [
                    'field' => 'risks[]',
                    'label' => 'Portfolio Risks',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'checkbox-group',
                    '_data'     => $risk_dropdown,
                    '_list_inline' => false,
                    '_checkbox_value' => [],
                    '_required' => true
                ]
            ],

            /**
             * Account IDs for this Portfolio - Validation Rules
             */
            'accounts' => [

                /**
                 * Account ID - Direct Premium Income
                 */
                [
                    'field' => 'account_id_dpi',
                    'label' => 'Account Direct Premium Income',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_dpi_dropdown)) . ']',
                    '_id'       => 'account_id_dpi',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_dpi_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - Treaty Premium Ceded
                 */
                [
                    'field' => 'account_id_tpc',
                    'label' => 'Account Treaty Premium Ceded',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_tpc_dropdown)) . ']',
                    '_id'       => 'account_id_tpc',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_tpc_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - FAC Premium Ceded
                 */
                [
                    'field' => 'account_id_fpc',
                    'label' => 'Account FAC Premium Ceded',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_fpc_dropdown)) . ']',
                    '_id'       => 'account_id_fpc',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_fpc_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - RI Treaty Commission
                 */
                [
                    'field' => 'account_id_rtc',
                    'label' => 'Account RI Treaty Commission',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_rtc_dropdown)) . ']',
                    '_id'       => 'account_id_rtc',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_rtc_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - RI FAC Commission
                 */
                [
                    'field' => 'account_id_rfc',
                    'label' => 'Account RI FAC Commission',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_rfc_dropdown)) . ']',
                    '_id'       => 'account_id_rfc',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_rfc_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - FAC Premium Accepted
                 */
                [
                    'field' => 'account_id_fpi',
                    'label' => 'Account FAC Premium Accepted',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_fpi_dropdown)) . ']',
                    '_id'       => 'account_id_fpi',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_fpi_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - FAC Commission Expense
                 */
                [
                    'field' => 'account_id_fce',
                    'label' => 'Account FAC Commission Expense',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_fce_dropdown)) . ']',
                    '_id'       => 'account_id_fce',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_fce_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - Portfolio Withdrawl
                 */
                [
                    'field' => 'account_id_pw',
                    'label' => 'Account Portfolio Withdrawl',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_pw_dropdown)) . ']',
                    '_id'       => 'account_id_pw',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_pw_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - Portfolio Entry
                 */
                [
                    'field' => 'account_id_pe',
                    'label' => 'Account Portfolio Entry',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_pe_dropdown)) . ']',
                    '_id'       => 'account_id_pe',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_pe_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - Claim Expense
                 */
                [
                    'field' => 'account_id_ce',
                    'label' => 'Account Claim Expense',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_ce_dropdown)) . ']',
                    '_id'       => 'account_id_ce',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_ce_dropdown,
                    '_required' => false
                ],

                /**
                 * Account ID - Claim Receivable
                 */
                [
                    'field' => 'account_id_cr',
                    'label' => 'Account Claim Receivable',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($account_id_cr_dropdown)) . ']',
                    '_id'       => 'account_id_cr',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $account_id_cr_dropdown,
                    '_required' => false
                ]
            ]

        ];
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pf_all');
        if(!$list)
        {
            // $list = parent::find_all();

            $list = $this->db->select('L1.*, L2.name_en as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->get()->result();
            $this->write_cache($list, 'pf_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function find($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'portfolio_s_'.$id;
        $record = $this->get_cache($cache_var);
        if(!$record)
        {
            $record = $this->db->select('L1.*, L2.name_en as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->where('L1.id', $id)
                             ->get()->row();

            if($record)
            {
                $this->write_cache($record, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $record;
    }

    // ----------------------------------------------------------------

    /**
     * Save Portfolio Specific Accounts
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function save_accounts($id, $data)
    {
        $result = $this->db->where('id', $id)
                        ->set($data)
                        ->update($this->table_name);

        if( $result)
        {
            $this->clear_cache();
        }

        return $result;
    }

    // ----------------------------------------------------------------

    /**
     * Trigger - Before Insert/Update
     *
     * The following tasks are carried out before inserting/updating the record:
     *  1. Capitalize Code
     *  2. Nullify Parent ID if empty supplied
     *
     * @param array $data
     * @return array
     */
    public function before_insert_update__defaults($data)
    {
        $code_cols = array('code');
        foreach($code_cols as $col)
        {
            if( isset($data[$col]) && !empty($data[$col]) )
            {
                $data[$col] = strtoupper($data[$col]);
            }
        }

        if( !$data['parent_id'])
        {
            $data['parent_id'] = NULL;
        }
        return $data;
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

    public function dropdown_parent($field='id')
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pf_parent_all');
        if(!$list)
        {
            $records = $this->db->select('id, code, name_en')
                             ->from($this->table_name)
                             ->where('parent_id', NULL)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->{$field};
                $list["{$column}"] = $record->name_en;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'pf_parent_all', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_children_tree()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pf_dropdown_children_tree');
        if(!$list)
        {
            $records = $this->db->select('N.id, N.parent_id, N.code, N.name_en, P.name_en AS parent_name_en')
                             ->from($this->table_name . ' AS N')
                             ->join($this->table_name . ' AS P', 'P.id = N.parent_id', 'left')
                             ->where('N.parent_id !=', NULL)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $parent_name = $record->parent_name_en;
                $list["{$parent_name}"]["{$record->id}"] = $record->name_en;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'pf_dropdown_children_tree', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Risks Dropdown for this portfolio
     *
     * @param integer $id
     * @return array
     */
    public function dropdown_risks($id)
    {
        $dropdown   = [];
        $record     = $this->find($id);
        if($record->risk_ids)
        {
            $this->load->model('risk_model');
            $risk_ids = explode(',', $record->risk_ids);
            $dropdown = $this->risk_model->dropdown_selected_ids($risk_ids);
        }
        return $dropdown;
    }

    // ----------------------------------------------------------------

    /**
     * Get all risks records for this portfolio
     *
     * @param integer $id
     * @return array
     */
    public function portfolio_risks($id)
    {
        $list   = [];
        $record     = $this->find($id);
        if($record->risk_ids)
        {
            $this->load->model('risk_model');
            $risk_ids = explode(',', $record->risk_ids);
            $list = $this->risk_model->get_selected_ids($risk_ids);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_code($id)
    {
        $record = $this->find($id);
        return $record ? $record->code : '';
    }

    // ----------------------------------------------------------------

    public function get_children($parent_id=NULL)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        if(!$parent_id)
        {
            $cache_var = 'pf_children_all';
        }
        else
        {
            $cache_var = 'pf_children_' . $parent_id;
        }

        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $this->db->select('L1.*, L2.code as parent_code, L2.name_en as parent_name_en, L2.name_np as parent_name_np')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left');


            // $this->db->select('id, parent_id, code, name_en, name_np')
            //                 ->from($this->table_name);

            if($parent_id)
            {
                $this->db->where('L1.parent_id', $parent_id);
            }
            else
            {
                $this->db->where('L1.parent_id !=', NULL);
            }
            $list = $this->db->get()->result();

            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_children($parent_id=NULL, $field='id')
    {
        $records = $this->get_children($parent_id);

        $list = [];
        foreach($records as $record)
        {
            $column = $record->{$field};
            $list["{$column}"] = $record->parent_code . ' - ' . $record->name_en;
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
            'pf_all',
            'pf_dropdown_children_tree',
            'pf_parent_all',
            'pf_children_*',
            'portfolio_s_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Check if this record has children
     */
    public function has_children($id)
    {
        return $this->db->where('parent_id', $id)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Check if we have child Constraint
        if( $this->has_children($id))
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
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}