<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_credit_note_model extends MY_Model
{
    protected $table_name   = 'ac_credit_notes';
    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert__defaults'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'customer_id', 'voucher_id', 'branch_id', 'fiscal_yr_id', 'fy_quarter', 'credit_note_date', 'amount', 'flag_paid', 'flag_printed', 'flag_complete', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $this->load->model('ac_credit_note_detail_model');
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Add New Credit Note
     *
     * The following tasks are carried during Credit Note Add:
     *      a. Insert Master Record, Update Credit Note Code
     *      b. Insert Credit Note Details
     *
     * @param array $master_data
     * @param array $batch_data_details
     * @param int   $policy_id (if credit_note generated for Endorsement)
     * @return mixed
     */
    public function add($master_data, $batch_data_details, $policy_id=NULL)
    {
        /**
         * !!! IMPORTANT
         *
         * We do not use transaction here as we may lost the credit_note id autoincrement.
         * We simply use try catch block.
         *
         * If transaction fails, we will have a credit_note with complete flag off.
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
                     * Task 1: Insert Credit Note Details
                     */
                    $this->ac_credit_note_detail_model->batch_insert($id, $batch_data_details);

                    // --------------------------------------------------------------------

                    /**
                     * Task 2: Complete Credit Note Status
                     */
                    $this->enable_credit_note($id);

                    // --------------------------------------------------------------------

                    /**
                     * Task 3: Clear Cache (For this Policy)
                     */
                    if($policy_id)
                    {
                        $cache_var = 'ac_credit_note_list_by_policy_'.$policy_id;
                        $this->clear_cache($cache_var);
                    }

                    // --------------------------------------------------------------------


                /**
                 * Complete transactions or Rollback
                 */
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE)
                {
                    throw new Exception("Exception [Model: Ac_credit_note_model][Method: add()]: Could not save Credit Note details and other details.");
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
            throw new Exception("Exception [Model: Ac_credit_note_model][Method: add()]: Could not insert record.");
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Enable Credit Note Transaction [Complete Flagg - OFF]
     *
     * @param integer $id
     * @return boolean
     */
    public function enable_credit_note($id)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, ['flag_complete' => IQB_FLAG_ON]);
    }

    // --------------------------------------------------------------------

    /**
     * Disable credit_note Transaction [Complete Flagg - OFF]
     *
     * @param integer $id
     * @return boolean
     */
    public function disable_credit_note($id)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, ['flag_complete' => IQB_FLAG_OFF]);
    }

    // --------------------------------------------------------------------

    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      3. Add Branch ID
     *      4. Add Fiscal Year ID
     *      5. Add Fiscal Year Quarter
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $fy_record  = $this->fiscal_year_model->get_fiscal_year($data['credit_note_date']);
        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($data['credit_note_date']);

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
     * Update Credit Note Flags
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
     * Check if Credit Note Exists for Given Voucher ID
     *
     * @param integer $voucher_id
     * @return integer
     */
    public function credit_note_exists($voucher_id)
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

        return $this->db->where('CN.id', $id)
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
                $this->db->where(['CN.id <=' => $next_id]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
                $this->db->where(['CN.branch_id' =>  $branch_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['CN.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $fy_quarter = $params['fy_quarter'] ?? NULL;
            if( $fy_quarter )
            {
                $this->db->where(['CN.fy_quarter' =>  $fy_quarter]);
            }

            // Start Dates
            $from_date = $params['from_date'] ?? NULL;
            if( $from_date )
            {
                $this->db->where(['CN.credit_note_date >=' =>  $from_date]);
            }

            // End Dates
            $to_date = $params['to_date'] ?? NULL;
            if( $to_date )
            {
                $this->db->where(['CN.credit_note_date <=' =>  $to_date]);
            }

            // Credit Note Code
            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('CN.id', $keywords, 'after');
            }
        }

        return $this->db
                    ->order_by('CN.id', 'DESC')
                    ->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                        // Credit Note Table
                        'CN.*, ' .

                        // Branch Table
                        'B.name AS branch_name, ' .

                        // Fiscal Year Table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

                        // Policy Voucher Relation Data
                        'REL.policy_id, REL.ref, REL.ref_id'
                    )
                ->from($this->table_name . ' AS CN')
                ->join('master_branches B', 'B.id = CN.branch_id')
                ->join('master_fiscal_yrs FY', 'FY.id = CN.fiscal_yr_id')
                ->join('ac_vouchers V', 'V.id = CN.voucher_id')
                ->join('rel_policy__voucher REL', 'REL.voucher_id = CN.voucher_id');

        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('CN');
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'ac_credit_note_list_by_policy_'.$policy_id;
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
            return $this->db->select('REL.policy_id, REL.ref, REL.ref_id')
                            ->where('REL.policy_id', $policy_id)
                            ->where('CN.flag_complete', IQB_FLAG_ON)
                            ->where('V.flag_complete', IQB_FLAG_ON)
                            ->order_by('CN.id', 'DESC')
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
                    ->join('dt_customers CST', 'CST.id = CN.customer_id');

        /**
         * Complete/Active Credit Note?
         */
        if($flag_complete !== NULL )
        {
            $this->db->where('CN.flag_complete', (int)$flag_complete);
        }

        return $this->db->where('CN.id', $id)
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
                'ac_credit_note_list_by_policy_*'
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