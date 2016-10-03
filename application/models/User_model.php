<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model 
{
	public $table; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'role_id', 'branch_id', 'department_id', 'username', 'password', 'email', 'scope', 'contact', 'profile', 'docs', 
    	'banned', 'ban_reason', 'newpass', 'newpass_key', 'newpass_time', 'last_ip', 'last_login'
    ]; 

    public $protected = ['id']; // ...Or you can set an array with the fields that cannot be filled by insert/update

    /**
     * Delete cache on save
     * 
     * @var boolean
     */
    public $delete_cache_on_save = TRUE;

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
		$this->table = $this->_prefix.$this->config->item('DX_users_table');

		$this->_roles_table = $this->_prefix.$this->config->item('DX_roles_table');

		parent::__construct();

		// After Create Callback
        $this->after_create[] = 'log_activity';

        // User Relationship
        $this->has_one['role'] = array('local_key'=>'role_id', 'foreign_key'=>'id', 'foreign_model'=>'Role_model');
        $this->has_one['branch'] = array('local_key'=>'branch_id', 'foreign_key'=>'id', 'foreign_model'=>'Branch_model');
        $this->has_one['department'] = array('local_key'=>'department_id', 'foreign_key'=>'id', 'foreign_model'=>'Department_model');
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
    	$this->db->select('U.id, U.username, U.banned, U.profile, R.name as role_name, B.name as branch_name, D.name as department_name')
    			 ->from($this->table . ' as U')
    			 ->join('auth_roles R', 'U.role_id = R.id')
    			 ->join('master_branches B', 'U.branch_id = B.id')
    			 ->join('master_departments D', 'U.department_id = D.id');


        if(!empty($params))
        {
            // $this->where($params);

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
            }
        }
        return $this->db->limit($this->settings->per_page+1)
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
    	$this->db->select('U.id, U.username, U.banned, U.profile, R.name as role_name, B.name as branch_name, D.name as department_name')
    			 ->from($this->table . ' as U')
    			 ->join('auth_roles R', 'U.role_id = R.id')
    			 ->join('master_branches B', 'U.branch_id = B.id')
    			 ->join('master_departments D', 'U.department_id = D.id');

        return $this->db->where('U.id', $id)
                    ->get()->row();
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
		return $this->with_role('fields:name')	
					->with_branch('fields:name')
					->with_department('fields:name')
					->limit($this->settings->per_page+1)
					->get(['id' => $id]);
    }

    // ----------------------------------------------------------------

	/**
     * Delete Cache on Update
     */
    public function _prep_after_write()
    {
    	$cache_names = [
            'auth_users_all'
        ];
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
            foreach($cache_names as $cache)
            {
                $this->delete_cache($cache);     
            }
        }       
        return TRUE;
    }

    // ----------------------------------------------------------------


	// General function
	
	// function get_all($offset = 0, $row_count = 0)
	// {
	// 	$users_table = $this->table;
	// 	$roles_table = $this->_roles_table;
		
	// 	if ($offset >= 0 AND $row_count > 0)
	// 	{
	// 		$this->db->select("$users_table.*", FALSE);
	// 		$this->db->select("$roles_table.name AS role_name", FALSE);
	// 		$this->db->join($roles_table, "$roles_table.id = $users_table.role_id");
	// 		$this->db->order_by("$users_table.id", "ASC");
			
	// 		$query = $this->db->get($this->table, $row_count, $offset); 
	// 	}
	// 	else
	// 	{
	// 		$query = $this->db->get($this->table);
	// 	}
		
	// 	return $query;
	// }

	function get_user_by_id($user_id)
	{
		$this->db->where('id', $user_id);
		return $this->db->get($this->table);
	}

	function get_user_by_username($username)
	{
		$this->db->where('username', $username);
		return $this->db->get($this->table);
	}
	
	function get_user_by_email($email)
	{
		$this->db->where('email', $email);
		return $this->db->get($this->table);
	}
	
	function get_login($login)
	{
		$this->db->where('username', $login);
		$this->db->or_where('email', $login);
		return $this->db->get($this->table);
	}
	
	function check_ban($user_id)
	{
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		$this->db->where('banned', '1');
		return $this->db->get($this->table);
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
		return $this->db->get($this->table);
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
		return $this->db->get($this->table);
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
		return $this->update($data, $id) && $this->log_activity($id, 'X');
		// return $this->set_user($user_id, $data);
	}
	
	function unban_user($id)
	{
		$id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        } 

		$data = array(
			'banned' 			=> 0,
			'ban_reason' 	=> NULL
		);
		return $this->update($data, $id) && $this->log_activity($id, 'U');
		// return $this->set_user($user_id, $data);
	}
		
	function set_role($user_id, $role_id)
	{
		$data = array(
			'role_id' => $role_id
		);
		return $this->set_user($user_id, $data);
	}

	// User table function

	function create_user($data)
	{
		// echo '<pre>'; print_r($data);exit;
		return $this->insert($data);
	}

	function get_user_field($user_id, $fields)
	{
		$this->db->select($fields);
		$this->db->where('id', $user_id);
		return $this->db->get($this->table);
	}

	function set_user($user_id, $data)
	{
		$this->db->where('id', $user_id);
		return $this->db->update($this->table, $data);
	}
	
	// function delete_user($user_id)
	// {
	// 	$this->db->where('id', $user_id);
	// 	$this->db->delete($this->table);
	// 	return $this->db->affected_rows() > 0;
	// }
	
	// Forgot password function

	function newpass($user_id, $pass, $key)
	{
		$data = array(
			'newpass' 			=> $pass,
			'newpass_key' 	=> $key,
			'newpass_time' 	=> date('Y-m-d h:i:s', time() + $this->config->item('DX_forgot_password_expire'))
		);
		return $this->set_user($user_id, $data);
	}

	function activate_newpass($user_id, $key)
	{
		$this->db->set('password', 'newpass', FALSE);
		$this->db->set('newpass', NULL);
		$this->db->set('newpass_key', NULL);
		$this->db->set('newpass_time', NULL);
		$this->db->where('id', $user_id);
		$this->db->where('newpass_key', $key);
		
		return $this->db->update($this->table);
	}

	function clear_newpass($user_id)
	{
		$data = array(
			'newpass' 			=> NULL,
			'newpass_key' 	=> NULL,
			'newpass_time' 	=> NULL
		);
		return $this->set_user($user_id, $data);
	}
	
	// Change password function

	function change_password($user_id, $new_pass)
	{
		$this->db->set('password', $new_pass);
		$this->db->where('id', $user_id);
		return $this->db->update($this->table) && $this->log_activity($user_id, 'H');;
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
            'module' => 'user',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);     
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
		else
		{
			$this->log_activity($id, 'D');
		}

		// Enable db_debug if on development environment
		$this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

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
    public function update_basic($id, $data)
    {
    	return $this->update($data, $id) && $this->log_activity($id, 'B');
    }

    // ----------------------------------------------------------------

    /**
     * Update User's Contact
     * 
     * @param integer $id 
     * @param array $data 
     * @return bool
     */
    public function update_contact($id, $data)
    {
    	return $this->update($data, $id) && $this->log_activity($id, 'T');
    }

    // ----------------------------------------------------------------
    
    /**
     * Update User's Profile
     * 
     * @param integer $id 
     * @param array $data 
     * @return bool
     */
    public function update_profile($id, $data)
    {
    	return $this->update($data, $id) && $this->log_activity($id, 'P');
    }
	
}