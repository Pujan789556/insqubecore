<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Roles Controller
 * 
 * This controller falls under "Master Setup" category.
 *  
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Roles extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		
		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Form Validation
		$this->load->library('Form_validation');				
	
		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | Roles & Permissions';

        // Setup Navigation        
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'security',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('role_model');		
	}
	
	// --------------------------------------------------------------------

	/**
	 * Default Method
	 * 
	 * Render the settings
	 * 
	 * @return type
	 */
	function index()
	{
		/**
		 * Normal Form Render
		 */
		// this will generate cache name: mc_auth_roles_all
		$records = $this->role_model->set_cache('all')->get_all();
	
		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Roles & Permissions',
								'breadcrumbs' => ['Master Setup' => NULL, 'Roles' => NULL]
						])
						->partial('content', 'setup/roles/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Role
	 * 
	 * 
	 * @param integer $id 
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->role_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);
		

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/roles/_form', 
			[
				'form_elements' => $this->role_model->rules['insert'],
				'record' 		=> $record
			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Role
	 * 
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');

		
		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/roles/_form', 
			[
				'form_elements' => $this->role_model->rules['insert'],
				'record' 		=> $record
			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 * 
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;
			

			// Insert or Update?
			if($action === 'add')
			{
				$done = $this->role_model->from_form()->insert();

				// @NOTE: Activity Log will be automatically inserted
			}
			else
			{
				// Update Validation Rule on Update
				$this->role_model->rules['insert'][0]['rules'] = 'trim|required|max_length[30]|callback_check_duplicate';
				
				
				// Now Update Data
				$extra_data = $this->_admin_role_edit_constraint($record->id);
	        	$done = $this->role_model->from_form(NULL, $extra_data)->update(NULL, $record->id) && $this->role_model->log_activity($record->id, 'E');
			}
			

        	if(!$done)
			{
				$status = 'error';
				$message = 'Validation Error.';				
			}
			else
			{
				$status = 'success';
				$message = 'Successfully Updated.';				
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->role_model->set_cache('all')->get_all();
					$success_html = $this->load->view('setup/roles/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->role_model->get($record->id);
					$success_html = $this->load->view('setup/roles/_single_row', ['record' => $record], TRUE);
				}
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success' 
										? 	[
												'box' 	=> $action === 'add' 
															? '#iqb-data-list' 
															: '#_data-row-' . $record->id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												// 
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error' 
									? 	$this->load->view('setup/roles/_form', 
											[
												'form_elements' => $this->role_model->rules['insert'],
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Role
	 * 
	 * @param integer $id 
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->role_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Role_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->role_model->delete($record->id);
		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->id
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be deleted. It might have references to other module(s)/component(s).'
			];
		}

		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Restrict Admin Role Modification
	 * 
	 * Admin role name can not be changed as it is associated with dx_auth 
	 * is_admin method. Only description can be changed if this role is being 
	 * edited.
	 * 
	 * @param type $id 
	 * @return type
	 */
	function _admin_role_edit_constraint($id)
    {
    	$id = (int)$id;
    	$data = NULL;
    	if( $id === 2)
    	{
			$data['name'] = 'Admin';
    	}
    	return $data;
    }

    // --------------------------------------------------------------------

    /**
     * Check Duplicate Callback
     * 
     * @param string $name 
     * @param integer|null $id 
     * @return bool
     */	
    public function check_duplicate($name, $id=NULL){

    	$name = $name ? $name : $this->input->post('name');
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->role_model->check_duplicate($name, $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Manage Permissions
     * 
     * Manage permissions per role basis
     * 
     * @param integer $id Role ID
     * @return json
     */
    public function permissions($id)
    {
    	// Valid Record ?
		$id = (int)$id;
		$record = $this->role_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

    	$this->load->config('dx_permissions');
    	if( $this->input->post() )
		{
			// All permissions
			$all_permissions = $this->_get_all_permissions(); 
			
			// Get all Permissions
			$post_data = [];
			foreach($all_permissions as $module => $actions)
			{
				if($this->input->post($module))
				{
					$post_data[$module] = $this->input->post($module);
				}
			}

			if( $this->_valid_permissions($post_data))
			{
				// Validate Permissions
				$json_permissions = $post_data ? json_encode($post_data) : NULL;

				// Let's Update the Permissions
				if( $this->role_model->update(['permissions' => $json_permissions], $record->id) && $this->role_model->log_activity($record->id, 'P'))
				{
					$status = 'success';
					$message = 'Successfully updated.';
				}
				else
				{
					$status = 'error';
					$message = 'Could not be updated.';
				}
			}
			else
			{
				$status = 'error';
				$message = 'Invalid permissions.';
			}

			$this->template->json([
				'status' => $status,
				'message' => $message,
				'hideBootbox' => $status === 'success' // Hide bootbox on Success
			]);
		}

    	// Let's load the form
    	$permission_configs = $this->config->item('DX_permissions');
		$json_data['form'] = $this->load->view('setup/roles/_form_permissions', 
			[
				'record' 			=> $record,
				'permission_configs' 	=> $permission_configs
			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Valid Permissions?
     * 
     * Permission Validation Method
     * This method will check submitted permissions against config permissions
     * 
     * @param array $permissions 
     * @return boolean
     */
    private function _valid_permissions($permissions)
    {
    	// Extract All Original Permission
    	$all_permissions = $this->_get_all_permissions();
    	
    	// Check passed permissions against original permissions
    	foreach($permissions as $module => $actions)
    	{
    		$master_actions = $all_permissions[$module];

    		// Check the difference against master actions
    		$diff = array_diff($actions, $master_actions);
    		
    		if( !empty($diff) )
    		{
    			// We have a problem
    			return FALSE;
    		}
    	}
    	return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Get All Permissions
     * 
     * Returns all the config permissions
     * 	
     * @return array
     */
    private function _get_all_permissions()
    {
    	$permission_configs = $this->config->item('DX_permissions');

    	$all_permission_data = [];
    	foreach($permission_configs as $section=>$modules)
    	{
    		foreach($modules as $module=>$actions)
    		{
    			$all_permission_data[$module] = $actions;
    		}    		
    	}  
    	return $all_permission_data;
    }

    // --------------------------------------------------------------------

    /**
     * Revoke All Permissions
     * 
     * Reset all role permissions i.e. It will clear out all permissions assigned to 
     * all roles
     * 
     * @return json
     */
    public function revoke_all_permissions()
    {
    	if($this->role_model->update(['permissions' => NULL]) && $this->role_model->log_activity(NULL, 'R'))
    	{
    		$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully revoked all role-permissions!'
			];

			// @TODO: Log activity
    	}
    	else
    	{
    		$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be updated!'
			];
		}
		return $this->template->json($data);
	}
}