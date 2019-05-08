<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_invoice_detail_model extends MY_Model
{
    protected $table_name = 'ac_invoice_details';

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['invoice_id', 'description', 'amount'];

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
     * Add Invoice Details Records
     *
     * @param int $invoice_id
     * @param array $batch_data
     * @return array successfully inserted IDs
     */
    public function add($invoice_id, $batch_data)
    {
        $ids = [];

        /**
         * Update Invoice id on Batch Details
         */
        foreach($batch_data as $single )
        {
            $single['invoice_id'] = $invoice_id;

            $ids[] = parent::insert($single, TRUE);
        }

        return $ids;
    }

    // --------------------------------------------------------------------

    /**
     * Get Invoice Detail Rows for Given Invoice ID
     *
     * @param integer $invoice_id
     * @return array
     */
    public function rows_by_invoice($invoice_id)
    {

        return $this->db->select('IDTL.*')
                        ->from($this->table_name . ' AS IDTL')
                        ->where('IDTL.invoice_id', $invoice_id)
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

    public function delete($id = NULL)
    {
        return FALSE;
    }
}