<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_receipt_model extends MY_Model
{
    protected $table_name   = 'ac_receipts';
    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert__defaults'];
    protected $after_insert  = ['after_insert__defaults', 'clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'receipt_code', 'invoice_id', 'customer_id', 'adjustment_amount', 'amount', 'received_in', 'received_in_date', 'flag_printed', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Helper
        $this->load->helper('account');

        // Validation Rules
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
        // $this->validation_rules = [

        //     /**
        //      * Invoice Basic Validation Rules
        //      */
        //     'basic' => $this->_v_rules_basic(),

        //     /**
        //      * Invoice Details Validation Rules
        //      */
        //     'details' => $this->_v_rules_details()
        // ];
    }

        private function _v_rules_basic()
        {
            // /**
            //  * @TODO - Does this user have "back-date" allowed?
            //  */
            // $v_rules = [
            //     // Can not go back than current quarter or future
            //     [
            //         'field' => 'invoice_date',
            //         'label' => 'Invoice Date',
            //         'rules' => 'trim|required|valid_date|callback__valid_invoice_date',
            //         '_type'             => 'date',
            //         '_default'          => date('Y-m-d'),
            //         '_extra_attributes' => 'data-provide="datepicker-inline"',
            //         '_required' => true
            //     ],
            //     [
            //         'field' => 'narration',
            //         'label' => 'Narration',
            //         'rules' => 'trim|max_length[300]',
            //         'rows'  => '3',
            //         '_type'     => 'textarea',
            //         '_required' => false
            //     ]
            // ];

            // return $v_rules;
        }

        private function _v_rules_details()
        {
            // $dropdown_party_types = ac_party_types_dropdown(false);
            // $v_rules = [

            //     /**
            //      * Credit Row
            //      */
            //     'credits' => [
            //         [
            //             'field' => 'account_id[cr][]',
            //             'label' => 'Account',
            //             'rules' => 'trim|required|integer|max_length[11]',
            //             '_field'    => 'account_id',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT,
            //             '_extra_attributes' => 'style="width:100%; display:block"',
            //             '_required' => true
            //         ],
            //         [
            //             'field' => 'party_type[cr][]',
            //             'label' => 'Party Type',
            //             'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
            //             '_field'    => 'party_type',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
            //             '_extra_attributes' => 'data-field="party_type" onchange="__reset_party(this)"',
            //             '_required' => false
            //         ],
            //         [
            //             'field' => 'party_id[cr][]',
            //             'label' => 'Party',
            //             'rules' => 'trim|integer|max_length[11]',
            //             '_field'    => 'party_id',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT,
            //             '_extra_attributes' => 'style="width:100%; display:block"',
            //             '_required' => false
            //         ],
            //         [
            //             'field' => 'amount[cr][]',
            //             'label' => 'Credit Amount',
            //             'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
            //             '_field'        => 'amount',
            //             '_extra_attributes' => 'data-group="cr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
            //             '_show_label' => false,
            //             '_type'         => 'text',
            //             '_show_label'   => false,
            //             '_required'     => true
            //         ],
            //     ],

            //     /**
            //      * Debit Row
            //      */
            //     'debits' => [
            //         [
            //             'field' => 'account_id[dr][]',
            //             'label' => 'Account',
            //             'rules' => 'trim|required|integer|max_length[11]',
            //             '_field'    => 'account_id',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT,
            //             '_required' => true
            //         ],
            //         [
            //             'field' => 'party_type[dr][]',
            //             'label' => 'Party Type',
            //             'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
            //             '_field'    => 'party_type',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
            //             '_extra_attributes' => 'data-field="party_type" onchange="__reset_party(this)"',
            //             '_required' => false
            //         ],
            //         [
            //             'field' => 'party_id[dr][]',
            //             'label' => 'Party',
            //             'rules' => 'trim|integer|max_length[11]',
            //             '_field'    => 'party_id',
            //             '_show_label' => false,
            //             '_type'     => 'dropdown',
            //             '_data'     => IQB_BLANK_SELECT,
            //             '_required' => false
            //         ],
            //         [
            //             'field' => 'amount[dr][]',
            //             'label' => 'Debit Amount',
            //             'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|callback__valid_voucher_amount', // compute dr = cr
            //             '_field'        => 'amount',
            //             '_extra_attributes' => 'data-group="dr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
            //             '_show_label' => false,
            //             '_type'         => 'text',
            //             '_show_label'   => false,
            //             '_required'     => true
            //         ],
            //     ]
            // ];

            // return $v_rules;
        }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules Formatted
     *
     * @return array
     */
    public function validation_rules_formatted()
    {
        // $v_rules         = $this->_v_rules_basic();
        // $sectioned_rules = $this->_v_rules_details();

        // foreach($sectioned_rules as $section => $rules)
        // {
        //     $v_rules = array_merge($v_rules, $rules);
        // }

        // return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Add New Receipt
     *
     * The following tasks are carried during Receipt Add:
     *      a. Insert Master Record,
     *      b. Update Receipt Code
     *
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        return parent::insert($data, TRUE);
    }

    // --------------------------------------------------------------------


    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      2. Add Draft Receipt Code (Random Characters)
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $this->load->library('Token');

        // Receipt Code
        $data['receipt_code']      = strtoupper($this->token->generate(10));

        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * After Insert Trigger
     *
     * Tasks that are to be performed after policy is created are
     *      1. Generate and Update Receipt Code
     *
     * @param array $arr_record
     * @return array
     */
    public function after_insert__defaults($arr_record)
    {
        /**
         * Data Structure
         *
            Array
            (
                [id] => 11
                [fields] => Array
                    (
                        [receipt_code] => 6
                        ...
                    )
                [method] => insert
            )
        */
        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $params     = [$id, $this->dx_auth->get_user_id()];
            $sql        = "SELECT `f_generate_receipt_number`(?, ?) AS receipt_code";
            return mysqli_store_procedure('select', $sql, $params);
        }
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Check if Invoice Exists for Given Voucher ID
     *
     * @param integer $invoice_id
     * @return integer
     */
    public function receipt_exists($invoice_id)
    {
        return $this->check_duplicate(['invoice_id' => $invoice_id]);
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

        return $this->db->where('I.id', $id)
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
        // $this->_row_select();

        // if(!empty($params))
        // {
        //     $next_id = $params['next_id'] ?? NULL;
        //     if( $next_id )
        //     {
        //         $this->db->where(['I.id <=' => $next_id]);
        //     }

        //     $branch_id = $params['branch_id'] ?? NULL;
        //     if( $branch_id )
        //     {
        //         $this->db->where(['I.branch_id' =>  $branch_id]);
        //     }

        //     $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
        //     if( $fiscal_yr_id )
        //     {
        //         $this->db->where(['I.fiscal_yr_id' =>  $fiscal_yr_id]);
        //     }

        //     $fy_quarter = $params['fy_quarter'] ?? NULL;
        //     if( $fy_quarter )
        //     {
        //         $this->db->where(['I.fy_quarter' =>  $fy_quarter]);
        //     }

        //     // Start Dates
        //     $start_date = $params['start_date'] ?? NULL;
        //     if( $start_date )
        //     {
        //         $this->db->where(['I.invoice_date >=' =>  $start_date]);
        //     }

        //     // End Dates
        //     $end_date = $params['end_date'] ?? NULL;
        //     if( $end_date )
        //     {
        //         $this->db->where(['I.invoice_date <=' =>  $end_date]);
        //     }

        //     // Invoice Code
        //     $keywords = $params['keywords'] ?? '';
        //     if( $keywords )
        //     {
        //         $this->db->like('I.invoice_code', $keywords, 'after');
        //     }
        // }

        // return $this->db
        //             ->order_by('I.id', 'DESC')
        //             ->limit($this->settings->per_page+1)
        //             ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        // $this->db->select(
        //                 // Invoice Table
        //                 'I.*, ' .

        //                 // Branch Table
        //                 'B.name AS branch_name, ' .

        //                 // Fiscal Year Table
        //                 'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np'
        //             )
        //         ->from($this->table_name . ' AS I')
        //         ->join('master_branches B', 'B.id = I.branch_id')
        //         ->join('master_fiscal_yrs FY', 'FY.id = I.fiscal_yr_id');

        // /**
        //  * Apply User Scope
        //  */
        // $this->dx_auth->apply_user_scope('I');
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        // /**
        //  * Get Cached Result, If no, cache the query result
        //  */
        // $cache_var = 'ac_receipt_list_by_policy_'.$policy_id;
        // $rows = $this->get_cache($cache_var);
        // if(!$rows)
        // {
        //     $rows = $this->_rows_by_policy($policy_id);

        //     if($rows)
        //     {
        //         $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
        //     }
        // }
        // return $rows;
    }

        /**
         * Get Rows from Database
         *
         * @param int $policy_id
         * @return array
         */
        private function _rows_by_policy($policy_id)
        {
            // // Common Row Select
            // $this->_row_select();

            // // Policy Related JOIN
            // return $this->db->select('PTXN.id AS policy_txn_id, PTXN.policy_id')
            //             ->join('ac_vouchers V', 'V.id = I.voucher_id')
            //             ->join('rel_policy_txn__voucher RELPTXNVHR', 'RELPTXNVHR.voucher_id = I.voucher_id')
            //             ->join('dt_policy_txn PTXN', 'RELPTXNVHR.policy_txn_id = PTXN.id')
            //             ->where('PTXN.policy_id', $policy_id)
            //             ->where('I.flag_complete', IQB_FLAG_ON)
            //             ->where('V.flag_complete', IQB_FLAG_ON)
            //             ->order_by('I.id', 'DESC')
            //             ->get()
            //             ->result();
        }



    // --------------------------------------------------------------------

    public function get($id, $flag_complete=NULL)
    {
        // // Common Row Select
        // $this->_row_select();

        // // Policy, Customer Related JOIN
        // $this->db->select(
        //                     // Branch Contact
        //                     'B.contacts as branch_contact, ' .

        //                     // Policy Transaction ID, Policy ID
        //                     'PTXN.id AS policy_txn_id, PTXN.policy_id, ' .

        //                     // Policy Code
        //                     'POLICY.code AS policy_code, ' .

        //                     // Customer Details
        //                     'CST.full_name AS customer_full_name, CST.contact as customer_contact'
        //                 )
        //             ->join('ac_vouchers V', 'V.id = I.voucher_id')
        //             ->join('rel_policy_txn__voucher RELPTXNVHR', 'RELPTXNVHR.voucher_id = I.voucher_id')
        //             ->join('dt_policy_txn PTXN', 'RELPTXNVHR.policy_txn_id = PTXN.id')
        //             ->join('dt_policies POLICY', 'POLICY.id = PTXN.policy_id')
        //             ->join('dt_customers CST', 'CST.id = I.customer_id');

        // /**
        //  * Complete/Active Invoice?
        //  */
        // if($flag_complete !== NULL )
        // {
        //     $this->db->where('I.flag_complete', (int)$flag_complete);
        // }

        // return $this->db->where('I.id', $id)
        //                 ->get()->row();
    }
	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache( $data=null )
    {
        /**
         * If no data supplied, delete all caches
         */
        if( !$data )
        {
            $cache_names = [
                'ac_receipt_list_by_policy_*'
            ];
        }
        else
        {
            /**
             * If data supplied, we only delete the supplied
             * caches
             */
            $cache_names = is_array($data) ? $data : [$data];
        }

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
            'module'    => 'ac_receipt',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}