<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends MY_Model
{
	protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

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

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete|Assign
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id=NULL, $action = 'C')
    {
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'role',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }




	// Required by DX_auth Library
	function get_role_by_id($role_id)
	{
		$this->db->where('id', $role_id);
		return $this->db->get($this->table_name);
	}
}