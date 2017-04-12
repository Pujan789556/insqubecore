<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Premium_model extends MY_Model
{
    protected $table_name = 'dt_policy_premium';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['policy_id'];

    // protected $before_insert = ['prepare_contact_data', 'prepare_customer_defaults', 'prepare_customer_fts_data'];
    // protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['sum_insured_amount', 'total_premium_amount', 'pool_premium_amount', 'comissionable_amount', 'stamp_duty_amount', 'vat_amount', 'attributes', 'extra_fields', 'remarks', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

    protected $skip_validation = TRUE; // No need to validate on Model

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
        // !!!NOTE: 'sum_insured_amount' can not be emptied as it is updated when policy is updated

        $numeric_fields  = ['total_premium_amount', 'pool_premium_amount', 'comissionable_amount', 'stamp_duty_amount', 'vat_amount'];
        $nullable_fields = ['attributes', 'extra_fields', 'remarks'];

        $reset_data = [];

        foreach ($numeric_fields as $field)
        {
             $reset_data[$field] = 0;
        }

        foreach ($nullable_fields as $field)
        {
             $reset_data[$field] = NULL;
        }
        return $this->db->where('policy_id', $policy_id)
                 ->update($this->table_name, $reset_data);
    }

    // --------------------------------------------------------------------

    /**
     * Save a policy premium
     *
     * @param int $policy_id
     * @param array $data
     * @return bool
     */
    public function save($policy_id, $data)
    {

        /**
         * Let's Build computable fields
         *      - vat_amount
         */
        $this->load->model('ac_duties_and_tax_model');
        $vat_config_record = $this->ac_duties_and_tax_model->get(IQB_AC_DUTY_AND_TAX_ID_VAT);
        $vat_amount = $data['total_premium_amount'] * ($vat_config_record->rate/100.00);

        $data['vat_amount'] = $vat_amount;

        // Let's Update the premium
        return parent::update_by(['policy_id' => $policy_id], $data);

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