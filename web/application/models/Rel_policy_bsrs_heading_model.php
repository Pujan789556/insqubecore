<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_bsrs_heading_model extends MY_Model
{
    protected $table_name = 'rel_policy__bsrs_heading';

    protected $skip_validation = TRUE;

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['bsrs_heading_id', 'policy_id'];

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

    /**
     * Save Relation
     * @param int $policy_id
     * @param array $bsrs_heading_ids BSRS Heading IDs
     * @return bool
     */
    public function save($policy_id, $bsrs_heading_ids)
    {
        /**
         * Old List
         */
        $old_ids = $this->bsrs_heading_ids_by_policy($policy_id, TRUE);
        asort($old_ids);

        // sort new ids
        asort($bsrs_heading_ids);


        // to del ids
        $to_del_tags = array_diff($old_ids, $bsrs_heading_ids);

        // to insert ids
        $to_insert_tags = array_diff($bsrs_heading_ids, $old_ids);

        // --------------------------------------------------------------------

        $status = TRUE;

        /*
         * ============================= TRANSACTION STARTS ======================================
         */
            $this->db->trans_start();

                /**
                 * Task 1: Insert New
                 */
                foreach($to_insert_tags as $bsrs_heading_id)
                {
                    $single_data = [
                        'policy_id'         => $policy_id,
                        'bsrs_heading_id'   => $bsrs_heading_id
                    ];
                    parent::insert($single_data, TRUE);
                }

                // --------------------------------------------------------------------


                /**
                 * Task 2: Delete unwanted
                 */
                foreach($to_del_tags as $bsrs_heading_id)
                {
                    $this->delete_single($policy_id, $bsrs_heading_id, FALSE);
                }

                // --------------------------------------------------------------------


                /**
                 * Task 3: Delete Cache
                 */
                $this->clear_cache( 'bsrs_hd_bypolicy_' . $policy_id );
                // --------------------------------------------------------------------


            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $status = FALSE;
            }
        /*
         * ============================= TRANSACTION ENDS ======================================
         */

        return $status;
    }

    // --------------------------------------------------------------------

    /**
     * Get list of all bs headings by Policy
     *
     * @param inte $policy_id
     * @return array
     */
    public function by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsrs_hd_bypolicy_' . $policy_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('REL.*, HD.heading_type_id, HD.code, HD.name AS heading_name, HT.name_en AS heading_type_name_en, HT.name_np AS heading_type_name_np')
                             ->from($this->table_name . ' AS REL')
                             ->join('bsrs_headings HD', 'HD.id = REL.bsrs_heading_id')
                             ->join('bsrs_heading_types HT', 'HT.id = HD.heading_type_id')
                             ->where('REL.policy_id', $policy_id)
                             ->order_by('HT.id')
                             ->order_by('HD.code')
                             ->get()->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get list of all bs headings by Policy
     *
     * @param inte $policy_id
     * @return array
     */
    public function bsrs_heading_ids_by_policy($policy_id)
    {
        $records = $this->by_policy($policy_id);
        $list = [];
        if($records)
        {
            foreach ($records as $single)
            {
                $list[] = $single->bsrs_heading_id;
            }
        }

        return $list;
    }

    // --------------------------------------------------------------------

    public function rel_exists($policy_id)
    {
        return $this->db
                        ->from($this->table_name . ' AS REL')
                        ->where('REL.policy_id', $policy_id)
                        ->count_all_results();
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache
     */
    public function clear_cache( $data=null )
    {
        /**
         * If no data supplied, delete all caches
         */
        if( !$data )
        {
            $cache_names = [
                'bsrs_hd_bypolicy_*'
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

    /**
     * Delete a Single Relation
     *
     * @param int $policy_id
     * @param int $bsrs_heading_id
     * @param bool $use_automatic_transaction
     * @return bool
     */
    public function delete_single($policy_id, $bsrs_heading_id, $use_automatic_transaction = TRUE)
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
                $where = ['policy_id' => $policy_id, 'bsrs_heading_id' => $bsrs_heading_id];
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
}