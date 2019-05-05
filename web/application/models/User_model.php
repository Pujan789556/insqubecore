<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{

	// protected $table_name = 'auth_users';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'code', 'role_id', 'branch_id', 'department_id', 'username', 'password', 'email', 'scope', 'contact', 'profile', 'docs', 'banned', 'ban_reason', 'newpass', 'newpass_key', 'newpass_time', 'last_ip', 'last_login', 'flag_re_login', 'flag_back_date', 'created_at', 'created_by', 'updated_at', 'updated_by'];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 1; // Prevent Admin user from being Delete

    // -------------------------------------------------------------------------

	function __construct()
	{
		// Other stuff
		$this->_prefix = $this->config->item('DX_table_prefix');
		$this->table_name = $this->_prefix.$this->config->item('DX_users_table');

		$this->_roles_table = $this->_prefix.$this->config->item('DX_roles_table');

		parent::__construct();

		// Dependant Model
		$this->load->model('address_model');
	}

	public function settings_v_rules()
	{
		return [
	        [
	            'field' => 'flag_re_login',
	            'label' => 'Force Relogin',
	            'rules' => 'trim|integer|in_list[1]',
	            '_type' => 'switch',
	            '_checkbox_value' => '1',
	            '_help_text' => 'If you want this user to re-login immediately, please set this flag ON.'
	        ],
	        [
	            'field' => 'flag_back_date',
	            'label' => 'Enable Back Date',
	            'rules' => 'trim|integer|in_list[1]',
	            '_type' => 'switch',
	            '_checkbox_value' => '1',
	            '_help_text' => 'This will enable user to select back date.'
	        ]
	    ];
	}

	// ----------------------------------------------------------------

	/**
	 * Is Employee Code Available?
	 *
	 * @param alpha-dash $code
	 * @param integer|null $id
	 * @return integer
	 */
	public function is_code_available($code, $id=NULL)
	{
		if( $id )
        {
            $this->db->where('id !=', $id);
        }
        $count = $this->db->where('code', $code)
                        ->count_all_results($this->table_name);

        return $count == 0;
	}

	// ----------------------------------------------------------------


	/**
	 * Get Data Rows
	 *
	 * For data listing purpose
	 *
	 * @param type|array $params
	 * @return type
	 */
	public function rows($params = array())
    {
    	$this->db->select('U.id, U.code, U.username, U.email, U.banned, U.profile, U._profile_name, R.name as role_name, B.name_en AS branch_name_en, B.name_np AS branch_name_np, D.name as department_name')
    			 ->from($this->table_name . ' as U')
    			 ->join('auth_roles R', 'U.role_id = R.id')
    			 ->join('master_branches B', 'U.branch_id = B.id')
    			 ->join('master_departments D', 'U.department_id = D.id');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
            	$this->db->where(['U.id >=' => $next_id]);
            }

            $role_id = $params['role_id'] ?? NULL;
            if( $role_id )
            {
            	$this->db->where(['U.role_id' =>  $role_id]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
            	$this->db->where(['U.branch_id' => $branch_id]);
            }

           	$department_id = $params['department_id'] ?? NULL;
            if( $department_id )
            {
            	$this->db->where(['U.department_id' => $department_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
            	$this->db->like('U.username', $keywords, 'after');
            	$this->db->or_like('U._profile_name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

	/**
	 * Get Dropdown List
	 *
	 *
	 *
	 * @param type|array $params
	 * @return type
	 */
	public function dropdown($branch_id = NULL, $department_id = NULL)
    {
    	/**
         * Get Cached Result, If no, cache the query result
         */
    	$cache_name = ['auth_users_dd', $branch_id ?? '_', $department_id ?? '_'];
    	$cache_name = implode('_', $cache_name);

    	// $this->delete_cache($cache_name);

    	$dropdown = $this->get_cache($cache_name);
        if(!$dropdown)
        {
            $this->db->select('U.id, U.code, U.username, U.email, U.profile, R.name as role_name, B.name_en AS branch_name_en, B.name_np AS branch_name_np, D.name as department_name')
	    			 ->from($this->table_name . ' as U')
	    			 ->join('auth_roles R', 'U.role_id = R.id')
	    			 ->join('master_branches B', 'U.branch_id = B.id')
	    			 ->join('master_departments D', 'U.department_id = D.id');

	        if( $branch_id )
	        {
	        	$this->db->where(['B.id' => $branch_id]);
	        }

	        if( $department_id )
	        {
	        	$this->db->where(['D.id' => $department_id]);
	        }

	        $list = $this->db->get()->result();

	        $dropdown = [];
	        foreach($list as $r)
	        {
	        	$profile = $r->profile ? json_decode($r->profile) : NULL;
	        	$initial = $r->branch_name_en . ' - ';
	        	$initial .= isset($profile->name) ? $profile->name . " ({$r->code})" : $r->username . " ({$r->code})";
	        	$dropdown["{$r->id}"] = $initial;
	        }
            $this->write_cache($dropdown, $cache_name, CACHE_DURATION_MONTH);
        }
        return $dropdown;
    }

    // ----------------------------------------------------------------

	/**
	 * List all users with core attributes
	 *
	 * @return array
	 */
	public function all_core()
    {
    	return $this->db->select('U.id, U.code, U.username, U.email, U.profile')
	    			 ->from($this->table_name . ' as U')
	    			 ->get()
	    			 ->result();
    }

    // ----------------------------------------------------------------

	/**
	 * Get Data Rows
	 *
	 * For data listing purpose
	 *
	 * @param type|array $params
	 * @return type
	 */
	public function user_ids_by_role($role_id)
    {
    	return $this->db->select('U.id')
	    			 ->from($this->table_name . ' as U')
	    			 ->where('U.role_id', $role_id)
	    			 ->get()->result();
    }

    // ----------------------------------------------------------------

    /**
     * Single Row on Basic Information Edit
     *
     * @param int $id
     * @return object
     */
	public function row($id)
    {
    	$this->db->select('U.id, U.code, U.username, U.email, U.banned, U.profile, R.name as role_name, B.name_en AS branch_name_en, B.name_np AS branch_name_np, D.name as department_name')
    			 ->from($this->table_name . ' as U')
    			 ->join('auth_roles R', 'U.role_id = R.id')
    			 ->join('master_branches B', 'U.branch_id = B.id')
    			 ->join('master_departments D', 'U.department_id = D.id');

        return $this->db->where('U.id', $id)
                    ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Loggedin User Rrecord
     *
     * @param integer $id
     * @return object
     */
    public function get_loggedin_user($id)
    {
    	/**
         * Get Cached Result, If no, cache the query result
         */
    	$cache_name = 'auth_users_l_' . $id;

    	$record = $this->get_cache($cache_name);
        if(!$record)
        {
            $this->db->select('U.id, U.code, U.username, U.email, U.banned, U.profile, U.contact, R.name AS role_name, B.name_en AS branch_name_en, B.name_np AS branch_name_np, D.name AS department_name')
						->from($this->table_name . ' AS U')
						->join('auth_roles R', 'U.role_id = R.id')
						->join('master_branches B', 'U.branch_id = B.id')
    			 		->join('master_departments D', 'U.department_id = D.id');


			// Include Branch Address Information
            $table_aliases = [
                // Address Table Alias
                'address' => 'ADR',

                // Country Table Alias
                'country' => 'CNTRY',

                // State Table Alias
                'state' => 'STATE',

                // Local Body Table Alias
                'local_body' => 'LCLBD',

                // Type/Module Table Alias
                'module' => 'B'
            ];
            $this->address_model->module_select(IQB_ADDRESS_TYPE_BRANCH, NULL, $table_aliases);


            // Get the record
            $record = $this->db->where('U.id', $id)
							   ->get()->row();

            $this->write_cache($record, $cache_name, CACHE_DURATION_HR);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    /**
     * Get User Flags
     *
     * @param integer $id
     * @return object
     */
    public function get_flags($id)
    {
    	/**
         * Get Cached Result, If no, cache the query result
         */
    	$cache_name = 'auth_users_flags_' . $id;

    	$record = $this->get_cache($cache_name);
        if(!$record)
        {
            $record = $this->db->select('U.flag_re_login, U.flag_back_date')
								->from($this->table_name . ' AS U')
								->where('U.id', $id)
								->get()->row();

            $this->write_cache($record, $cache_name, CACHE_DURATION_HR);
        }
        return $record;
    }

    // --------------------------------------------------------------------

    /**
     * Get Flag Value
     *
     * @param integer $id
     * @param string $flag_name
     * @return mixed
     */
    public function flag_enabled($id, $flag_name)
    {
        $enabled = FALSE;

        $record = $this->get_flags($id);
        if($record && isset($record->{$flag_name}) )
        {
            $enabled = (int)$record->{$flag_name} === IQB_FLAG_ON;
        }
        return $enabled;
    }

    // ----------------------------------------------------------------

    /**
     * Get Details of a Single Record
     *
     * @param int $id
     * @return object
     */
	public function details($id)
    {
    	$this->db->select('U.id, U.code, U.username, U.banned, U.profile, U.contact, R.name as role_name, B.name_en AS branch_name_en, B.name_np AS branch_name_np, D.name as department_name')
    			 ->from($this->table_name . ' as U')
    			 ->join('auth_roles R', 'U.role_id = R.id')
    			 ->join('master_branches B', 'U.branch_id = B.id')
    			 ->join('master_departments D', 'U.department_id = D.id');

        return $this->db->where('U.id', $id)
                    ->get()->row();
    }

    // ----------------------------------------------------------------

	/**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	$cache_names = [
            'auth_users_dd*',
            'auth_users_l_*',
            'auth_users_flags_*'
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------


	function get_user_by_id($user_id)
	{
		$this->db->where('id', $user_id);
		return $this->db->get($this->table_name);
	}

	function get_user_by_username($username)
	{
        return $this->db->select('U.*, R.name as role_name, B.code as branch_code, D.code as department_code')
                        ->from($this->table_name . ' U')
                        ->join($this->_roles_table . ' R', 'R.id = U.role_id')
                        ->join('master_branches B', 'B.id = U.branch_id')
                        ->join('master_departments D', 'D.id = U.department_id')
                        ->where('U.username', $username)
                        ->get();

		// $this->db->where('username', $username);
		// return $this->db->get($this->table_name);
	}

	function get_user_by_email($email)
	{
        return $this->db->select('U.*, R.name as role_name, B.code as branch_code, D.code as department_code')
                        ->from($this->table_name . ' U')
                        ->join($this->_roles_table . ' R', 'R.id = U.role_id')
                        ->join('master_branches B', 'B.id = U.branch_id')
                        ->join('master_departments D', 'D.id = U.department_id')
                        ->where('U.email', $email)
                        ->get();

		// $this->db->where('email', $email);
		// return $this->db->get($this->table_name);
	}

	function get_login($login)
	{
        return $this->db->select('U.*, R.name as role_name, B.code as branch_code, D.code as department_code')
                        ->from($this->table_name . ' U')
                        ->join($this->_roles_table . ' R', 'R.id = U.role_id')
                        ->join('master_branches B', 'B.id = U.branch_id')
                        ->join('master_departments D', 'D.id = U.department_id')
                        ->where('U.username', $login)
                        ->or_where('U.email', $login)
                        ->get();

		// $this->db->where('username', $login);
		// $this->db->or_where('email', $login);
		// return $this->db->get($this->table_name);
	}

	function check_ban($user_id)
	{
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		$this->db->where('banned', '1');
		return $this->db->get($this->table_name);
	}

	function check_username($username, $id=NULL)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(username)=', strtolower($username));

		// Edit mode (id supplied)?
		if( $id )
		{
			$this->db->where('id !=', $id);
		}
		return $this->db->get($this->table_name);
	}

	function check_email($email, $id=NULL)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(email)=', strtolower($email));

		// Edit mode (id supplied)?
		if( $id )
		{
			$this->db->where('id !=', $id);
		}
		return $this->db->get($this->table_name);
	}

	function ban_user($id, $reason = NULL)
	{
		$id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

		$data = array(
			'banned' 			=> 1,
			'ban_reason' 	=> $reason
		);
		return parent::update($id, $data, TRUE);
	}

	function unban_user($id)
	{
		$id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

		$data = array(
			'banned' 		=> 0,
			'ban_reason' 	=> NULL
		);
		return parent::update($id, $data, TRUE);
	}

	function set_role($id, $role_id)
	{
		$data = array(
			'role_id' => $role_id
		);
		return parent::update($id, $data, TRUE);
	}

	// User table function

	function create_user($data)
	{
		return parent::insert($data, TRUE);
	}

	function get_user_field($user_id, $fields)
	{
		$this->db->select($fields);
		$this->db->where('id', $user_id);
		return $this->db->get($this->table_name);
	}

	function set_user($user_id, $data)
	{
		$this->db->where('id', $user_id);
		return $this->db->update($this->table_name, $data);
	}


	// Forgot password function

	function newpass($id, $pass, $key)
	{
		$data = array(
			'newpass' 		=> $pass,
			'newpass_key' 	=> $key,
			'newpass_time' 	=> date('Y-m-d h:i:s', time() + $this->config->item('DX_forgot_password_expire'))
		);
		return parent::update($id, $data, TRUE);
	}

	function activate_newpass($id, $key)
	{
		$user = parent::find_by(['id' => $id, 'newpass_key' => $key]);
		if( !$user )
		{
			return FALSE;
		}

		$data = [
			'password' 		=> $user->newpass, // Set new password as The Password
			'newpass' 		=> NULL,
			'newpass_key' 	=> NULL,
			'newpass_time' 	=> NULL
		];
		return parent::update($id, $data, TRUE);
	}

	function clear_newpass($id)
	{
		$data = array(
			'newpass' 		=> NULL,
			'newpass_key' 	=> NULL,
			'newpass_time' 	=> NULL
		);
		return parent::update($id, $data, TRUE);
	}

	// Change password function
	function change_password($id, $new_pass)
	{
		$data = [
			'password' 		=> $new_pass,
			'flag_re_login' => IQB_FLAG_ON
		];

		return parent::update($id, $data, TRUE);
	}

    // ----------------------------------------------------------------

	function force_relogin($id)
	{
		$data = [
			'flag_re_login' => IQB_FLAG_ON
		];

		return parent::update($id, $data, TRUE);
	}

	// ----------------------------------------------------------------

	function force_relogin_by_role($role_id, $use_automatic_transaction = TRUE)
	{
		$result = $this->db->select('id')
							->from($this->table_name)
							->where('role_id', $role_id)
							->get();

		$users = $result ? $result->result() : [];
		$data = [
			'flag_re_login' => IQB_FLAG_ON
		];

		// ----------------------------------------------------------------

		$status = TRUE;
		// Use automatic transaction
		if($use_automatic_transaction)
		{
			$this->db->trans_start();
		}
			foreach($users as $user)
			{
				parent::update($user->id, $data, TRUE);
			}

		if($use_automatic_transaction)
		{
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
		        // get_allenerate an error... or use the log_message() function to log your error
				$status = FALSE;
			}
		}

		// return result/status
		return $status;
	}

	// ----------------------------------------------------------------

	function reset_relogin($id)
	{
		$data = [
			'flag_re_login' => IQB_FLAG_OFF
		];

		return parent::update($id, $data, TRUE);
	}

    // ----------------------------------------------------------------

	/**
	 * Delete User
	 *
	 * @param integer|null $id
	 * @return bool
	 */
	public function delete_user($id = NULL)
	{
		$id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

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

		// return result/status
		return $status;
	}

     // ----------------------------------------------------------------

    /**
     * Update User's Basic Information
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function update_basic($id, $data, $skip_validation=TRUE)
    {
    	return parent::update($id, $data,  $skip_validation);
    }

    // ----------------------------------------------------------------

    /**
     * Update User's Contact
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function update_contact($id, $data, $skip_validation=TRUE)
    {
    	return parent::update($id, $data, $skip_validation);
    }

    // ----------------------------------------------------------------

    /**
     * Update User's Profile
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function update_profile($id, $data, $skip_validation=TRUE)
    {
    	return parent::update($id, $data, $skip_validation);
    }

    // ----------------------------------------------------------------

    /**
     * Update User's Settings
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function update_settings($id, $data, $skip_validation=TRUE)
    {
    	return parent::update($id, $data, $skip_validation);
    }

    // ----------------------------------------------------------------

    /**
     * Force all user to re-login
     *
     * @return bool
     */
    public function force_relogin_all($use_automatic_transaction = TRUE)
    {
        // Get all IDs
        $all_users = $this->db->select('id')->from($this->table_name)->get()->result();

        // ----------------------------------------------------------------

        $status = TRUE;

        if($use_automatic_transaction)
        {
        	// Use automatic transaction
	        $this->db->trans_start();
        }
        	// Update one by one
            foreach($all_users as $single)
            {
                parent::update($single->id, ['flag_re_login' => IQB_FLAG_ON], TRUE);
            }

        if($use_automatic_transaction)
        {
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE)
	        {
	            // generate an error... or use the log_message() function to log your error
	            $status = FALSE;
	        }
	    }

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    /**
     * Revoke all Backdate
     *
     * @return bool
     */
    public function revoke_all_backdate($use_automatic_transaction = TRUE)
    {
        // Get all IDs
        $all_users = $this->db->select('id')->from($this->table_name)->get()->result();

        // ----------------------------------------------------------------

        $status = TRUE;

        if($use_automatic_transaction)
        {
        	// Use automatic transaction
	        $this->db->trans_start();
        }
        	// Update one by one
            foreach($all_users as $single)
            {
                parent::update($single->id, ['flag_back_date' => IQB_FLAG_OFF], TRUE);
            }

        if($use_automatic_transaction)
        {
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE)
	        {
	            // generate an error... or use the log_message() function to log your error
	            $status = FALSE;
	        }
	    }

        // return result/status
        return $status;
    }

}