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
	private $_navigation = [];

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
        $this->_navigation = [
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class,
			'level_3' => 'index'
		];
		$this->active_nav_primary($this->_navigation);

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
		$records = $this->branch_model->get_all();
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
		$record = $this->branch_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/branches/_form',
			[
				'form_elements' => $this->branch_model->validation_rules,
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
				'form_elements' => $this->branch_model->validation_rules,
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

			$rules = array_merge($this->branch_model->validation_rules, get_contact_form_validation_rules());
			if($action === 'edit')
			{
				// Update Validation Rule on Update
				$rules[1]['rules'] = 'trim|required|max_length[5]|callback_check_duplicate';
			}
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->branch_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->branch_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->branch_model->update($record->id, $data, TRUE) && $this->branch_model->log_activity($record->id, 'E');
				}

				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}
			}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->branch_model->get_all();
					$success_html = $this->load->view('setup/branches/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->branch_model->find($record->id);
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
												'form_elements' => $this->branch_model->validation_rules,
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
		$record = $this->branch_model->find($id);
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
		if( !safe_to_delete( 'Branch_model', $id ) )
		{
			return $this->template->json($data);
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

    /**
     * View Branch Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->branch_model->find($id);
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

    // --------------------------------------------------------------------


    // --------------------------------------------------------------------
    // MANAGE TARGETS
    // --------------------------------------------------------------------


    public function targets()
    {
    	// Site Meta
    	$this->data['site_title'] = 'Master Setup | Branch Targets';

    	$this->load->model('branch_target_model');

    	/**
    	 * Update Nav Data
    	 */
    	$this->_navigation['level_3'] = 'targets';
    	$this->active_nav_primary($this->_navigation);

    	$records = $this->branch_target_model->get_row_list();


		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Branch Targets',
								'breadcrumbs' => ['Master Setup' => NULL, 'Branches' => 'branches', 'Targets' => NULL]
						])
						->partial('content', 'setup/branches/_targets', compact('records'))
						->render($this->data);
  	}

  	// --------------------------------------------------------------------

  	// --------------------------------------------------------------------

	/**
	 * Add new Targets for a Specific Fiscal Year
	 *
	 * @return void
	 */
	public function add_targets()
	{
		$this->load->model('branch_target_model');

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save_targets('add');


		// No form Submitted?
		$rules = $this->branch_target_model->validation_rules;
		$rules[0]['_data'] = ['' => 'Select...'] + $this->fiscal_year_model->dropdown();

		$branches = $this->branch_model->dropdown();

		$json_data['form'] = $this->load->view('setup/branches/_form_targets',
			[
				'form_elements' => $rules,
				'branches' 		=> $branches,
				'action' 		=> 'add',
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Branch Targets for Specific Fiscal Year
	 *
	 *
	 * @param integer $fiscal_yr_id
	 * @return void
	 */
	public function edit_targets($fiscal_yr_id)
	{
		$this->load->model('branch_target_model');

		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$record 		= $this->branch_target_model->get_row_single($fiscal_yr_id);
		$targets 		= $this->branch_target_model->get_list_by_fiscal_year($fiscal_yr_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save_targets('edit', $record);


		// No form Submitted?
		$branches = $this->branch_model->dropdown();

		$rules = $this->branch_target_model->validation_rules;
		$rules[0]['_data'] = ['' => 'Select...'] + $this->fiscal_year_model->dropdown();
		$json_data['form'] = $this->load->view('setup/branches/_form_targets',
			[
				'form_elements' => $rules,
				'action' 		=> 'edit',
				'branches' 		=> $branches,
				'targets' 		=> $targets,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Targets for Specific Fiscal Year
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save_targets($action, $record = NULL)
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

			$rules = $this->branch_target_model->validation_rules;


			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$fiscal_yr_id = $this->input->post('fiscal_yr_id');
				$target_total = $this->input->post('target_total');
				$branches = $this->branch_model->dropdown();

				// Insert or Update?
				if($action === 'add')
				{
					$i = 0;
					foreach($branches as $branch_id => $branch_name)
					{
						$data = [
							'fiscal_yr_id' 	=> $fiscal_yr_id,
							'branch_id'    	=> $branch_id,
							'target_total' 	=> $target_total[$i]
						];

						$done = $this->branch_target_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->branch_target_model->log_activity($done, 'C'): '';
						$i++;
					}
				}
				else
				{
					// Now Update Data
					$target_ids = $this->input->post('target_ids');
					$i = 0;
					foreach($branches as $branch_id => $branch_name)
					{
						$data = [
							'target_total' 	=> $target_total[$i]
						];
						$target_id = $target_ids[$i];

						$done = $this->branch_target_model->update($target_id, $data, TRUE) && $this->branch_target_model->log_activity($target_id, 'E');

						$i++;
					}

				}

				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}
			}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->branch_target_model->get_row_list();
					$success_html = $this->load->view('setup/branches/_list_targets', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->branch_target_model->get_row_single($fiscal_yr_id);
					$success_html = $this->load->view('setup/branches/_single_row_targets', ['record' => $record], TRUE);
				}
			}

			$rules 				= $this->branch_target_model->validation_rules;
			$rules[0]['_data'] 	= ['' => 'Select...'] + $this->fiscal_year_model->dropdown();
			$targets 			= $record ? $this->branch_target_model->get_list_by_fiscal_year($record->fiscal_yr_id) : NULL;

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
															: '#_data-row-' . $record->fiscal_yr_id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/branches/_form_targets',
											[
												'form_elements' => $rules,
												'record' 		=> $record,
												'action' 		=> $action,
												'targets' 		=> $targets
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Targets of a Specific Fiscal Year
	 * @param integer $id
	 * @return json
	 */
	public function delete_targets($fiscal_yr_id)
	{
		$this->load->model('branch_target_model');

		// Valid Record ?
		$fiscal_yr_id = (int)$fiscal_yr_id;
		$targets 		= $this->branch_target_model->get_list_by_fiscal_year($fiscal_yr_id);
		$record 		= $this->branch_target_model->get_row_single($fiscal_yr_id);
		if(!$record || !$targets)
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];

		foreach ($targets as $target)
		{
			/**
			 * Safe to Delete?
			 */
			if( !safe_to_delete( 'Branch_target_model', $target->id ) )
			{
				return $this->template->json($data);
			}

			$done = $this->branch_target_model->delete($target->id);
		}



		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->fiscal_yr_id
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


}