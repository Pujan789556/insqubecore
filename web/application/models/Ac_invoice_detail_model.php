<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_invoice_detail_model extends MY_Model
{
    protected $table_name = 'ac_invoice_details';

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

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
     * Batch Insert Invoice Details Records
     *
     * @param integer $invoice_id
     * @param array $batch_data
     * @return bool
     */
    public function batch_insert($invoice_id, $batch_data)
    {
        /**
         * Update Invoice id on Batch Details
         */
        foreach($batch_data as &$single )
        {
            $single['invoice_id'] = $invoice_id;
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
     * Get Invoice Detail Rows for Given Invoice ID
     *
     * @param integer $invoice_id
     * @return array
     */
    public function rows_by_invoice($invoice_id)
    {

        return parent::find_by(['invoice_id'=>$invoice_id]);
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

    public function delete_old( $invoice_id )
    {
        return parent::delete_by(['invoice_id' => $invoice_id]);
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
    }
}