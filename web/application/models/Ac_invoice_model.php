<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_invoice_model extends MY_Model
{
    protected $table_name   = 'ac_invoices';
    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert__defaults'];
    protected $after_insert  = ['after_insert__defaults', 'clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'invoice_code', 'customer_id', 'voucher_id', 'branch_id', 'fiscal_yr_id', 'fy_quarter', 'invoice_date', 'amount', 'flag_paid', 'flag_printed', 'flag_complete', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Set validation rule
        $this->load->model('ac_invoice_detail_model');
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Add New Invoice
     *
     * The following tasks are carried during Invoice Add:
     *      a. Insert Master Record, Update Invoice Code
     *      b. Insert Invoice Details
     *
     * @param array $master_data
     * @param array $batch_data_details
     * @param int   $policy_id (if invoice generated for Endorsement)
     * @return mixed
     */
    public function add($master_data, $batch_data_details, $policy_id=NULL)
    {
        /**
         * !!! IMPORTANT
         *
         * We do not use transaction here as we may lost the invoice id autoincrement.
         * We simply use try catch block.
         *
         * If transaction fails, we will have a invoice with complete flag off.
         */

        $id = parent::insert($master_data, TRUE);

        if( $id )
        {

            /**
             * ==================== TRANSACTIONS BEGIN =========================
             */


                /**
                 * Disable DB Debugging
                 */
                $this->db->db_debug = FALSE;
                $this->db->trans_start();


                    // --------------------------------------------------------------------

                    /**
                     * Task 1: Insert Invoice Details
                     */
                    $this->ac_invoice_detail_model->batch_insert($id, $batch_data_details);

                    // --------------------------------------------------------------------

                    /**
                     * Task 2: Complete Invoice Status
                     */
                    $this->enable_invoice($id);


                    // --------------------------------------------------------------------

                    /**
                     * Task 4: Clear Cache (For this Policy)
                     */
                    if($policy_id)
                    {
                        $cache_var = 'ac_invoice_list_by_policy_'.$policy_id;
                        $this->clear_cache($cache_var);
                    }

                    // --------------------------------------------------------------------


                /**
                 * Complete transactions or Rollback
                 */
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE)
                {
                    throw new Exception("Exception [Model: Ac_invoice_model][Method: add()]: Could not save Invoice details and other details.");
                }

                /**
                 * Restore DB Debug Configuration
                 */
                $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

            /**
             * ==================== TRANSACTIONS END =========================
             */
        }
        else
        {
            throw new Exception("Exception [Model: Ac_invoice_model][Method: add()]: Could not insert record.");
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Enable Invoice Transaction [Complete Flagg - OFF]
     *
     * @param integer $id
     * @return boolean
     */
    public function enable_invoice($id)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, ['flag_complete' => IQB_FLAG_ON]);
    }

    // --------------------------------------------------------------------

    /**
     * Disable invoice Transaction [Complete Flagg - OFF]
     *
     * @param integer $id
     * @return boolean
     */
    public function disable_invoice($id)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, ['flag_complete' => IQB_FLAG_OFF]);
    }

    // --------------------------------------------------------------------

    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      2. Add Draft Invoice Code (Random Characters)
     *      3. Add Branch ID
     *      4. Add Fiscal Year ID
     *      5. Add Fiscal Year Quarter
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $this->load->library('Token');
        $fy_record  = $this->fiscal_year_model->get_fiscal_year($data['invoice_date']);
        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($data['invoice_date']);

        // Invoice Code
        $data['invoice_code']      = strtoupper($this->token->generate(10));

        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();

        // Fiscal Year
        $data['fiscal_yr_id'] = $fy_record->id;

        // Fiscal Year Quarter
        $data['fy_quarter'] = $fy_quarter->quarter;

        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * After Insert Trigger
     *
     * Tasks that are to be performed after policy is created are
     *      1. Generate and Update Invoice Code
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
                        [invoice_code] => 6
                        [branch_id] => 6
                        [fiscal_yr_id] => x
                        ...
                    )
                [method] => insert
            )
        */
        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $params     = [$id, $this->dx_auth->get_user_id()];
            $sql        = "SELECT `f_generate_invoice_number`(?, ?) AS invoice_code";
            return mysqli_store_procedure('select', $sql, $params);
        }
        return FALSE;
    }

    // --------------------------------------------------------------------


    /**
     * Update Invoice Flags
     *
     *  Flags: flag_paid|flag_printed
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
     * @param integer $voucher_id
     * @return integer
     */
    public function invoice_exists($voucher_id)
    {
        return $this->check_duplicate(['voucher_id' => $voucher_id, 'flag_complete' => IQB_FLAG_ON]);
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
        $this->_row_select();

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['I.id <=' => $next_id]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
                $this->db->where(['I.branch_id' =>  $branch_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['I.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $fy_quarter = $params['fy_quarter'] ?? NULL;
            if( $fy_quarter )
            {
                $this->db->where(['I.fy_quarter' =>  $fy_quarter]);
            }

            // Start Dates
            $start_date = $params['start_date'] ?? NULL;
            if( $start_date )
            {
                $this->db->where(['I.invoice_date >=' =>  $start_date]);
            }

            // End Dates
            $end_date = $params['end_date'] ?? NULL;
            if( $end_date )
            {
                $this->db->where(['I.invoice_date <=' =>  $end_date]);
            }

            // Invoice Code
            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('I.invoice_code', $keywords, 'after');
            }
        }

        return $this->db
                    ->order_by('I.id', 'DESC')
                    ->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                        // Invoice Table
                        'I.*, ' .

                        // Receipt Data
                        'RCPT.id as receipt_id, RCPT.adjustment_amount, RCPT.received_in, RCPT.received_in_date, RCPT.flag_printed as receipt_flag_printed, ' .

                        // Branch Table
                        'B.name_en AS branch_name_en, B.name_np AS branch_name_np, ' .

                        // Fiscal Year Table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

                        // Policy Voucher Relation Data
                        'REL.policy_id, REL.ref, REL.ref_id'
                    )
                ->from($this->table_name . ' AS I')
                ->join('ac_receipts RCPT', 'I.id = RCPT.invoice_id', 'left')
                ->join('master_branches B', 'B.id = I.branch_id')
                ->join('master_fiscal_yrs FY', 'FY.id = I.fiscal_yr_id')
                ->join('ac_vouchers V', 'V.id = I.voucher_id')
                ->join('rel_policy__voucher REL', 'REL.voucher_id = I.voucher_id');

        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('I');
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'ac_invoice_list_by_policy_'.$policy_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows_by_policy($policy_id);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

        /**
         * Get Rows from Database
         *
         * @param int $policy_id
         * @return array
         */
        private function _rows_by_policy($policy_id)
        {
            // Common Row Select
            $this->_row_select();

            // Policy Related JOIN
            return $this->db->where('REL.policy_id', $policy_id)
                            ->where('I.flag_complete', IQB_FLAG_ON)
                            ->where('V.flag_complete', IQB_FLAG_ON)
                            ->order_by('I.id', 'DESC')
                            ->get()
                            ->result();
        }



    // --------------------------------------------------------------------

    public function get($id, $flag_complete=NULL)
    {
        // Common Row Select
        $this->_row_select();

        // Policy, Customer Related JOIN
        $this->db->select(
                            // Branch Contact
                            'B.contacts as branch_contact, ' .

                            // Policy Installment ID, Endorsement ID
                            'PTI.id as policy_installment_id, PTI.endorsement_id, ' .

                            // Policy Code
                            'POLICY.code AS policy_code, ' .

                            // Customer Details
                            'CST.full_name AS customer_full_name, CST.contact as customer_contact'
                        )
                    ->join('dt_policies POLICY', 'POLICY.id = REL.policy_id')
                    ->join('dt_policy_installments PTI', "REL.ref = '" . IQB_REL_POLICY_VOUCHER_REF_PI . "' AND REL.ref_id = PTI.id")
                    ->join('dt_customers CST', 'CST.id = I.customer_id');

        /**
         * Complete/Active Invoice?
         */
        if($flag_complete !== NULL )
        {
            $this->db->where('I.flag_complete', (int)$flag_complete);
        }

        return $this->db->where('I.id', $id)
                        ->get()->row();
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
                'ac_invoice_list_by_policy_*'
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
}