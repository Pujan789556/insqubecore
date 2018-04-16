<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_customer_object_model extends MY_Model
{
    protected $table_name = 'rel_customer__object';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

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
        return $this->db->where($where)
                        ->update($this->table_name, $data);
    }

    // --------------------------------------------------------------------

    public function add_new_object_owner( $object_id, $customer_id )
    {
        return parent::insert([
            'object_id'     => $object_id,
            'customer_id'   => $customer_id
        ]);
    }

    // --------------------------------------------------------------------

    /**
     * Save (Insert or Update) customer relation on debit note add/edit
     *
     * @param array $data
     * @return mixed
     */
    // public function save_on_debit_note($data)
    // {
    //     $object_id  = $data['object_id'];
    //     $customer_id = $data['customer_id'] ?? NULL;

    //     if($customer_id)
    //     {
    //         // Do we have exisiting record? Simply update agent id
    //         $where = [
    //             'object_id' => $object_id
    //         ];
    //         $row = $this->find_by($where);

    //         if( !$row )
    //         {
    //             // No Record, Insert
    //             return $this->insert($data);
    //         }
    //         else if( $row && $row->customer_id != $customer_id )
    //         {
    //             // Has record and Different customer ID
    //             return $this->update_by($where, ['customer_id' => $customer_id]);
    //         }
    //         else
    //         {
    //             return TRUE;
    //         }
    //     }
    //     return FALSE;
    // }

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

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------
}