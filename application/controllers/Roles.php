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
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $form_elements = [
		[
			'name' 		=> 'name',
	        'label' 	=> 'Name',
	        '_id' 		=> 'name',
	        '_type' 	=> 'text',
	        '_required' => true
		],
		[
			'name' 		=> 'description',
	        'label' 	=> 'Description',
	        '_id' 		=> 'description',
	        '_type'		=> 'text',
	        '_required' => false
		]	
	];

	// --------------------------------------------------------------------

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
		$this->load->model('dx_auth/role_model');

		// Load Activitis Library
		$this->load->library('activity');    
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
				'form_elements' => $this->form_elements,
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
				'form_elements' => $this->form_elements,
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
												'form_elements' => $this->form_elements,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->role_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Admin Constraint?
		$done = false;
		if( !$this->_admin_role_delete_constraint($record->id) )
		{
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
		}
		else{
			$data = [
				'status' => 'error',
				'message' => 'You can not delete Admin Role.'
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
	 * Restrict Admin Role Deletion
	 * 
	 * Admin Role cannot be deleted.
	 * 
	 * @param type $id 
	 * @return type
	 */
	function _admin_role_delete_constraint($id)
    {
    	$id = (int)$id;
    	$data = NULL;
    	if( $id === 2)
    	{
			return TRUE;
    	}
    	return FALSE;
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
    	$all_permissions = $this->config->item('DX_permissions');

    	if( $this->input->post() )
		{
			// Get all Permissions
			$post_data = [];
			foreach($all_permissions as $module => $actions)
			{
				if($this->input->post($module))
				{
					$post_data[$module] = $this->input->post($module);
				}
			}

			if( !empty($post_data) && $this->_valid_permissions($post_data))
			{
				// Validate Permissions
				$json_permissions = json_encode($post_data);

				// Let's Update the Permissions
				if( $this->role_model->update(['permissions' => $json_permissions], $record->id) )
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
		$json_data['form'] = $this->load->view('setup/roles/_form_permissions', 
			[
				'record' 			=> $record,
				'all_permissions' 	=> $all_permissions
			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
    }

    private function _valid_permissions($permissions)
    {
    	// $this->load->config('dx_permissions');    	
    	$all_permissions = $this->config->item('DX_permissions');
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

}