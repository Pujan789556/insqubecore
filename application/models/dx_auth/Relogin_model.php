<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Re-Login Tracking Model
 *
 * This model is used to track whether the user required re-login.
 * This is necessary when we do the following activities related to a
 * user:
 * 		a. change password
 * 		b. change role
 * 		c. change permissions of a role ( all users belonging to this role must re-login)
 */
class Relogin_model extends MY_Model
{
    protected $table_name = 'auth_relogin';

    protected $set_created = false;

    protected $set_modified = false;

    protected $log_user = false;

    protected $protected_attributes = ["user_id"];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["user_id", "flag_re_login", "created_at", "updated_at"];

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
    	$this->_prefix 		= $this->config->item('DX_table_prefix');
		$this->table_name 	= $this->_prefix.$this->config->item('DX_relogin_table');

        parent::__construct();
    }


    // ----------------------------------------------------------------

    /**
     * Update Flag By User
     *
     * @param integer $user_id
     * @param integer $flag 	1|0
     * @return boolean
     */
    public function update_by_user($user_id, $flag)
    {
    	// Update Flag
    	$done = $this->db->where('user_id', $user_id)
    			 		->update($this->table_name, ['flag_re_login' => $flag]);

	 	// Delete Cache Key
	 	$cache_key = 'rl_u_' . $user_id;
	 	if($done)
	 	{
	 		$this->clear_cache($cache_key);
	 	}

	 	return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Update Flag By Role
     *
     * @param integer $role_id
     * @param integer $flag 	1|0
     * @return boolean
     */
    public function update_by_role($role_id, $flag)
    {
    	// Find all the user_ids of this roles
    	$this->load->model('user_model');
    	$user_list = $this->user_model->user_ids_by_role($role_id);

    	$user_ids = [];
    	$cache_keys = [];
    	if($user_list)
    	{
    		foreach($user_list as $u)
    		{
    			$user_ids[] 	= $u->id;
    			$cache_keys[] 	= 'rl_u_' . $u->id;
    		}
    	}

    	$done = FALSE;
    	if($user_ids)
    	{
    		// Update Flag
    		$done = $this->db->where_in('user_id', $user_ids)
    			 			->update($this->table_name, ['flag_re_login' => $flag]);
    	}

	 	if($done)
	 	{
	 		$this->clear_cache($cache_keys);
	 	}

	 	return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Update All Records
     *
     * Update all users so that all need to relogin to perform.
     * This method is called when you revoke all permissions of all roles
     *
     * @param integer $flag     1|0
     * @return boolean
     */
    public function update_flag_all($flag)
    {

        $done = $this->db->update($this->table_name, ['flag_re_login' => $flag]);
        $cache_key = 'rl_u_*';
        if($done)
        {
            $this->clear_cache($cache_key);
        }
        return $done;
    }

    // --------------------------------------------------------------------


    /**
     * Get Single Record By User ID
     */
    public function get_by_user($user_id)
    {
        $cache_key = 'rl_u_' . $user_id;

        /**
         * Get Cached Result, If no, cache the query result
         */
        $record = $this->get_cache($cache_key);
        if(!$record)
        {
            $record = $this->find_by(['user_id' => $user_id]);
            $this->write_cache($record, $cache_key, CACHE_DURATION_DAY);
        }

        return $record;
    }



	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($keys=null)
    {
        $cache_names = [];

        if($keys)
        {
        	if(  !is_array($keys) )
        	{
        		$cache_names = [$keys];
        	}
        	else
        	{
        		$cache_names = $keys;
        	}
        }

    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }
}
