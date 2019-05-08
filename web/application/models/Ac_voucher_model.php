<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_voucher_model extends MY_Model
{
    protected $table_name   = 'ac_vouchers';
    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = TRUE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'voucher_code', 'branch_id', 'fiscal_yr_id', 'fy_quarter', 'voucher_type_id', 'voucher_date', 'narration', 'flag_internal', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $this->load->model('ac_voucher_detail_model');
        $this->load->model('ac_voucher_type_model');
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
        $this->validation_rules = [

            /**
             * Voucher Basic Validation Rules
             */
            'basic' => $this->_v_rules_basic(),

            /**
             * Voucher Details Validation Rules
             */
            'details' => $this->_v_rules_details()
        ];
    }

        private function _v_rules_basic()
        {
            /**
             * @TODO - Does this user have "back-date" allowed?
             */
            $dropdown_voucher_types     = $this->ac_voucher_type_model->dropdown();
            $v_rules = [
                // Can not go back than current quarter or future
                [
                    'field' => 'voucher_date',
                    'label' => 'Voucher Date',
                    'rules' => 'trim|required|valid_date|callback__valid_voucher_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'voucher_type_id',
                    'label' => 'Voucher Type',
                    'rules' => 'trim|required|integer|max_length[3]|in_list[' . implode(',', array_keys($dropdown_voucher_types)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $dropdown_voucher_types,
                    '_required' => true
                ],
                [
                    'field' => 'narration',
                    'label' => 'Narration',
                    'rules' => 'trim|max_length[300]',
                    'rows'  => '3',
                    '_type'     => 'textarea',
                    '_required' => false
                ]
            ];

            return $v_rules;
        }

        private function _v_rules_details()
        {
            $dropdown_party_types = ac_party_types_dropdown(false);
            $v_rules = [

                /**
                 * Credit Row
                 */
                'credits' => [
                    [
                        'field' => 'account_id[cr][]',
                        'label' => 'Account',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_field'    => 'account_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_required' => true
                    ],
                    [
                        'field' => 'party_type[cr][]',
                        'label' => 'Party Type',
                        'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
                        '_field'    => 'party_type',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
                        '_extra_attributes' => 'data-field="party_type" onchange="__reset_party(this)"',
                        '_required' => false
                    ],
                    [
                        'field' => 'party_id[cr][]',
                        'label' => 'Party',
                        'rules' => 'trim|integer|max_length[11]',
                        '_field'    => 'party_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_required' => false
                    ],
                    [
                        'field' => 'amount[cr][]',
                        'label' => 'Credit Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_field'        => 'amount',
                        '_extra_attributes' => 'data-group="cr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
                        '_show_label' => false,
                        '_type'         => 'text',
                        '_show_label'   => false,
                        '_required'     => true
                    ],
                ],

                /**
                 * Debit Row
                 */
                'debits' => [
                    [
                        'field' => 'account_id[dr][]',
                        'label' => 'Account',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_field'    => 'account_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_required' => true
                    ],
                    [
                        'field' => 'party_type[dr][]',
                        'label' => 'Party Type',
                        'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
                        '_field'    => 'party_type',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
                        '_extra_attributes' => 'data-field="party_type" onchange="__reset_party(this)"',
                        '_required' => false
                    ],
                    [
                        'field' => 'party_id[dr][]',
                        'label' => 'Party',
                        'rules' => 'trim|integer|max_length[11]',
                        '_field'    => 'party_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_required' => false
                    ],
                    [
                        'field' => 'amount[dr][]',
                        'label' => 'Debit Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|callback__valid_voucher_amount', // compute dr = cr
                        '_field'        => 'amount',
                        '_extra_attributes' => 'data-group="dr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
                        '_show_label' => false,
                        '_type'         => 'text',
                        '_show_label'   => false,
                        '_required'     => true
                    ],
                ]
            ];

            return $v_rules;
        }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules Formatted
     *
     * @return array
     */
    public function validation_rules_formatted()
    {
        $v_rules         = $this->_v_rules_basic();
        $sectioned_rules = $this->_v_rules_details();

        foreach($sectioned_rules as $section => $rules)
        {
            $v_rules = array_merge($v_rules, $rules);
        }

        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Add New Voucher
     *
     * The following tasks are carried during Voucher Add:
     *      a. Insert Master Record, Update Voucher Code
     *      b. Insert Voucher Details - Debit, Credit
     *
     * @param array $data
     * @param int   $policy_id (if voucher generated for Endorsement)
     * @return mixed
     */
    public function add($data, $policy_id=NULL)
    {
        /**
         * Prepare Master Record Data
         */
        $master_data = $data['master'];
        $master_data = $this->_pre_add_tasks($master_data);


        // ----------------------------------------------------------------

        /**
         * Batch Data - Voucher Details
         */
        $batch_data_details = $this->_build_voucher_details_batch_data($data);


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
                 * Task 2: Insert Voucher Details
                 */
                $this->ac_voucher_detail_model->add($id, $batch_data_details);

                // --------------------------------------------------------------------

                /**
                 * Task 3: Generate Invoice Code
                 */
                $this->_post_add_tasks($id);

                // --------------------------------------------------------------------

                /**
                 * Task 3: Clear Cache (For this Policy)
                 */
                if($policy_id)
                {
                    $cache_var = 'ac_voucher_list_by_policy_'.$policy_id;
                    $this->clear_cache($cache_var);
                }

                // --------------------------------------------------------------------

            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $id = FALSE;
            }

        /**
         * ==================== TRANSACTIONS END =========================
         */

        // return result/status
        return $id;
    }

    // ----------------------------------------------------------------

    /**
     * Edit Voucher
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Update Master Record
     *      b. Insert Voucher Details - Debit, Credit (Remove Old Ones)
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function edit($id, $data)
    {
        /**
         * Prepare Master Record Data
         */
        $master_data = $data['master'];

        // ----------------------------------------------------------------

        /**
         * Batch Data - Voucher Details
         */
        $batch_data_details = $this->_build_voucher_details_batch_data($data);


        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
            $this->db->trans_start();

                /**
                 * Task 1: Insert Master Record
                 */
                $status = parent::update($id, $master_data, TRUE);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Delete Old Details Records
                 */
                $this->ac_voucher_detail_model->delete_old($id);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Insert New Voucher Details
                 */
                $this->ac_voucher_detail_model->add($id, $batch_data_details);

                // --------------------------------------------------------------------

            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $status = FALSE;
            }

        /**
         * ==================== TRANSACTIONS END =========================
         */

        // return result/status
        return $status;
    }



    // --------------------------------------------------------------------

    /**
     * Build Voucher Details Batch Data
     *
     * @param array $data Form Post Data
     * @return array
     */
    private function _build_voucher_details_batch_data($data)
    {
        $batch_data = [];

        // ----------------------------------------------------------------


        // Build Debit Rows
        $sno = 1;
        foreach( $data['dr_rows'] as $row)
        {
            $row['sno']         = $sno;
            $row['flag_type']   = IQB_AC_FLAG_DEBIT;

            $batch_data[]   = $row;

            $sno++;
        }

        // ----------------------------------------------------------------

        // Build Credit Rows
        $sno = 1;
        foreach( $data['cr_rows'] as $row)
        {
            $row['sno']         = $sno;
            $row['flag_type']   = IQB_AC_FLAG_CREDIT;

            $batch_data[]   = $row;

            $sno++;
        }

        // ----------------------------------------------------------------

        /**
         * DR === CR ???
         */
        if( !$this->is_dr_equals_cr($batch_data) )
        {
            throw new Exception("Exception [Model: Ac_voucher_model][Method: _build_voucher_details_batch_data()]: Debit Total is NOT EXACT equal to Credit Total.");
        }

        return $batch_data;
    }

    // --------------------------------------------------------------------

    /**
     * Pre-add Tasks
     *
     * Tasks carried
     *      2. Add Draft Voucher Code (Random Characters)
     *      3. Add Branch ID
     *      4. Add Fiscal Year ID
     *      5. Add Fiscal Year Quarter
     *
     * @param array $data
     * @return array
     */
    public function _pre_add_tasks($data)
    {
        $this->load->library('Token');

        $voucher_date = $data['voucher_date'];

        $fy_record  = $this->fiscal_year_model->get_fiscal_year($voucher_date);
        if(!$fy_record)
        {
            throw new Exception("Exception [Model: Ac_voucher_model][Method: before_insert__defaults()]: Fiscal Year not found for supplied voucher date ({$voucher_date}).");
        }

        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($voucher_date);
        if(!$fy_quarter)
        {
            throw new Exception("Exception [Model: Ac_voucher_model][Method: before_insert__defaults()]: Fiscal Year Quarter not found for supplied voucher date ({$voucher_date}).");
        }

        // Voucher Code
        $data['voucher_code']      = strtoupper(TOKEN::v2(10));

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
     *      1. Generate and Update Voucher Code
     *
     * @param int $id
     * @return void
     */
    public function _post_add_tasks($id)
    {
        // Old Record
        $this->audit_old_record = parent::find($id);

        // Generate and Update Voucher Number
        $this->_generate_voucher_number($id);

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
    private function _generate_voucher_number($id)
    {
        $params     = [$id, $this->dx_auth->get_user_id()];
        $sql        = "SELECT `f_generate_voucher_number`(?, ?) AS voucher_code";
        $result     = mysqli_store_procedure('select', $sql, $params);

        /**
         * Get the result
         */
        $result_row = $result[0];

        return $result_row->voucher_code;
    }

    // --------------------------------------------------------------------

    /**
     * Check if Debit Equals Credit
     *
     * @param array $voucher_rows
     * @return boolean
     */
    function is_dr_equals_cr($voucher_rows)
    {
        $debit_total    = 0.00;
        $credit_total   = 0.00;

        // Compute Debit Total
        foreach($voucher_rows as $row)
        {
            if($row['flag_type'] == IQB_AC_FLAG_DEBIT )
            {
                $debit_total += $row['amount'];
                $debit_total = bcadd($debit_total, $row['amount'], IQB_AC_DECIMAL_PRECISION);
            }
            else
            {
                $credit_total += $row['amount'];

                $credit_total = bcadd($credit_total, $row['amount'], IQB_AC_DECIMAL_PRECISION);
            }
        }

        if( $credit_total === $debit_total )
        {
            return TRUE;
        }

        return FALSE;
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

        return $this->db->where('V.id', $id)
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
                $this->db->where(['V.id <=' => $next_id]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
                $this->db->where(['V.branch_id' =>  $branch_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['V.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $fy_quarter = $params['fy_quarter'] ?? NULL;
            if( $fy_quarter )
            {
                $this->db->where(['V.fy_quarter' =>  $fy_quarter]);
            }

            // Start Dates
            $start_date = $params['start_date'] ?? NULL;
            if( $start_date )
            {
                $this->db->where(['V.voucher_date >=' =>  $start_date]);
            }

            // End Dates
            $end_date = $params['end_date'] ?? NULL;
            if( $end_date )
            {
                $this->db->where(['V.voucher_date <=' =>  $end_date]);
            }

            // Voucher Code
            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('V.voucher_code', $keywords, 'after');
            }
        }
        return $this->db
                    ->order_by('V.id', 'DESC')
                    ->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                        // Voucher Table
                        'V.id, V.voucher_type_id, V.branch_id,  V.voucher_code, V.fiscal_yr_id, V.voucher_date, V.flag_internal, V.voucher_date, ' .

                        // Voucher Type Table
                        'VT.name AS voucher_type_name, ' .

                        // Branch Table
                        'B.name_en AS branch_name_en, B.name_np AS branch_name_np, ' .

                        // Fiscal Year Table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np'
                    )
                ->from($this->table_name . ' AS V')
                ->join('ac_voucher_types VT', 'VT.id = V.voucher_type_id')
                ->join('master_branches B', 'B.id = V.branch_id')
                ->join('master_fiscal_yrs FY', 'FY.id = V.fiscal_yr_id');


        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('V');
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'ac_voucher_list_by_policy_'.$policy_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows_by_policy($policy_id);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        // $rows = $this->_rows_by_policy($policy_id);
        // echo $this->db->last_query();exit;
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
            return $this->db->select('REL.policy_id, REL.ref, REL.ref_id, REL.flag_invoiced')
                        ->join('rel_policy__voucher REL', 'REL.voucher_id = V.id')
                        ->where('REL.policy_id', $policy_id)
                        ->order_by('V.id', 'DESC')
                        ->get()
                        ->result();
        }

    // --------------------------------------------------------------------

    public function get($id, $policy_relation = FALSE)
    {
        $select =   // Voucher Table
                    'V.*, ' .

                    // Voucher Type Table
                    'VT.name AS voucher_type_name, ' .

                    // Branch Table
                    'B.name_en AS branch_name_en, B.name_np AS branch_name_np, ' .

                    // Fiscal Year Table
                    'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np';

        /**
         * Add Policy Relation as well?
         */
        if( $policy_relation )
        {
            $select .= ', REL.policy_id, REL.ref, REL.ref_id, REL.flag_invoiced';
        }

        $this->db->select($select)
                ->from($this->table_name . ' AS V')
                ->join('ac_voucher_types VT', 'VT.id = V.voucher_type_id')
                ->join('master_branches B', 'B.id = V.branch_id')
                ->join('master_fiscal_yrs FY', 'FY.id = V.fiscal_yr_id');


        /**
         * Add Policy Relation as well?
         */
        if( $policy_relation )
        {
            $this->db->join('rel_policy__voucher REL', 'V.id = REL.voucher_id');
        }


        return $this->db->where('V.id', $id)
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
                'ac_voucher_list_by_policy_*'
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