<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_creditor_model extends MY_Model
{
    protected $table_name = 'rel_policy__creditor';

    protected $skip_validation = TRUE;

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['policy_id', 'creditor_id', 'creditor_branch_id'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 12 records from deletion.

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

    // --------------------------------------------------------------------

    public function get($policy_id, $creditor_id, $creditor_branch_id)
    {
        $where = [
            'policy_id'             => $policy_id,
            'creditor_id'           => $creditor_id,
            'creditor_branch_id'    => $creditor_branch_id,
        ];
        return $this->find_by($where);
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $creditor_branch_id = NULL)
    {
        if($creditor_branch_id)
        {
            $this->db->where('creditor_branch_id !=', $creditor_branch_id);
        }
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------

    public function save($data, $old_record = NULL)
    {
        /**
         * If OLD Record and NEW Record Same - DO NOTHING
         */
        if($old_record)
        {
            $old = [
                'policy_id'             => $old_record->policy_id,
                'creditor_id'           => $old_record->creditor_id,
                'creditor_branch_id'    => $old_record->creditor_branch_id,
            ];

            if($old == $data)
            {
                return TRUE;
            }
        }

        $status             = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Delete OLD record if any
             */
            if($old_record)
            {
                // Delete Old Record
                $this->delete_single($old_record->policy_id, $old_record->creditor_id, $old_record->creditor_branch_id, FALSE);
            }


            /**
             * Insert New Data
             */
            parent::insert($data);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;

    }

    // --------------------------------------------------------------------

    public function rows($where)
    {
        return $this->db->select(
                            // REL Table
                            "REL.*, " .

                            // Creditor Table
                            "C.name_en, C.name_np, " .

                            // Creditro Branch Table
                            "CB.name_en as branch_name_en, CB.name_np as branch_name_np"
                        )
                        ->from($this->table_name . ' AS REL')
                        ->join('master_companies C', 'REL.creditor_id = C.id')
                        ->join('master_company_branches CB', 'REL.creditor_branch_id = CB.id')
                        ->where($where)
                        ->get()
                        ->result();

    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
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

    public function delete_by_policy($policy_id, $use_automatic_transaction = TRUE)
    {
        $old_records = parent::find_many_by(['policy_id' => $policy_id]);

        if($old_records)
        {
            foreach ($old_records as $single)
            {
                $this->delete_single($single->policy_id, $single->creditor_id, $single->creditor_branch_id, $use_automatic_transaction);
            }
        }

        return TRUE;
    }

    public function delete_single($policy_id, $creditor_id, $creditor_branch_id, $use_automatic_transaction = TRUE)
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
                 * Task 1: Manually Delete Old Relations
                 */
                $where = ['policy_id' => $policy_id, 'creditor_id' => $creditor_id, 'creditor_branch_id' => $creditor_branch_id];
                $this->db->where($where)
                         ->delete($this->table_name);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Manually Audit Log
                 */
                $this->audit_old_record = (object)$where;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id'     => NULL
                ]);
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

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return false;
    }

    // ----------------------------------------------------------------
}