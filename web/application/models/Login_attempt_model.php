<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_attempt_model extends MY_Model
{
    protected $table_name = 'auth_login_attempts';

    protected $set_created = false;

    protected $set_modified = false;

    protected $log_user = false;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'ip_address', 'time'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default  = FALSE;
    public static $protect_max_id   = 0; // Prevent first 12 records from deletion.

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

    function check_attempts($ip_address)
    {
        $this->db->select('1', FALSE);
        $this->db->where('ip_address', $ip_address);
        return $this->db->get($this->table_name);
    }

    // ----------------------------------------------------------------

    // Increase attempts count
    function increase_attempt($ip_address)
    {
        // Insert new record
        $data = array(
            'ip_address' => $ip_address
        );

        return parent::insert($data, TRUE);
    }

    // ----------------------------------------------------------------


    // Clear Login attempts
    public function clear_attempts($ip_address)
    {
        return $this->delete_by_ip($ip_address);
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('login_attempts_all');
        if(!$list)
        {
            $list = $this->get_by_ip();
            $this->write_cache($list, 'login_attempts_all', CACHE_DURATION_HR);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_by_ip()
    {
        return $this->db->select('ip_address, count(*) AS attemtps')
                        ->from($this->table_name)
                        ->group_by('ip_address')
                        ->order_by('id', 'desc')
                        ->get()
                        ->result();
    }


	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'login_attempts_all'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_by_ip($ip_address)
    {

        $records = $this->db->select('id')
                            ->from($this->table_name)
                            ->where('ip_address', $ip_address)
                            ->get()->result();
        // ----------------------------------------------------------------

        $status = TRUE;
        if($records)
        {
            // Use automatic transaction
            $this->db->trans_start();

                foreach($records as $single)
                {
                    parent::delete($single->id); // For Audit Log, we delete one by one
                }

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // get_allenerate an error... or use the log_message() function to log your error
                $status = FALSE;
            }
            else
            {
                $this->clear_cache();
            }
        }


        // return result/status
        return $status;
    }
}