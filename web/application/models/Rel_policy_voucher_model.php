<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_voucher_model extends MY_Model
{
    protected $table_name   = 'rel_policy__voucher';
    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $skip_validation      = TRUE;
    protected $protected_attributes = [];

    protected $before_insert = [];
    protected $after_insert  = [];
    protected $after_update  = [];
    protected $after_delete  = [];

    protected $fields = ['policy_id', 'voucher_id', 'ref', 'ref_id', 'flag_invoiced'];
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

    // ----------------------------------------------------------------

    /**
     * Add New Voucher Internal Relation
     *
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        /**
         * !!!IMPORTANT
         *  Can't call parent::insert() because it requires primary key as 'id'
         *  to return a positive integer. Calling this ( parent::insert() ) simply
         *  return '0' because it does not find a primary key.
         */
        $done = $this->db->insert($this->table_name, $data);
        if( !$done )
        {
            throw new Exception("Exception [Model: Rel_policy_voucher_model][Method: add()]: Could not insert record.");
        }

        // return result/status
        return $done;
    }

    // --------------------------------------------------------------------

    /**
     * Update flag_invoiced to ON/OFF
     *
     * @param array $where
     * @param int $to_status
     * @return bool
     */
    public function flag_invoiced($where, $to_status)
    {
        return $this->db->where($where)
                        ->update($this->table_name, ['flag_invoiced' => $to_status]);
    }

	// --------------------------------------------------------------------

    /**
     * Check if Voucher Exists for Given Transaction ID
     *
     * @param array $where  [
     *                          'REL.policy_id' => $policy_id,
     *                          'REL.ref'       => IQB_REL_POLICY_VOUCHER_REF_PI | IQB_REL_POLICY_VOUCHER_REF_CLM,
     *                          'REL.ref_id'    => $ref_id
     *                      ]
     * @return integer
     */
    public function voucher_exists($where)
    {
        return $this->db
                        ->from($this->table_name . ' AS REL')
                        ->join('ac_vouchers V', 'V.id = REL.voucher_id')
                        ->where($where)
                        ->where('V.flag_complete', IQB_FLAG_ON)
                        ->count_all_results();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
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
        return FALSE;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        return true;
        // $action = is_string($action) ? $action : 'C';
        // // Save Activity Log
        // $activity_log = [
        //     'module'    => 'ac_voucher',
        //     'module_id' => $id,
        //     'action'    => $action
        // ];
        // return $this->activity->save($activity_log);
    }
}