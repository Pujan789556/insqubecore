<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_voucher_model extends MY_Model
{
    protected $table_name   = 'rel_policy__voucher';
    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $skip_validation      = TRUE;
    protected $protected_attributes = [];

    protected $before_insert = [];
    protected $after_insert  = [];
    protected $after_update  = [];
    protected $after_delete  = [];

    protected $fields = ['policy_id', 'voucher_id', 'ref', 'ref_id', 'flag_invoiced'];
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
    }

    // ----------------------------------------------------------------

    /**
     * Add New Voucher Internal Relation
     *
     * @param array $data
     * @return mixed
     */
    public function add($data, $use_automatic_transaction = FALSE)
    {
        $status = TRUE;

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        if($use_automatic_transaction)
        {
            $this->db->trans_start();
        }
                /**
                 * Save the Relation and Audit Log
                 */
                parent::insert($data, TRUE);

                // --------------------------------------------------------------------

        if($use_automatic_transaction)
        {
            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $status = FALSE;
            }
        }
        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $status;
    }

    // --------------------------------------------------------------------

    /**
     * Update flag_invoiced to ON/OFF
     *
     * @param array $where
     * @param int $to_status
     * @return bool
     */
    public function flag_invoiced($where, $to_status, $use_automatic_transaction = FALSE)
    {
        $data = ['flag_invoiced' => $to_status];
        $status = TRUE;

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        if($use_automatic_transaction)
        {
            $this->db->trans_start();
        }
                // Grab Old Record for Audit Log before Update
                $this->audit_old_record = parent::find_by($where);

                /**
                 * Task 1: Manually Update The record
                 */
                $this->db->where($where)
                        ->update($this->table_name, $data);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Manually Save Audit Log
                 */
                $audit_data = [
                    'id'     => NULL,
                    'fields' => $data,
                    'method' => 'update'
                ];
                parent::save_audit_log($audit_data, $where);
                $this->audit_old_record = NULL;

                // --------------------------------------------------------------------

        if($use_automatic_transaction)
        {
            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $status = FALSE;
            }
        }
        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $status;
    }

	// --------------------------------------------------------------------

    /**
     * Check if Voucher Exists for Given Transaction ID
     *
     * @param array $where  [
     *                          'REL.policy_id' => $policy_id,
     *                          'REL.ref'       => IQB_REL_POLICY_VOUCHER_REF_PI | IQB_REL_POLICY_VOUCHER_REF_CLM,
     *                          'REL.ref_id'    => $ref_id
     *                      ]
     * @return integer
     */
    public function voucher_exists($where)
    {
        return $this->db
                        ->from($this->table_name . ' AS REL')
                        ->join('ac_vouchers V', 'V.id = REL.voucher_id')
                        ->where($where)
                        ->count_all_results();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [

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
        return FALSE;
    }
}