<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_broker_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaty_brokers';

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

    protected $fields = ['treaty_id', 'company_id'];

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
     * Add Brokers for a Treaty
     *
     * @param int $treaty_id Treaty ID
     * @param array $broker_ids Company IDs
     * @return bool
     */
    public function add_brokers($treaty_id, $broker_ids)
    {
        $done  = TRUE;


        // Insert Individual - No batch-insert - because of Audit Log Requirement
        foreach($broker_ids as $company_id )
        {
            $single_data = [
                'treaty_id'     => $treaty_id,
                'company_id'    => $company_id
            ];
            parent::insert($single_data, TRUE);
        }

        // return result/status
        return $done;
    }

    // --------------------------------------------------------------------

    public function get_many_by_treaty($treaty_id)
    {
        return $this->db->select('TB.treaty_id, TB.company_id, C.name_en, C.picture, C.pan_no, C.active, C.type')
                        ->from($this->table_name . ' AS TB')
                        ->join('master_companies C', 'C.id = TB.company_id')
                        ->where('TB.treaty_id', $treaty_id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function broker_dropdown($treaty_id)
    {
        $list = $this->get_many_by_treaty($treaty_id);
        $brokers = [];
        foreach($list as $record)
        {
            $brokers["{$record->company_id}"] = $record->name_en;
        }
        return $brokers;
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

    public function delete_brokers($treaty_id, $broker_ids)
    {
        $status         = FALSE;
        $old_records    = [];
        if($broker_ids)
        {
            $old_records = $this->db->where('treaty_id', $treaty_id)
                                    ->where_in('company_id', $broker_ids)
                                    ->get($this->table_name)->result();
        }

        if($old_records)
        {
            // Delete The Records
            $status = $this->db->where('treaty_id', $treaty_id)
                             ->where_in('company_id', $broker_ids)
                             ->delete($this->table_name);

            // Manually Audit Log
            foreach($old_records as $single)
            {
                $this->audit_old_record = $single;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id' => NULL
                ]);
                $this->audit_old_record = NULL;
            }
        }

        return $status;
    }
}