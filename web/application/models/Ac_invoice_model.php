<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_invoice_model extends MY_Model
{
    protected $table_name   = 'ac_invoices';
    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = TRUE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = ['id'];

    // protected $before_insert = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'invoice_code', 'customer_id', 'voucher_id', 'branch_id', 'fiscal_yr_id', 'fy_quarter', 'invoice_date', 'amount', 'flag_paid', 'flag_printed', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
     * Pre-add Tasks
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
         * Prepare Data
         */
        $master_data = $this->_pre_add_tasks($master_data);


        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
            $this->db->trans_start();


                /**
                 * Task 1: Insert Master Record
                 */
                $id = parent::insert($master_data, TRUE);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Insert Invoice Details
                 */
                $this->ac_invoice_detail_model->add($id, $batch_data_details);


                // --------------------------------------------------------------------

                /**
                 * Task 3: Generate Invoice Code
                 */
                $this->_post_add_tasks($id);

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
         * ==================== TRANSACTIONS END =========================
         */

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Before Insert Tasks
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
    private function _pre_add_tasks($data)
    {
        $this->load->library('Token');
        $fy_record  = $this->fiscal_year_model->get_fiscal_year($data['invoice_date']);
        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($data['invoice_date']);

        // Invoice Code
        $data['invoice_code']      = strtoupper(TOKEN::v2(10));

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
     * After Insert Tasks
     *
     * Tasks that are to be performed after policy is created are
     *      1. Generate and Update Invoice Code
     *
     * @param int $id Invoice ID
     * @return mixed
     */
    private function _post_add_tasks($id)
    {
        // Old Record
        $this->audit_old_record = parent::find($id);

        // Generate and Update Invoice Number
        $this->_generate_invoice_number($id);

        // Save Audit Log - Manually
         $this->save_audit_log([
            'method' => 'update',
            'id'     => $id,
            'fields' => (array)parent::find($id)
        ]);
        $this->audit_old_record = NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Generate Invoice Number
     *
     * @param type $id
     * @return mixed
     */
    private function _generate_invoice_number($id)
    {
        $params     = [$id, $this->dx_auth->get_user_id()];
        $sql        = "SELECT `f_generate_invoice_number`(?, ?) AS invoice_code";
        $result     = mysqli_store_procedure('select', $sql, $params);

        /**
         * Get the result
         */
        $result_row = $result[0];

        return $result_row->invoice_code;
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
        return parent::update($id, [$flag_name => $flag_value], TRUE);
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
        return $this->check_duplicate(['voucher_id' => $voucher_id]);
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
                            ->order_by('I.id', 'DESC')
                            ->get()
                            ->result();
        }



    // --------------------------------------------------------------------

    /**
     * Get the First Invoice of a Policy
     *
     * @param int $policy_id
     * @return object
     */
    public function first_invoice($policy_id)
    {
        return $this->db->select(
                            // Invoice Table
                            'I.*, ' .

                            // Receipt Data
                            'RCPT.id as receipt_id, RCPT.receipt_code, RCPT.created_at AS receipt_datetime, ' .

                            // Policy Voucher Relation Data
                            'REL.policy_id, REL.ref, REL.ref_id'
                        )
                    ->from($this->table_name . ' AS I')
                    ->join('ac_receipts RCPT', 'I.id = RCPT.invoice_id', 'left')
                    ->join('ac_vouchers V', 'V.id = I.voucher_id')
                    ->join('rel_policy__voucher REL', 'REL.voucher_id = I.voucher_id')
                    ->where('REL.policy_id', $policy_id)
                    ->order_by('I.id', 'ASC')
                    ->get()->row();
    }

    // --------------------------------------------------------------------

    public function get($id)
    {
        // Common Row Select
        $this->_row_select();

        // Get Branch Address Information
        $this->load->model('address_model');
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADR',

            // Country Table Alias
            'country' => 'CNTRY',

            // State Table Alias
            'state' => 'STATE',

            // Local Body Table Alias
            'local_body' => 'LCLBD',

            // Type/Module Table Alias
            'module' => 'B'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_BRANCH, NULL, $table_aliases, 'addr_', FALSE);

        // Policy, Customer Related JOIN
        $this->db->select(
                            // Policy Installment ID, Endorsement ID
                            'PTI.id as policy_installment_id, PTI.endorsement_id, ' .

                            // Policy Code
                            'POLICY.code AS policy_code, ' .

                            // Customer Details
                            'CST.full_name_en AS customer_full_name_en, CST.full_name_np AS customer_full_name_np, CST.pan AS customer_pan'
                        )
                    ->join('dt_policies POLICY', 'POLICY.id = REL.policy_id')
                    ->join('dt_policy_installments PTI', "REL.ref = '" . IQB_REL_POLICY_VOUCHER_REF_PI . "' AND REL.ref_id = PTI.id")
                    ->join('dt_customers CST', 'CST.id = I.customer_id');

        /**
         * Customer Address
         */
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADRC',

            // Country Table Alias
            'country' => 'CNTRYC',

            // State Table Alias
            'state' => 'STATEC',

            // Local Body Table Alias
            'local_body' => 'LCLBDC',

            // Type/Module Table Alias
            'module' => 'CST'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_customer_', FALSE);

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