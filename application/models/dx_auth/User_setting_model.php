<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User Settings Model
 *
 * This model contains per user settings flags and various other
 * fields.
 *
 * This model is used to track whether the user required re-login.
 * This is necessary when we do the following activities related to a
 * user:
 * 		a. change password
 * 		b. change role
 * 		c. change permissions of a role ( all users belonging to this role must re-login)
 */
class User_setting_model extends MY_Model
{
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
		$this->table_name 	= $this->_prefix.$this->config->item('DX_user_setting_table');

        parent::__construct();
    }


    // ----------------------------------------------------------------

    /**
     * Update Flag By User
     *
     * @param integer $user_id
     * @param string $flag_name     Flag Column Name
     * @param integer $flag_value   Flag Value [1|0]
     * @return boolean
     */
    public function update_flag_by_user($user_id, $flag_name, $flag_value)
    {
    	// Update Flag
    	$done = $this->db->where('user_id', $user_id)
    			 		->update($this->table_name, [$flag_name => $flag_value]);

	 	// Delete Cache Key
	 	$cache_key = 'usr_stng_' . $user_id;
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
     * @param string $flag_name     Flag Column Name
     * @param integer $flag_value   Flag Value [1|0]
     * @return boolean
     */
    public function update_flag_by_role($role_id, $flag_name, $flag_value)
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
    			$cache_keys[] 	= 'usr_stng_' . $u->id;
    		}
    	}

    	$done = FALSE;
    	if($user_ids)
    	{
    		// Update Flag
    		$done = $this->db->where_in('user_id', $user_ids)
    			 			->update($this->table_name, [$flag_name => $flag_value]);
    	}

	 	if($done)
	 	{
	 		$this->clear_cache($cache_keys);
	 	}

	 	return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Update Specific Flag For all records
     *
     *
     * @param string $flag_name     Flag Column Name
     * @param integer $flag_value   Flag Value [1|0]
     * @return boolean
     */
    public function update_flag_all($flag_name, $flag_value)
    {

        $done = $this->db->update($this->table_name, [$flag_name => $flag_value]);
        $cache_key = 'usr_stng_*';
        if($done)
        {
            $this->clear_cache($cache_key);
        }
        return $done;
    }

    // --------------------------------------------------------------------


    /**
     * Get a Single Record
     */
    public function get($user_id)
    {
        $cache_key = 'usr_stng_' . $user_id;

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
