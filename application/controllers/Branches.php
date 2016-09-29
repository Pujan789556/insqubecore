<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Branches Controller
 * 
 * This controller falls under "Master Setup" category.
 *  
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Branches extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Branches';

        // Setup Navigation        
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('branch_model');		    
	}
	
	// --------------------------------------------------------------------

	/**
	 * Default Method
	 * 
	 * Render the list
	 * 
	 * @return type
	 */
	function index()
	{
		/**
		 * Normal Form Render
		 */
		// this will generate cache name: mc_master_departments_all
		$records = $this->branch_model->set_cache('all')->get_all();
	
		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Branches',
								'breadcrumbs' => ['Master Setup' => NULL, 'Branches' => NULL]
						])
						->partial('content', 'setup/branches/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Branch
	 * 
	 * 
	 * @param integer $id 
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->branch_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);
		

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/branches/_form', 
			[
				'form_elements' => $this->branch_model->rules['insert'],
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
		$json_data['form'] = $this->load->view('setup/branches/_form', 
			[
				'form_elements' => $this->branch_model->rules['insert'],
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
			
			$val_rules = array_merge($this->branch_model->rules['insert'], get_contact_form_validation_rules());

			// Insert or Update?
			if($action === 'add')
			{
				// @NOTE: Activity Log will be automatically inserted
				$done = $this->branch_model->from_form($val_rules)->insert();				
			}
			else
			{
				// Update Validation Rule on Update
				$val_rules[1]['rules'] = 'trim|required|max_length[5]|callback_check_duplicate';
				
				
				// Now Update Data
				$done = $this->branch_model->from_form($val_rules)->update(NULL, $record->id) && $this->branch_model->log_activity($record->id, 'E');
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
					$records = $this->branch_model->set_cache('all')->get_all();
					$success_html = $this->load->view('setup/branches/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->branch_model->get($record->id);
					$success_html = $this->load->view('setup/branches/_single_row', ['record' => $record], TRUE);
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
									? 	$this->load->view('setup/branches/_form', 
											[
												'form_elements' => $this->branch_model->rules['insert'],
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Branch
	 * @param integer $id 
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->branch_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$done = $this->branch_model->delete($record->id);
		
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

        if( $this->branch_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->branch_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}
		$this->data['site_title'] = 'Branch Details | ' . $record->name;
		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Branch Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Branches' => 'branches', 'Details' => NULL]
						])
						->partial('content', 'setup/branches/_details', compact('record'))
						->render($this->data);

    }
}