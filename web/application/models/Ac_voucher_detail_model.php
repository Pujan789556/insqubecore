<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_voucher_detail_model extends MY_Model
{
    protected $table_name = 'ac_voucher_details';

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

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
     * Add Voucher Details Records
     *
     * @param int $voucher_id
     * @param array $batch_data
     * @return array successfully inserted IDs
     */
    public function add($voucher_id, $batch_data)
    {
        $ids = [];

        /**
         * Update Voucher id on Batch Details
         */
        foreach($batch_data as $single )
        {
            $single['voucher_id'] = $voucher_id;

            $ids[] = parent::insert($single, TRUE);
        }

        return $ids;
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
        $where   = ['voucher_id' => $voucher_id];
        $records = parent::find_many_by($where);

        /**
         * Delete and Audit Log Manually
         */
        if($records)
        {
            // Delete Old Records
            $this->db->where($where)
                     ->delete($this->table_name);

            // Audit Log Manually
            foreach ($records as $record)
            {
                $this->audit_old_record = $record;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id'     => NULL
                ]);
                $this->audit_old_record = NULL;
            }
        }
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return FALSE;
    }
}