<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Companies Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Companies extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Companies';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('company_model');
		$this->load->model('company_branch_model');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'companies/';
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
	function page( $layout='f', $next_id = 0,  $ajax_extra = [] )
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
		$filter_data = $this->_get_filter_data( );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->company_model->rows($params);
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
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-company', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-company' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'companies/page/r/' . $next_id ) : NULL
		] + $dom_data;


		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/companies/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('companies/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/companies/_list';
		}
		else
		{
			$view = 'setup/companies/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{
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
							'setup/companies/_index_header',
							['content_header' => 'Manage Company'] + $dom_data)
						->partial('content', 'setup/companies/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$select = ['' => 'Select ...'];
			$type_in_list = implode(',', array_keys(_COMPANY_type_dropdown(FALSE)));
			$filters = [
	            [
	                'field' => 'filter_type',
	                'label' => 'Company Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[' . $type_in_list . ']',
	                '_type'     => 'dropdown',
	                '_data'     => _COMPANY_type_dropdown(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_pan_no',
	                'label' => 'PAN No',
	                'rules' => 'trim|max_length[20]',
	                '_type'     => 'text',
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_active',
	                'label' => 'Is Active?',
	                'rules' => 'trim|integer|exact_length[1]',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', '0' => 'Not Active', '1' => 'Active'],
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Company Name',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

		private function _get_filter_data()
		{
			$data = ['status' => 'empty'];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'pan_no' 			=> $this->input->post('filter_pan_no') ?? NULL,
						'active' 			=> $this->input->post('filter_active') ?? NULL,
						'keywords' 			=> $this->input->post('filter_keywords') ?? ''
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
		$record = $this->company_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/companies/_form',
			[
				'form_elements' => $this->company_model->validation_rules,
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
		$json_data['form'] = $this->load->view('setup/companies/_form',
			[
				'form_elements' => $this->company_model->validation_rules,
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

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;

			$rules = array_merge($this->company_model->validation_rules, get_contact_form_validation_rules());
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
				 * Upload Image If any?
				 */
				$upload_result 	= $this->_upload_profile_picture($picture);
				$status 		= $upload_result['status'];
				$message 		= $upload_result['message'];
				$files 			= $upload_result['files'];
				$picture = $status === 'success' ? $files[0] : $picture;

	        	if( $status === 'success' || $status === 'no_file_selected')
	            {
	            	$data = $this->input->post();
        			$data['picture'] = $picture;


            		// Insert or Update?
					if($action === 'add')
					{
						$done = $this->company_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->company_model->log_activity($done, 'C'): '';
					}
					else
					{
						// Now Update Data
						$done = $this->company_model->update($record->id, $data, TRUE) && $this->company_model->log_activity($record->id, 'E');
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
								'box' 		=> '#_iqb-data-list-box-company',
								'method' 	=> 'html'
							],
						]);

				}
				else
				{
					// Get Updated Record
					$record = $this->company_model->find($record->id);
					$success_html = $this->load->view('setup/companies/_single_row', ['record' => $record], TRUE);
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
												'box' 	=> '#_data-row-' . $record->id,
												'html' 	=> $success_html,
												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/companies/_form',
											[
												'form_elements' => $this->company_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

		/**
		 * Sub-function: Upload Company Profile Picture
		 *
		 * @param string|null $old_picture
		 * @return array
		 */
		private function _upload_profile_picture( $old_picture = NULL )
		{
			$options = [
				'config' => [
					'encrypt_name' => TRUE,
	                'upload_path' => $this->_upload_path,
	                'allowed_types' => 'gif|jpg|jpeg|png',
	                'max_size' => '2048'
				],
				'form_field' => 'picture',

				'create_thumb' => TRUE,

				// Delete Old file
				'old_files' => $old_picture ? [$old_picture] : [],
				'delete_old' => TRUE
			];
			return upload_insqube_media($options);
		}

	// --------------------------------------------------------------------

	/**
	 * Delete a Company
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->company_model->find($id);
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
		if( !safe_to_delete( 'Company_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->company_model->delete($record->id);

		if($done)
		{
			/**
			 * Delete Media if any
			 */
			if($record->picture)
			{
				// Load media helper
				$this->load->helper('insqube_media');

				delete_insqube_document($this->_upload_path . $record->picture);
			}

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
     * View Company Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->company_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Load media helper
		$this->load->helper('insqube_media');

		$data = [
			'record' 	=> $record,
			'branches' 	=>  $this->company_branch_model->get_by_company($record->id)
		];

		$this->data['site_title'] = 'Company Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Company Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Companies' => 'companies', 'Details' => NULL]
						])
						->partial('content', 'setup/companies/_details', $data)
						->render($this->data);

    }

	// --------------------------------------------------------------------
	// COMPANY BRANCHES FUNCTIONS
	// --------------------------------------------------------------------

    public function branch($action, $company_id, $branch_id=NULL)
    {
    	if(!in_array($action, ['add', 'edit', 'delete']))
    	{
    		$this->template->json([
    			'status' 	=> 'error',
    			'message' 	=> 'Invalid Action!'
			], 404);
    	}

    	// Permission?
    	$permission = $action . '.company.branch';
    	if( !$this->dx_auth->is_authorized('companies', $permission) )
    	{
    		$this->template->json([
    			'status' 	=> 'error',
    			'message' 	=> 'Permission Denied!'
			], 403);
    	}

    	switch ($action)
    	{
    		case 'add':
    		case 'edit':
    			return $this->__branch_save($action, $company_id, $branch_id);
    			break;

			case 'delete':
    			return $this->__branch_delete($company_id, $branch_id);
    			break;

    		default:
    			break;
    	}
    }

	// --------------------------------------------------------------------

    private function __branch_save($action, $company_id, $branch_id=NULL)
    {
    	$record = NULL;
    	if($action === 'edit')
    	{
    		$branch_id 	= (int)$branch_id;
    		$record 	= $this->company_branch_model->find($branch_id);

    		if( !$record || $record->company_id != $company_id )
    		{
    			$this->template->json([
	    			'status' 	=> 'error',
	    			'message' 	=> 'Either branch not found or supplied branch does not belong to specified company!'
				], 404);
    		}
    	}

    	// JSON Data to pass to form
    	$json_data = [];

    	// Form Posted? Let's save the damn thing!
    	if($this->input->post())
    	{
    		$rules = array_merge($this->company_branch_model->validation_rules, get_contact_form_validation_rules());
            $this->form_validation->set_rules($rules);
            $status = 'error';
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$data['company_id'] = $company_id;
					$done = $this->company_branch_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->company_branch_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->company_branch_model->update($record->id, $data, TRUE) && $this->company_branch_model->log_activity($record->id, 'E');
				}

	        	if(!$done)
				{
					$message = 'Could not update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}


				if($status === 'success' )
				{
					$row_view = 'setup/company_branches/_single_row';
					if($action === 'add')
					{
						$record 	= $this->company_branch_model->find($done);
						$dom_box 	= '#search-result-company-branch';
						$dom_method = 'prepend';
					}
					else
					{
						$record 	= $this->company_branch_model->find($record->id);
						$dom_box 	= '#_data-row-company-branch-' . $record->id;
						$dom_method = 'replaceWith';

					}
					$html = $this->load->view($row_view, ['record' => $record], TRUE);

					$ajax_data = [
						'message' 		=> $message,
						'status'  		=> $status,
						'updateSection' => true,
						'hideBootbox' 	=> true,
						'updateSectionData' => [
							'box' 		=> $dom_box,
							'method' 	=> $dom_method,
							'html'		=> $html
						]
					];

					// return json
					return $this->template->json($ajax_data);
				}
        	}
        	else
        	{
        		$message = 'Validation Error.';
        	}

        	// return form with validation error
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 			=> $this->load->view('setup/company_branches/_form',
									[
										'form_elements' => $this->company_branch_model->validation_rules,
										'record' 		=> $record
									], TRUE)
			]);
    	}

    	// No form Submitted?
		$json_data['form'] = $this->load->view('setup/company_branches/_form_box',
		[
			'form_elements' => $this->company_branch_model->validation_rules,
			'record' 		=> $record
		], TRUE);

		// Load the form
		$this->template->json($json_data);
    }

	// --------------------------------------------------------------------

    /**
	 * Delete a Company
	 * @param integer $id
	 * @return json
	 */
	public function __branch_delete($company_id, $branch_id)
	{
		// Valid Record?
		$branch_id 	= (int)$branch_id;
		$record 	= $this->company_branch_model->find($branch_id);

		if( !$record || $record->company_id != $company_id )
		{
			$this->template->json([
    			'status' 	=> 'error',
    			'message' 	=> 'Either branch not found or supplied branch does not belong to specified company!'
			], 404);
		}


		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Company_branch_model', $branch_id ) )
		{
			$this->template->json([
    			'status' 	=> 'error',
    			'message' 	=> 'Sorry! You can not delete default records.'
			], 404);
		}

		$done = $this->company_branch_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-company-branch-'.$record->id
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