<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends MY_Model
{
	public $table; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'name', 'description', 'permissions'
    ]; 

    public $protected = ['id']; // ...Or you can set an array with the fields that cannot be filled by insert/update

    /**
     * Delete cache on save
     * 
     * @var boolean
     */
    public $delete_cache_on_save = TRUE;


    /**
     * Validation Rules
     * 
     * We can use model to directly save the form data
     * 
     * @var array
     */
    public  $rules = [
		'insert' => [
			[
				'field' => 'name',
		        'label' => 'Name',
		        'rules' => 'trim|required|max_length[30]|is_unique[auth_roles.name]'
			],
			[
				'field' => 'description',
		        'label' => 'Description',
		        'rules' => 'trim|max_length[255]',
			]
		]	
	];

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();
		
		// Setup Table ( Using Dx_auth Config to Setup Table Name)
		$this->_prefix = $this->config->item('DX_table_prefix');
		$this->table = $this->_prefix.$this->config->item('DX_roles_table');

		// After Create Callback
		// $this->after_create[] = 'log_activity';		
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
						->count_all_results($this->table);
	}

	// ----------------------------------------------------------------
	
	public function delete($id = NULL)
	{
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

	/**
     * Delete Cache on Update
     */
    public function _prep_after_write()
    {
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
        	$this->delete_cache('auth_roles_all'); 
        }       
        return TRUE;
    }

    // ----------------------------------------------------------------
    
    public function log_activity($id, $action = 'C')
    {
    	return TRUE;

	  //   	$action = is_string($action) ? $action : 'C';
	  //   	// Save Activity Log
			// $activity_log = [
			// 	'module' => 'role',
			// 	'module_id' => $id,
			// 	'action' => $action
			// ];
			// return $this->activity->save($activity_log);		
    }

	
	// function get_all()
	// {
	// 	$this->db->order_by('id', 'asc');
	// 	return $this->db->get($this->_table);
	// }
	
	// function get_role_by_id($role_id)
	// {
	// 	$this->db->where('id', $role_id);
	// 	return $this->db->get($this->_table);
	// }
	
	// function create_role($name, $parent_id = 0)
	// {
	// 	$data = array(
	// 		'name' => $name,
	// 		'parent_id' => $parent_id
	// 	);
            
	// 	$this->db->insert($this->_table, $data);
	// }
	
	// function delete_role($role_id)
	// {
	// 	$this->db->where('id', $role_id);
	// 	$this->db->delete($this->_table);		
	// }
}