<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_tax_and_commission_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaty_tax_and_commission';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = [];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['treaty_id', 'qs_comm_ri_quota', 'qs_comm_ri_surplus_1', 'qs_comm_ri_surplus_2', 'qs_comm_ri_surplus_3', 'qs_tax_ri_quota', 'qs_tax_ri_surplus_1', 'qs_tax_ri_surplus_2', 'qs_tax_ri_surplus_3', 'qs_tax_ib_quota', 'qs_tax_ib_surplus_1', 'qs_tax_ib_surplus_2', 'qs_tax_ib_surplus_3', 'flag_qs_comm_scale_quota', 'flag_qs_comm_scale_surplus_1', 'flag_qs_comm_scale_surplus_2', 'flag_qs_comm_scale_surplus_3', 'eol_min_n_deposit_amt_l1', 'eol_min_n_deposit_amt_l2', 'eol_min_n_deposit_amt_l3', 'eol_min_n_deposit_amt_l4', 'eol_premium_mode_l1', 'eol_premium_mode_l2', 'eol_premium_mode_l3', 'eol_premium_mode_l4', 'eol_min_rate_l1', 'eol_min_rate_l2', 'eol_min_rate_l3', 'eol_min_rate_l4', 'eol_max_rate_l1', 'eol_max_rate_l2', 'eol_max_rate_l3', 'eol_max_rate_l4', 'eol_fixed_rate_l1', 'eol_fixed_rate_l2', 'eol_fixed_rate_l3', 'eol_fixed_rate_l4', 'eol_loading_factor_l1', 'eol_loading_factor_l2', 'eol_loading_factor_l3', 'eol_loading_factor_l4', 'eol_tax_ri_l1', 'eol_tax_ri_l2', 'eol_tax_ri_l3', 'eol_tax_ri_l4', 'eol_comm_ib_l1', 'eol_comm_ib_l2', 'eol_comm_ib_l3', 'eol_comm_ib_l4', 'flag_eol_rr_l1', 'flag_eol_rr_l2', 'flag_eol_rr_l3', 'flag_eol_rr_l4'];


    // Tax and Commission Tables
    protected static $fillables = ['qs_comm_ri_quota', 'qs_comm_ri_surplus_1', 'qs_comm_ri_surplus_2', 'qs_comm_ri_surplus_3', 'qs_tax_ri_quota', 'qs_tax_ri_surplus_1', 'qs_tax_ri_surplus_2', 'qs_tax_ri_surplus_3', 'qs_tax_ib_quota', 'qs_tax_ib_surplus_1', 'qs_tax_ib_surplus_2', 'qs_tax_ib_surplus_3', 'flag_qs_comm_scale_quota', 'flag_qs_comm_scale_surplus_1', 'flag_qs_comm_scale_surplus_2', 'flag_qs_comm_scale_surplus_3', 'eol_min_n_deposit_amt_l1', 'eol_min_n_deposit_amt_l2', 'eol_min_n_deposit_amt_l3', 'eol_min_n_deposit_amt_l4', 'eol_premium_mode_l1', 'eol_premium_mode_l2', 'eol_premium_mode_l3', 'eol_premium_mode_l4', 'eol_min_rate_l1', 'eol_min_rate_l2', 'eol_min_rate_l3', 'eol_min_rate_l4', 'eol_max_rate_l1', 'eol_max_rate_l2', 'eol_max_rate_l3', 'eol_max_rate_l4', 'eol_fixed_rate_l1', 'eol_fixed_rate_l2', 'eol_fixed_rate_l3', 'eol_fixed_rate_l4', 'eol_loading_factor_l1', 'eol_loading_factor_l2', 'eol_loading_factor_l3', 'eol_loading_factor_l4', 'eol_tax_ri_l1', 'eol_tax_ri_l2', 'eol_tax_ri_l3', 'eol_tax_ri_l4', 'eol_comm_ib_l1', 'eol_comm_ib_l2', 'eol_comm_ib_l3', 'eol_comm_ib_l4', 'flag_eol_rr_l1', 'flag_eol_rr_l2', 'flag_eol_rr_l3', 'flag_eol_rr_l4'];

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

    public function get($treaty_id)
    {
        return parent::find_by(['treaty_id' => $treaty_id]);
    }

    // ----------------------------------------------------------------

    /**
     * Save Tax & Commission Configuration of a Treaty
     *
     * @param integer $treaty_id Treaty ID
     * @param array $data
     * @return bool
     */
    public function save($treaty_id, $data)
    {
        // Get only fillable fields
        $fillable_data = [];
        foreach( self::$fillables as $col )
        {
            $fillable_data[$col] = $data[$col] ?? NULL;
        }

        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Old Data - For Audit Record
            $where = ['treaty_id' => $treaty_id];
            $this->audit_old_record = parent::find_by($where);

            // Save Database without MY Model ( as it can not find audit_old_record where there is no PK as id)
            $this->db->where('treaty_id', $treaty_id)
                     ->set($fillable_data)
                     ->update($this->table_name);

            // Save Audit Log
            $this->save_audit_log([
                'method' => 'update',
                'id'     => NULL,
                'fields' => $fillable_data
            ], $where);

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;

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

    public function delete($treaty_id)
    {
        $status         = FALSE;
        $old_record     = parent::find_by(['treaty_id' => $treaty_id]);

        if($old_record)
        {
            // Delete The Records
            $status = $this->db->where(['treaty_id' => $treaty_id])
                                ->delete($this->table_name);

            // Manually Audit Log
            $this->audit_old_record = $old_record;
            $this->save_audit_log([
                'method' => 'delete',
                'id' => NULL
            ]);
            $this->audit_old_record = NULL;
        }

        return $status;
    }
}