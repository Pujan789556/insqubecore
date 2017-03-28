<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Setup - Treaties Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category RI
 */

// --------------------------------------------------------------------

class Ri_setup_treaties extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | RI | Treaties';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'ri',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ri_setup_treaty_model');
		$this->load->model('ri_setup_treaty_type_model');

		// Data Path
        $this->_upload_path = INSQUBE_DATA_PATH . 'treaties/';
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

	// --------------------------------------------------------------------

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

		$records = $this->ri_setup_treaty_model->rows($params);
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
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-ri-setup-treaty', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ri-setup-treaty' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ri_setup_treaties/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/ri/treaties/_index';

			/**
			 * Filter Configurations
			 */
			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ri_setup_treaties/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/ri/treaties/_list';
		}
		else
		{
			$view = 'setup/ri/treaties/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{


			// $view = $refresh === FALSE ? 'setup/ri/treaties/_rows' : 'setup/ri/treaties/_list';
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

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/ri/treaties/_index_header',
							['content_header' => 'Manage Treaties'] + $dom_data)
						->partial('content', 'setup/ri/treaties/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

		private function _get_filter_elements()
		{
			$filters = [
				[
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_treaty_type_id',
	                'label' => 'Treaty Type',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->ri_setup_treaty_type_model->dropdown(),
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
						'fiscal_yr_id' 		=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'treaty_type_id' 	=> $this->input->post('filter_treaty_type_id') ?? NULL
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

	// --------------------------------------------------------------------

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
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form',
			[
				'form_elements' => $this->ri_setup_treaty_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

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
		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form',
			[
				'form_elements' => $this->ri_setup_treaty_model->validation_rules,
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
			$file = $record->file ?? NULL;

			$rules = $this->ri_setup_treaty_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
				 * Upload Document If any?
				 */
				$upload_result 	= $this->_upload_treaty_document($file);
				$status 		= $upload_result['status'];
				$message 		= $upload_result['message'];
				$files 			= $upload_result['files'];
				$file = $status === 'success' ? $files[0] : $file;

				if( $status === 'success' || $status === 'no_file_selected')
	            {
	            	$data = $this->input->post();
	            	$data['file'] = $file;

	        		// Insert or Update?
					if($action === 'add')
					{
						$done = $this->ri_setup_treaty_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->ri_setup_treaty_model->log_activity($done, 'C'): '';
					}
					else
					{
						// Now Update Data
						$done = $this->ri_setup_treaty_model->update($record->id, $data, TRUE) && $this->ri_setup_treaty_model->log_activity($record->id, 'E');
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
								'box' 		=> '#_iqb-data-list-box-ri-setup-treaty',
								'method' 	=> 'html'
							]
						], FALSE);
				}
				else
				{
					// Get Updated Record
					$record = $this->ri_setup_treaty_model->row($record->id);
					$success_html = $this->load->view('setup/ri/treaties/_single_row', ['record' => $record], TRUE);
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
					'form' 	  		=> $this->load->view('setup/ri/treaties/_form',
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
		 * Sub-function: Upload Company Profile Picture
		 *
		 * @param string|null $old_file
		 * @return array
		 */
		private function _upload_treaty_document( $old_file = NULL )
		{
			$options = [
				'config' => [
					'encrypt_name' => TRUE,
	                'upload_path' => $this->_upload_path,
	                'allowed_types' => 'pdf',
	                'max_size' => '4096'
				],
				'form_field' => 'file',

				'create_thumb' => FALSE,

				// Delete Old file
				'old_files' => $old_file ? [$old_file] : [],
				'delete_old' => TRUE
			];
			return upload_insqube_media($options);
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - [Fiscal Year ID, Treaty Type]
		 *
		 * Duplicate Condition: [Fiscal Year ID, Treaty Type] Should be Unique
		 *
		 * @param integer $treaty_type_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_treaty_type__check_duplicate($treaty_type_id, $id=NULL)
		{
			$treaty_type_id = strtoupper( $treaty_type_id ? $treaty_type_id : $this->input->post('treaty_type_id') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');
	    	$fiscal_yr_id = (int)$this->input->post('fiscal_yr_id');

	    	// Check if Fiscal Year has not been selected yet?
	    	if( !$fiscal_yr_id )
	    	{
	    		$this->form_validation->set_message('_cb_treaty_type__check_duplicate', 'The Fiscal Year must be supplied along with %s.');
	            return FALSE;
	    	}

	    	// Check Duplicate
	        if( $this->ri_setup_treaty_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id, 'treaty_type_id' => $treaty_type_id], $id))
	        {
	            $this->form_validation->set_message('_cb_treaty_type__check_duplicate', 'The %s already exists for supplied Fiscal Year.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - Name
		 *
		 * Duplicate Condition: [Fiscal Year ID, Treaty Type] Should be Unique
		 *
		 * @param integer $name
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_name__check_duplicate($name, $id=NULL)
		{
			$name = strtoupper( $name ? $name : $this->input->post('name') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	    	// Check Duplicate
	        if( $this->ri_setup_treaty_model->check_duplicate(['LOWER(`name`)=' => strtolower($name)], $id))
	        {
	            $this->form_validation->set_message('_cb_name__check_duplicate', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

    /**
     * View Treaty Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ri_setup_treaties', 'explore.treaty') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
    	$record = $this->ri_setup_treaty_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		$data = [
			'record' 		=> $record,
			'brokers' 		=> $this->ri_setup_treaty_model->get_brokers_by_treaty($id),
			'portfolios' 	=> $this->ri_setup_treaty_model->get_portfolios_by_treaty($id),
			'distribution' 	=> $this->ri_setup_treaty_model->get_treaty_distribution_by_treaty($id),
		];

		$this->data['site_title'] = 'Treaty Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Treaty Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Treaty Setup' => 'ri_setup_treaties', 'Details' => NULL]
						])
						->partial('content', 'setup/ri/treaties/_details', $data)
						->render($this->data);

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
		$record = $this->ri_setup_treaty_model->find($id);
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
		if( !safe_to_delete( 'Ri_setup_treaty_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ri_setup_treaty_model->delete($record->id);

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

	public function download($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ri_setup_treaties', 'download.treaty') )
		{
			$this->dx_auth->deny_access();
		}

		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Let's Download
		$this->load->helper('download');
        $download_file = $record->file ? $this->_upload_path . $record->file : NULL;
        if( $download_file && file_exists($download_file) )
        {
            force_download($download_file, NULL, true);
        }
        else
        {
        	$this->template->render_404('', "Sorry! File Not Found.");
        }
	}

	// --------------------------------------------------------------------


}