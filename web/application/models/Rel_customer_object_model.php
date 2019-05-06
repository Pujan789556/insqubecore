<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_customer_object_model extends MY_Model
{
    protected $table_name = 'rel_customer__object';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ["customer_id", "object_id"];

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

    public function reset_current_owner( $object_id, $customer_id )
    {
        $where = [
            'object_id'     => $object_id,
            'customer_id'   => $customer_id
        ];
        $data = ['flag_current' => IQB_FLAG_OFF];


        // Set Old Audit Record - Manually
        $this->audit_old_record = parent::find_by($where);

        // Update Data - Manually
        $this->db->where($where)
                 ->set($data)
                 ->update($this->table_name);

         // Save Audit Log - Manually
         $this->save_audit_log([
            'method' => 'update',
            'id'     => NULL,
            'fields' => $data
        ],$where);
        $this->audit_old_record = NULL;
    }

    // --------------------------------------------------------------------

    public function add_new_object_owner( $object_id, $customer_id )
    {
        return parent::insert([
            'object_id'     => $object_id,
            'customer_id'   => $customer_id,
            'flag_current'  => IQB_FLAG_ON
        ]);
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

    public function delete($id = NULL)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        $status = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------
}