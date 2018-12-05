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

    protected $fields = ['id', 'receipt_code', 'invoice_id', 'customer_id', 'adjustment_amount', 'amount', 'received_in', 'received_in_date', 'received_in_ref', 'flag_printed', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
     * Update Receipt Flags
     *
     *  Flags: flag_printed
     *
     * @param integer $id
     * @return boolean
     */
    public function update_flag($id, $flag_name, $flag_value)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, [$flag_name => $flag_value]);
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
        //                 'B.name_en AS branch_name_en, B.name_np AS branch_name_np, ' .

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
            // return $this->db->select('ENDRSMNT.id AS policy_installment_id, ENDRSMNT.policy_id')
            //             ->join('ac_vouchers V', 'V.id = I.voucher_id')
            //             ->join('rel_policy_installment_voucher RELENDRSMNTVHR', 'RELENDRSMNTVHR.voucher_id = I.voucher_id')
            //             ->join('dt_endorsements ENDRSMNT', 'RELENDRSMNTVHR.policy_installment_id = ENDRSMNT.id')
            //             ->where('ENDRSMNT.policy_id', $policy_id)
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

        //                     // Endorsement ID, Policy ID
        //                     'ENDRSMNT.id AS policy_installment_id, ENDRSMNT.policy_id, ' .

        //                     // Policy Code
        //                     'POLICY.code AS policy_code, ' .

        //                     // Customer Details
        //                     'CST.full_name_en AS customer_full_name_en, CST.contact as customer_contact'
        //                 )
        //             ->join('ac_vouchers V', 'V.id = I.voucher_id')
        //             ->join('rel_policy_installment_voucher RELENDRSMNTVHR', 'RELENDRSMNTVHR.voucher_id = I.voucher_id')
        //             ->join('dt_endorsements ENDRSMNT', 'RELENDRSMNTVHR.policy_installment_id = ENDRSMNT.id')
        //             ->join('dt_policies POLICY', 'POLICY.id = ENDRSMNT.policy_id')
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