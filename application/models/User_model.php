<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model 
{
	public $table; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	// 'name', 'description', 'permissions'
    ]; 

    public $protected = ['id']; // ...Or you can set an array with the fields that cannot be filled by insert/update

    /**
     * Delete cache on save
     * 
     * @var boolean
     */
    public $delete_cache_on_save = TRUE;

    // -------------------------------------------------------------------------

	function __construct()
	{
		// Other stuff
		$this->_prefix = $this->config->item('DX_table_prefix');
		$this->table = $this->_prefix.$this->config->item('DX_users_table');

		$this->_roles_table = $this->_prefix.$this->config->item('DX_roles_table');

		parent::__construct();
	}

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
	
	function check_username($username)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(username)=', strtolower($username));
		return $this->db->get($this->table);
	}

	function check_email($email)
	{
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(email)=', strtolower($email));
		return $this->db->get($this->table);
	}
		
	function ban_user($user_id, $reason = NULL)
	{
		$data = array(
			'banned' 			=> 1,
			'ban_reason' 	=> $reason
		);
		return $this->set_user($user_id, $data);
	}
	
	function unban_user($user_id)
	{
		$data = array(
			'banned' 			=> 0,
			'ban_reason' 	=> NULL
		);
		return $this->set_user($user_id, $data);
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
		// $data['created'] = date('Y-m-d H:i:s', time());

		// Timestamps and Creators
		timestamp_create($data);
		
		return $this->db->insert($this->table, $data);
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
	
	function delete_user($user_id)
	{
		$this->db->where('id', $user_id);
		$this->db->delete($this->table);
		return $this->db->affected_rows() > 0;
	}
	
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
		return $this->db->update($this->table);
	}
	
}