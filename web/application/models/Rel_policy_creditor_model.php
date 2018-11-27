<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_creditor_model extends MY_Model
{
    protected $table_name = 'rel_policy__creditor';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

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

    public function check_duplicate($where)
    {
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

        // Disable DB Debug for transaction to work
        $this->db->db_debug = TRUE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Delete OLD record if any
             */
            if($old_record)
            {
                $where = [
                    'policy_id'             => $old_record->policy_id,
                    'creditor_id'           => $old_record->creditor_id,
                    'creditor_branch_id'    => $old_record->creditor_branch_id,
                ];
                parent::delete_by($where);
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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

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
                            "C.name, " .

                            // Creditro Branch Table
                            "CB.name as branch_name"
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

    public function delete_creditor($record)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            $where = [
                'policy_id'             => $record->policy_id,
                'creditor_id'           => $record->creditor_id,
                'creditor_branch_id'    => $record->creditor_branch_id,
            ];
            parent::delete_by($where);

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

    // ----------------------------------------------------------------

    public function delete_by_policy($policy_id)
    {
        return parent::delete_by(['policy_id' => $policy_id]);

    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return false;
    }

    // ----------------------------------------------------------------
}