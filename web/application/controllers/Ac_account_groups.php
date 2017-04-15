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
		$records = $this->ac_account_group_model->rows();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Account Groups',
								'breadcrumbs' => ['Master Setup' => NULL, 'Account Groups' => NULL]
						])
						->partial('content', 'setup/ac/groups/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Chart of Account
	 *
	 * Render the Chart of Account
	 *
	 * @return type
	 */
	function chart($action='view')
	{
		/**
		 * Get the Chart of account tree
		 */
		$records = $this->ac_account_group_model->tree(NULL, ' | ---');


		if($action == 'view')
		{
			$this->template->partial(
								'content_header',
								'templates/_common/_content_header',
								[
									'content_header' => 'Chart of Accounts',
									'breadcrumbs' => ['Master Setup' => NULL, 'Account Groups' => 'ac_account_groups']
							])
							->partial('content', 'setup/ac/groups/_chart', compact('records'))
							->render($this->data);
		}
		else if($action == 'print')
		{

			if( $records )
			{
				$this->load->library('pdf');
		        $mpdf = $this->pdf->load();
		        $mpdf->SetMargins(10, 10, 5);
		        $mpdf->margin_header = 0;
		        $mpdf->margin_footer = 2;
		        $mpdf->SetProtection(array('print'));
		        $mpdf->SetTitle("Chart of Accounts");
		        $mpdf->SetAuthor($this->settings->orgn_name_en);


		        $mpdf->showWatermarkText = true;
		        $mpdf->watermark_font = 'DejaVuSansCondensed';
		        $mpdf->watermarkTextAlpha = 0.1;
		        $mpdf->SetDisplayMode('fullpage');

		        $html = $this->load->view('setup/ac/groups/_chart_data', compact('records'), TRUE);
		        $mpdf->WriteHTML($html);

		        $mpdf->Output('chart-of-accounts.pdf', 'I');
			}
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		$form_data = [
			'form_elements' => $this->ac_account_group_model->validation_rules['add'],
			'record' 		=> $record
		];

		return $this->_save('add', $form_data, $record);
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

		$form_data = [
			'form_elements' => $this->ac_account_group_model->validation_rules['edit'],
			'record' 		=> $record
		];

		return $this->_save('edit', $form_data, $record);
	}

	// --------------------------------------------------------------------

	/**
	 * Move a  Record
	 *
	 * @return void
	 */
	public function move($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_account_group_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$form_data = [
			'form_elements' => $this->ac_account_group_model->validation_rules['move'],
			'record' 		=> $record
		];

		return $this->_save('move', $form_data, $record);
	}

	// --------------------------------------------------------------------

	/**
	 * Move a  Record
	 *
	 * @return void
	 */
	public function order($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_account_group_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$form_data = [
			'form_elements' => $this->ac_account_group_model->validation_rules['order'],
			'record' 		=> $record
		];

		return $this->_save('order', $form_data, $record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $form_data, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit', 'move', 'order')))
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			]);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $form_data['form_elements'];
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->ac_account_group_model->add($data); // No Validation on Model

					// Activity Log
					$done ? $this->ac_account_group_model->log_activity($done, 'C'): '';
				}
				else if($action === 'edit' )
				{
					// Now Update Data
					$done = $this->ac_account_group_model->update($record->id, $data, TRUE) && $this->ac_account_group_model->log_activity($record->id, 'E');
				}
				else if($action === 'move' ){
					// Now Update Data
					$done = $this->ac_account_group_model->move($record->id, $data);
				}
				else if($action === 'order' ){
					// Now Update Data
					$done = $this->ac_account_group_model->order($record->id, $data);
				}

				if(!$done)
				{
					$message = 'Could not update.';
					$status = 'error';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}
			}
			else
			{
				$message = validation_errors();
				$status = 'error';
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->ac_account_group_model->rows();
					$success_html = $this->load->view('setup/ac/groups/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->ac_account_group_model->row($record->id);
					$success_html = $this->load->view('setup/ac/groups/_single_row', ['record' => $record], TRUE);
				}


				$return_data = [
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> false,
					'hideBootbox' 	=> true,
					'updateSection' => true,
					'updateSectionData'	=> [
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
				];

				$this->template->json($return_data);

			}
			else
			{
				return $this->template->json([
					'status' => 'error',
					'message' => $message
				]);
			}
		}

		// Render Form
		$json_data['form'] = $this->load->view('setup/ac/groups/_form', $form_data, TRUE);

		// Return HTML
		$this->template->json($json_data);

	}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Valid Parent
		 *
		 * 1. Same account can not be parent and child
		 *
		 * @param integer $parent_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_valid_parent($parent_id, $id=NULL)
		{
			$parent_id = (int)$parent_id;
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	    	if($id && $id === $parent_id)
	    	{
	    		$this->form_validation->set_message('_cb_valid_parent', 'Same account group can not be parent for itself.');
	            return FALSE;
	    	}
	        return TRUE;
		}

    // --------------------------------------------------------------------


	public function delete($type, $id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_account_group_model->find($id);

		if( !$record || !in_array($type, ['node', 'subtree']) )
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
		if( !safe_to_delete( 'Ac_account_group_model', $id ) )
		{
			return $this->template->json($data);
		}

		// Admin Constraint?
		$done = $this->ac_account_group_model->delete_nodes($record->id, $type);
		if($done)
		{
			$records = $this->ac_account_group_model->rows();
			$success_html = $this->load->view('setup/ac/groups/_list', ['records' => $records], TRUE);

			// Reload the view
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'multipleUpdate' => [
					[
						'box' 		=> '#iqb-data-list',
						'method' 	=> 'html',
						'html' 		=> $success_html
					]
				]
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


}