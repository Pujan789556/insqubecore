<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_claim_bsrs_heading_model extends MY_Model
{
    protected $table_name = 'rel_claim__bsrs_heading';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

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

            // Disable DB Debug for transaction to work
            $this->db->db_debug = FALSE;

            // Use automatic transaction
            $this->db->trans_start();


                /**
                 * Task 1: Delete Old Relations
                 */
                parent::delete_by(['claim_id' => $claim_id]);


                /**
                 * Task 2: Insert New Relation Data
                 */
                parent::insert_batch($batch_data, TRUE);


            // Commit all transactions on success, rollback else
            $this->db->trans_complete();


        /*
         * ============================= TRANSACTION ENDS ======================================
         */


        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }
        else
        {
            /**
             * Delete Cache
             */
            $this->clear_cache( 'bsrs_hd_byclaim_' . $claim_id );
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return TRUE;
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
}