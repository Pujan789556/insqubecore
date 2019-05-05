<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends MY_Model
{
	protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name", "description", "permissions", "created_at", "created_by", "updated_at", "updated_by"];


    /**
     * Validation Rules
     *
     * We can use model to directly save the form data
     *
     * @var array
     */
    protected  $validation_rules = [
		[
			'field' => 'name',
	        'label' => 'Role Name',
	        'rules' => 'trim|required|max_length[30]|is_unique[auth_roles.name]',
	        '_type'     => 'text',
            '_required' => true
		],
		[
			'field' => 'description',
	        'label' => 'Description',
	        'rules' => 'trim|max_length[255]',
	        '_type'     => 'text'
		]
	];

	/**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 20; // Prevent first 2 records from deletion.

	// --------------------------------------------------------------------

	function __construct()
	{

		// Setup Table ( Using Dx_auth Config to Setup Table Name)
		$this->_prefix = $this->config->item('DX_table_prefix');
		$this->table_name = $this->_prefix.$this->config->item('DX_roles_table');

		parent::__construct();
	}

	// ----------------------------------------------------------------

    /**
     * Revoke all permissions from all roles
     *
     * @return bool
     */
    public function revoke_all_permissions()
    {
        // Get all IDs
        $all_roles = $this->db->select('id')->from($this->table_name)->get()->result();

        // ----------------------------------------------------------------

        $status = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            foreach($all_roles as $single)
            {
                parent::update($single->id, ['permissions' => NULL], TRUE);
            }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------


    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('roles_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'roles_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
    	$records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name;
        }
        return $list;
    }

	// ----------------------------------------------------------------

	public function check_duplicate($name, $id=NULL)
	{
		if( $id )
		{
			$this->db->where('id !=', $id);
		}
		$name = ucfirst(strtolower($name));
		return $this->db->where('name' , $name)
						->count_all_results($this->table_name);
	}

	// ----------------------------------------------------------------

	public function delete($id = NULL)
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
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	$cache_names = [
            'roles_all'
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }




	// Required by DX_auth Library
	function get_role_by_id($role_id)
	{
		$this->db->where('id', $role_id);
		return $this->db->get($this->table_name);
	}
}