<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_claim_bsrs_heading_model extends MY_Model
{
    protected $table_name = 'rel_claim__bsrs_heading';

    protected $skip_validation = TRUE;

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['bsrs_heading_id', 'claim_id'];

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
     * Save BSRS Heading Relations with Policy
     *
     * @param inte $claim_id
     * @param array $bsrs_heading_ids
     * @return mixed
     */
    public function save($claim_id, $bsrs_heading_ids)
    {
        $old_records = $this->by_claim($claim_id);
        $old_bsrs_heading_ids = [];
        foreach($old_records as $single)
        {
            $old_bsrs_heading_ids[] = $single->bsrs_heading_id;
        }
        asort($old_bsrs_heading_ids);

        $new_bsrs_heading_ids = $bsrs_heading_ids;
        asort($new_bsrs_heading_ids);

        $to_del_bsrs_heading_ids    = array_diff($old_bsrs_heading_ids, $new_bsrs_heading_ids);
        $to_insert_bsrs_heading_ids = array_diff($new_bsrs_heading_ids, $old_bsrs_heading_ids);

        /**
         * Build Relation Data
         */
        $batch_data = [];
        foreach($bsrs_heading_ids as $bsrs_heading_id)
        {
            $batch_data[] = [
                'claim_id'         => $claim_id,
                'bsrs_heading_id'   => $bsrs_heading_id
            ];
        }

        /*
         * ============================= TRANSACTION STARTS ======================================
         */

            $status = TRUE;
            // Use automatic transaction
            $this->db->trans_start();


                /**
                 * Task 1: Delete Old Relations
                 */
                foreach($to_del_bsrs_heading_ids as $bsrs_heading_id)
                {
                    $this->_delete($claim_id, $bsrs_heading_id);
                }


                /**
                 * Task 2: Insert New Relation Data
                 */
                foreach($to_insert_bsrs_heading_ids as $bsrs_heading_id)
                {
                    $data = ['claim_id' => $claim_id, 'bsrs_heading_id' => $bsrs_heading_id];
                    parent::insert($data, TRUE);
                }

            // Commit all transactions on success, rollback else
            $this->db->trans_complete();


        /*
         * ============================= TRANSACTION ENDS ======================================
         */


        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            /**
             * Delete Cache
             */
            $this->clear_cache( 'bsrs_hd_byclaim_' . $claim_id );
        }

        // return result/status
        return $status;
    }

    // --------------------------------------------------------------------

    /**
     * Get list of all bs headings by Claim
     *
     * @param inte $claim_id
     * @return array
     */
    public function by_claim($claim_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsrs_hd_byclaim_' . $claim_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('REL.*, HD.heading_type_id, HD.code, HD.name AS heading_name, HT.name_en AS heading_type_name_en, HT.name_np AS heading_type_name_np')
                             ->from($this->table_name . ' AS REL')
                             ->join('bsrs_headings HD', 'HD.id = REL.bsrs_heading_id')
                             ->join('bsrs_heading_types HT', 'HT.id = HD.heading_type_id')
                             ->where('REL.claim_id', $claim_id)
                             ->order_by('HT.id')
                             ->order_by('HD.code')
                             ->get()->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    public function rel_exists($claim_id)
    {
        return $this->db
                        ->from($this->table_name . ' AS REL')
                        ->where('REL.claim_id', $claim_id)
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
                'bsrs_hd_byclaim_*'
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


    public function delete_by_claim($claim_id, $use_auto_transaction = FALSE)
    {
        $records = $this->db->select('*')->where('claim_id', $claim_id)->get($this->table_name)->result();
        if($records)
        {
            foreach($records as $single)
            {
                $this->delete_single($single->claim_id, $single->bsrs_heading_id, $use_auto_transaction);
            }
        }
    }

    // ----------------------------------------------------------------

    public function delete_single($claim_id, $bsrs_heading_id, $use_auto_transaction = TRUE)
    {
        $status = TRUE;
        if($use_auto_transaction )
        {
            // Use automatic transaction
            $this->db->trans_start();

                $this->_delete($claim_id, $bsrs_heading_id);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // get_allenerate an error... or use the log_message() function to log your error
                $status = FALSE;
            }
        }
        else
        {
            $status = $this->_delete($claim_id, $bsrs_heading_id);
        }

        // Clear Cache
        if($status)
        {
            $this->delete_cache('bsrs_hd_byclaim_' . $claim_id);
        }

        // return result/status
        return $status;
    }

        private function _delete($claim_id, $bsrs_heading_id)
        {
            // Set Old Audit Record - Manually
            $where = ['claim_id' => $claim_id, 'bsrs_heading_id' => $bsrs_heading_id];
            $this->audit_old_record = (object)$where;

            // Delete Manually
            $this->db->where($where)->delete($this->table_name);

            // Save Audit Log Manually
            $this->save_audit_log([
                'method' => 'delete',
                'id'     => NULL
            ],$where);
            $this->audit_old_record = NULL;

            return TRUE;
        }

    // ----------------------------------------------------------------

}