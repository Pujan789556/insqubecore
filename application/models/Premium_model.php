<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Premium_model extends MY_Model
{
    protected $table_name = 'dt_policy_premium';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data', 'prepare_customer_defaults', 'prepare_customer_fts_data'];
    // protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "policy_id", "total_amount", "comission_amount", "stamp_duty",  "attributes", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Required Helpers/Configurations
        $this->load->config('policy');
        $this->load->helper('policy');
        $this->load->helper('object');
    }

    // ----------------------------------------------------------------

    public function reset($policy_id)
    {
        $reset_data = [
            'total_amount'      => 0.00,
            'comission_amount'  => NULL,
            'stamp_duty'    => 0.00,
            'attributes'    => NULL
        ];
        return $this->db->where('policy_id', $policy_id)
                 ->update($this->table_name, $reset_data);
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        /**
         * Cache Clearance Logic:
         * --------------------------------------
         * Every cache object belongs to specific customer ID.
         * So, when we add/edit/delete a object, we only need to
         * clear cache of the specific customer's object cache
         */
        $cache_names = [];
        $id = $data['id'] ?? null;
        $customer_id = $data['fields']['customer_id'] ?? '*';
        if( !$customer_id)
        {
            $record = $this->row($id);
            $customer_id = $record->customer_id;
        }
        $cache_names[] = 'object_customer_' . $customer_id;
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

        $record = $this->row($id);
        if(!$record)
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
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            // Clear cache for this customer
            $data['fields']['customer_id'] = $record->customer_id;
            $this->clear_cache($data);

            $this->log_activity($id, 'D');
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
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
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'premium',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}