<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Account Groups Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Ac_account_groups extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Account Groups';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'account',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ac_account_group_model');
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
		$records = $this->ac_account_group_model->get_all();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Account Groups',
								'breadcrumbs' => ['Master Setup' => NULL, 'Account Groups' => NULL]
						])
						->partial('content', 'setup/ac_account_groups/_index', compact('records'))
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
		$record = $this->ac_account_group_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ac_account_groups/_form',
			[
				'form_elements' => $this->ac_account_group_model->validation_rules,
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

			$rules = $this->ac_account_group_model->validation_rules;
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->ac_account_group_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->ac_account_group_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->ac_account_group_model->update($record->id, $data, TRUE) && $this->ac_account_group_model->log_activity($record->id, 'E');
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
					$records = $this->ac_account_group_model->get_all();
					$success_html = $this->load->view('setup/ac_account_groups/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->ac_account_group_model->row($record->id);
					$success_html = $this->load->view('setup/ac_account_groups/_single_row', ['record' => $record], TRUE);
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
									? 	$this->load->view('setup/ac_account_groups/_form',
											[
												'form_elements' => $this->ac_account_group_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

    // --------------------------------------------------------------------

}