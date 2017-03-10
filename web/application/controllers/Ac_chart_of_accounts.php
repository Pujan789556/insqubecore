<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chart of Accounts Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Ac_chart_of_accounts extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Chart of Accounts';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'account',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ac_account_group_model');
		$this->load->model('ac_chart_of_account_model');
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
	function page( $layout='f', $next_id = 0,  $ajax_extra = [], $do_filter = TRUE )
	{

		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;

		$params = array();
		if( $next_id )
		{
			$params = ['next_id' => $next_id];
		}

		/**
		 * Extract Filter Elements
		 */
		$filter_data = $this->_get_filter_data( $do_filter );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->ac_chart_of_account_model->rows($params);
		$records = $records ? $records : [];
		$total = count($records);

		/**
		 * Grab Next ID or Reset It
		 */
		if($total == $this->settings->per_page+1)
		{
			$next_id = $records[$total-1]->id;
			unset($records[$total-1]); // remove last record
		}
		else
		{
			$next_id = NULL;
		}

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-ac-chart-of-account', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ac-chart-of-account' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ac_chart_of_accounts/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/ac_chart_of_accounts/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_chart_of_accounts/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/ac_chart_of_accounts/_list';
		}
		else
		{
			$view = 'setup/ac_chart_of_accounts/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{


			// $view = $refresh === FALSE ? 'setup/ac_chart_of_accounts/_rows' : 'setup/ac_chart_of_accounts/_list';
			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];

			if( !empty($ajax_extra))
			{
				$ajax_data = array_merge($ajax_data, $ajax_extra);
			}
			$this->template->json($ajax_data);
		}

		/**
		 * Filter Configurations
		 */
		// $data['filters'] = $this->_get_filter_elements();
		// $data['filter_url'] = site_url('ac_chart_of_accounts/filter/');

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/ac_chart_of_accounts/_index_header',
							['content_header' => 'Manage Chart of Accounts'] + $dom_data)
						->partial('content', 'setup/ac_chart_of_accounts/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$dropdwon_heading_groups = $this->ac_account_group_model->dropdown();
			$filters = [
				[
	                'field' => 'filter_account_group_id',
	                'label' => 'Account Group',
	                'rules' => 'trim|integer|max_length[10]|in_list[' . implode(',', array_keys($dropdwon_heading_groups)) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $dropdwon_heading_groups,
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Chart of Account Name',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

		private function _get_filter_data( $do_filter=TRUE )
		{
			$data = ['status' => 'empty'];

			// Return Empty on do_filter = false (set 'false' by 'add' method)
			if( !$do_filter )
			{
				return $data;
			}
			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'account_group_id' 	=> $this->input->post('filter_account_group_id') ?? NULL,
						'keywords' 					=> $this->input->post('filter_keywords') ?? ''
					];
					$data['status'] = 'success';
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
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_chart_of_account_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ac_chart_of_accounts/_form',
			[
				'form_elements' => $this->ac_chart_of_account_model->validation_rules,
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
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ac_chart_of_accounts/_form',
			[
				'form_elements' => $this->ac_chart_of_account_model->validation_rules,
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

		// Load media helper
		$this->load->helper('insqube_media');

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->ac_chart_of_account_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->ac_chart_of_account_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->ac_chart_of_account_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->ac_chart_of_account_model->update($record->id, $data, TRUE) && $this->ac_chart_of_account_model->log_activity($record->id, 'E');
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
			$return_extra = [];
			if($status === 'success' )
			{
				if($action === 'add')
				{
					// Refresh the list page and close bootbox
					return $this->page('l', 0, [
							'message' => $message,
							'status'  => $status,
							'hideBootbox' => true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_iqb-data-list-box-ac-chart-of-account',
								'method' 	=> 'html'
							]
						], FALSE);
				}
				else
				{
					// Get Updated Record
					$record = $this->ac_chart_of_account_model->row($record->id);
					$success_html = $this->load->view('setup/ac_chart_of_accounts/_single_row', ['record' => $record], TRUE);
					$ajax_data = [
						'message' => $message,
						'status'  => $status,
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#_data-row-' . $record->id,
						'method' 	=> 'replaceWith',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
				}
			}
			else
			{
				$return_data = [
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'hideBootbox' 	=> false,
					'updateSection' => false,
					'updateSectionData'	=> NULL,
					'form' 	  		=> $this->load->view('setup/ac_chart_of_accounts/_form',
												[
													'form_elements' => $rules,
													'record' 		=> $record
												], TRUE)

				];
			}
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function
		 *
		 * 1. Validate the ac_number range as per selected account group
		 * 2. Check duplicate
		 *
		 * @param integer $ac_number
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_valid_account_group($ac_number, $id=NULL)
		{
			$ac_number = strtoupper( $ac_number ? $ac_number : $this->input->post('ac_number') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');
	    	$account_group_id = (int)$this->input->post('account_group_id');

	    	// First Check if Valid Range
	    	if( !$this->ac_account_group_model->valid_range($account_group_id, $ac_number) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_heading_group', 'The %s does not fall under selected heading group range.');
	            return FALSE;
	    	}

	    	// Check Duplicate
	        if( $this->ac_chart_of_account_model->check_duplicate(['ac_number' => $ac_number], $id))
	        {
	            $this->form_validation->set_message('_cb_valid_heading_group', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

	/**
	 * Delete a Agent
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_chart_of_account_model->find($id);
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

		$done = $this->ac_chart_of_account_model->delete($record->id);

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