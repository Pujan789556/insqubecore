<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_agent_policy_model extends MY_Model
{
    protected $table_name = 'rel_agent__policy';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ["agent_id", "policy_id"];

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

    public function insert_or_update($data)
    {
        $policy_id = $data['policy_id'];
        $agent_id = $data['agent_id'] ?? NULL;

        if($agent_id)
        {
            // Do we have exisiting record? Simply update agent id
            $where = [
                'policy_id' => $policy_id
            ];
            $row = $this->find_by($where);

            if( !$row)
            {
                // No Record, Insert
                return $this->insert($data);
            }
            else if($row && $row->agent_id != $agent_id)
            {
                // Has record and Different agent ID
                return $this->update_by($where, ['agent_id' => $agent_id]);
            }
            else
            {
                return TRUE;
            }
        }
        return FALSE;
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