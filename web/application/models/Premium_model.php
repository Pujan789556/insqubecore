<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Premium_model extends MY_Model
{
    protected $table_name   = 'dt_premiums';
    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = TRUE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'endorsement_id', 'premium_for', 'gross_full_amt_basic_premium', 'gross_full_amt_pool_premium', 'gross_full_amt_commissionable', 'gross_full_amt_agent_commission', 'gross_full_amt_ri_commission', 'gross_full_amt_direct_discount', 'gross_computed_amt_basic_premium', 'gross_computed_amt_pool_premium', 'gross_computed_amt_commissionable', 'gross_computed_amt_agent_commission', 'gross_computed_amt_ri_commission', 'gross_computed_amt_direct_discount', 'refund_amt_basic_premium', 'refund_amt_pool_premium', 'refund_amt_commissionable', 'refund_amt_agent_commission', 'refund_amt_ri_commission', 'refund_amt_direct_discount', 'net_amt_basic_premium', 'net_amt_pool_premium', 'net_amt_commissionable', 'net_amt_agent_commission', 'net_amt_ri_commission', 'net_amt_direct_discount', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

    public function get_many_by_endorsement($endorsement_id)
    {
        return $this->db->select('PRM.*')
                        ->from($this->table_name . ' PRM')
                        ->where('PRM.endorsement_id', $endorsement_id)
                        ->get()->result();
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
}