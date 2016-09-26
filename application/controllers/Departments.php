<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Departments Controller
 * 
 * This controller falls under "Master Setup" category.
 *  
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Departments extends MY_Controller
{
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $form_elements = [
		[
			'name' 		=> 'name',
	        'label' 	=> 'Department Name',
	        '_id' 		=> 'name',
	        '_type' 	=> 'text',
	        '_required' => true
		],
		[
			'name' 		=> 'code',
	        'label' 	=> 'Department Code',
	        '_id' 		=> 'code',
	        '_type'		=> 'text',
	        '_required' => true
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
        $this->data['site_title'] = 'Master Setup | Departments';

        // Setup Navigation        
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('department_model');		  
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
		// this will generate cache name: mc_master_departments_all
		$records = $this->department_model->set_cache('all')->get_all();
	
		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Departments',
								'breadcrumbs' => ['Master Setup' => NULL, 'Departments' => NULL]
						])
						->partial('content', 'setup/departments/_index', compact('records'))
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
		$record = $this->department_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);
		

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/departments/_form', 
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
		$json_data['form'] = $this->load->view('setup/departments/_form', 
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
				$done = $this->department_model->from_form()->insert();

				// @NOTE: Activity Log will be automatically inserted
			}
			else
			{
				// Update Validation Rule on Update
				$this->department_model->rules['insert'][1]['rules'] = 'trim|required|max_length[5]|callback_check_duplicate';
				
				
				// Now Update Data
				$done = $this->department_model->from_form()->update(NULL, $record->id) && $this->department_model->log_activity($record->id, 'E');
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
					$records = $this->department_model->set_cache('all')->get_all();
					$success_html = $this->load->view('setup/departments/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->department_model->get($record->id);
					$success_html = $this->load->view('setup/departments/_single_row', ['record' => $record], TRUE);
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
									? 	$this->load->view('setup/departments/_form', 
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
		$record = $this->department_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Admin Constraint?
		$done = $this->department_model->delete($record->id);
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
     * Check Duplicate Callback
     * 
     * @param string $code 
     * @param integer|null $id 
     * @return bool
     */	
    public function check_duplicate($code, $id=NULL){

    	$code = strtoupper( $code ? $code : $this->input->post('code') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->department_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

}