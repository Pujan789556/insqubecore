<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_voucher_detail_model extends MY_Model
{
    protected $table_name = 'ac_voucher_details';

    protected $set_created = false;
    protected $set_modified = false;
    protected $log_user = false;

    protected $protected_attributes = [];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['sno', 'flag_type', 'voucher_id', 'account_id', 'party_type', 'party_id', 'amount'];

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

	// --------------------------------------------------------------------

    /**
     * Batch Insert Voucher Details Records
     *
     * @param integer $voucher_id
     * @param array $batch_data
     * @return bool
     */
    public function batch_insert($voucher_id, $batch_data)
    {
        /**
         * Update voucher id on Batch Details
         */
        foreach($batch_data as &$single )
        {
            $single['voucher_id'] = $voucher_id;
        }

        // Insert Batch
        if( $batch_data )
        {
            return $this->db->insert_batch( $this->table_name, $batch_data);
        }
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Get Voucher Detail Rows for Given Voucher ID
     *
     * @param integer $voucher_id
     * @return array
     */
    public function rows_by_voucher($voucher_id)
    {
        return $this->db->select(
                        // Voucher Table
                        'VDTL.*, ' .

                        // Voucher Type Table
                        'AC.account_group_id, AC.name AS account_name'
                    )
                ->from($this->table_name . ' AS VDTL')
                ->join('ac_accounts AC', 'AC.id = VDTL.account_id')
                ->where('VDTL.voucher_id', $voucher_id)
                ->get()
                ->result();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [ ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_old( $voucher_id )
    {
        return parent::delete_by(['voucher_id' => $voucher_id]);
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return FALSE;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        return TRUE;

        // $action = is_string($action) ? $action : 'C';
        // // Save Activity Log
        // $activity_log = [
        //     'module'    => 'ac_account',
        //     'module_id' => $id,
        //     'action'    => $action
        // ];
        // return $this->activity->save($activity_log);
    }
}