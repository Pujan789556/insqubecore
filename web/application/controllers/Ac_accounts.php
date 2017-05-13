<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Accounts Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Ac_accounts extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// // Only Admin Can access this controller
		// if( !$this->dx_auth->is_admin() )
		// {
		// 	$this->dx_auth->deny_access();
		// }

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | Accounts';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'account',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ac_account_group_model');
		$this->load->model('ac_account_model');
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
		$this->page();
	}

	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $from_widget='n', $next_id = 0, $widget_reference = '' )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_accounts', 'explore.ac_account') )
		{
			$this->dx_auth->deny_access();
		}

		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= 'ac_accounts/page/r/' . $from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-ac-account', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ac-account' 			// Filter Form ID
		];


		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id, $widget_reference );
		$data = array_merge($data, $dom_data);

		/**
		 * Widget Specific Data
		 */
		$data['_flag__show_widget_row'] = $from_widget === 'y';


		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = $from_widget === 'y' ? 'setup/ac/accounts/_find_widget' : 'setup/ac/accounts/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->router->class . '/page/l/' . $from_widget . '/0/' . $widget_reference)
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/ac/accounts/_list';
		}
		else
		{
			$view = 'setup/ac/accounts/_rows';
		}

		if ( $this->input->is_ajax_request() )
		{

			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];

			$this->template->json($ajax_data);
		}

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/ac/accounts/_index_header',
							['content_header' => 'Manage Accounts'] + $dom_data)
						->partial('content', 'setup/ac/accounts/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$dropdwon_account_groups = $this->ac_account_group_model->dropdown_tree();
			$filters = [
				[
	                'field' => 'filter_account_group_id',
	                'label' => 'Account Group',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $dropdwon_account_groups,
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Account Name',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

		private function _get_filter_data( $next_url_base, $next_id = 0, $widget_reference = '' )
		{
			$params = [];
			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'account_group_id' 	=> $this->input->post('filter_account_group_id') ?? NULL,
						'keywords' 			=> $this->input->post('filter_keywords') ?? ''
					];
				}
				else
				{
					$data = [
						'status' 	=> 'error',
						'message' 	=> validation_errors()
					];

					$this->template->json($data);
				}
			}

			$next_id = (int)$next_id;
			if( $next_id )
			{
				$params['next_id'] = $next_id;
			}

			/**
			 * Get Search Result
			 */
			$records = $this->ac_account_model->rows($params);
			$records = $records ? $records : [];
			$total = count($records);

			/**
			 * Account Group Paths
			 */
			if($total)
			{
				foreach($records as &$record)
				{
					$path = $this->ac_account_group_model->get_path($record->account_group_id);
					$record->acg_path = $path;
				}
			}

			/**
			 * Grab Next ID or Reset It
			 */
			if( $total == $this->settings->per_page+1 )
			{
				$next_id = $records[$total-1]->id;
				unset($records[$total-1]); // remove last record
			}
			else
			{
				$next_id = NULL;
			}

			$data = [
				'records' 			=> $records,
				'widget_reference' 	=> $widget_reference,
				'next_id'  => $next_id,
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id  . '/' . $widget_reference ) : NULL
			];
			return $data;
		}

	/**
	 * Refresh The Module
	 *
	 * Simply reload the first page
	 *
	 * @return type
	 */
	function refresh()
	{
		$this->page('l');
	}

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l');
	}

	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id, $from_widget = 'n', $widget_reference = '')
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_accounts', 'edit.ac_account') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_account_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $from_widget, $widget_reference);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ac/accounts/_form_box',
			[
				'form_elements' => $this->ac_account_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add( $from_widget='n', $widget_reference = '' )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_accounts', 'add.ac_account') )
		{
			$this->dx_auth->deny_access();
		}

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record, $from_widget, $widget_reference);

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ac/accounts/_form_box',
			[
				'form_elements' => $this->ac_account_model->validation_rules,
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
	 * @param char 	$from_widget
	 * @param string $widget_reference
	 * @return array
	 */
	private function _save($action, $record = NULL, $from_widget='n', $widget_reference = '')
	{
		// Valid action? Valid from_widget
		if( !in_array($action, array('add', 'edit')) || !in_array($from_widget, array('y', 'n')) )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			], 404);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->ac_account_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		// Active/Inactive
        		$data['active'] = $data['active'] ?? 0;

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->ac_account_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->ac_account_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->ac_account_model->update($record->id, $data, TRUE) && $this->ac_account_model->log_activity($record->id, 'E');
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

			if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 			= $this->ac_account_model->row( $action === 'add' ? $done : $record->id );
				$record->acg_path 	= $this->ac_account_group_model->get_path($record->account_group_id);
				$single_row 		=  'setup/ac/accounts/_single_row';
				if($action === 'add' && $from_widget === 'y' )
				{
					$single_row = 'setup/ac/accounts/_single_row_widget';
				}
				$html = $this->load->view($single_row, ['record' => $record, 'widget_reference' => $widget_reference], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-ac-account' : '#_data-row-' . $record->id,
					'method' 	=> $action === 'add' ? 'prepend' : 'replaceWith',
					'html'		=> $html
				];

				return $this->template->json($ajax_data);
			}

			// Form
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 			=> $this->load->view('setup/ac/accounts/_form',
									[
										'form_elements' => $rules,
										'record' 		=> $record
									], TRUE)
			]);
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Agent
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_accounts', 'delete.ac_account') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_account_model->find($id);
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
		if( !safe_to_delete( 'Ac_chart_of_account_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ac_account_model->delete($record->id);

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
}