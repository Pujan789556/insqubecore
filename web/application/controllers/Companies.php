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
	/**
	 * Files Upload Path
	 */
	public static $media_upload_path = INSQUBE_MEDIA_ROOT . 'media/companies/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('companies', 'explore.company') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= $this->router->class . '/page/r/' . $from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-company', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-company' 			// Filter Form ID
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
			$view = $from_widget === 'y' ? 'setup/companies/_find_widget' : 'setup/companies/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->router->class . '/page/l/' . $from_widget . '/0/' . $widget_reference)
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
			$type_dropdown = _COMPANY_type_dropdown(FALSE);
			$filters = [
	            [
	                'field' => 'filter_type',
	                'label' => 'Company Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($type_dropdown)) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $type_dropdown,
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

		private function _get_filter_data( $next_url_base, $next_id = 0, $widget_reference = '')
		{
			$params = [];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'pan_no' 			=> $this->input->post('filter_pan_no') ?? NULL,
						'active' 			=> $this->input->post('filter_active') ?? NULL,
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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('companies', 'edit.company') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->company_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Address Record
		$address_record = $this->address_model->get_by_type(IQB_ADDRESS_TYPE_COMPANY_BRANCH, $record->company_branch_id);

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $address_record, $from_widget, $widget_reference);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/companies/_form_box',
			[
				'form_elements' => $this->company_model->validation_rules,
				'address_elements' 	=> $this->address_model->v_rules_edit($address_record),
				'record' 			=> $record,
				'address_record' 	=> $address_record
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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('companies', 'add.company') )
		{
			$this->dx_auth->deny_access();
		}

		$record 		= NULL;
		$address_record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record, $address_record, $from_widget, $widget_reference);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/companies/_form_box',
			[
				'form_elements' 	=> $this->company_model->validation_rules,
				'record' 			=> $record,
				'address_elements' 	=> $this->address_model->v_rules_add(),
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
	private function _save($action, $record = NULL, $address_record = NULL, $from_widget='n', $widget_reference = '')
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

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;

			$rules = array_merge($this->company_model->validation_rules, $this->address_model->v_rules_on_submit(TRUE));
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


        			// Active ??
        			$data['active'] = $data['active'] ?? IQB_FLAG_OFF;

            		// Insert or Update?
					if($action === 'add')
					{
						$done = $this->company_model->add($data); // No Validation on Model
					}
					else
					{
						// Now Update Data
						$done = $this->company_model->edit($record->id, $data);
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


			if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 			= $this->company_model->find( $action === 'add' ? $done : $record->id );
				$single_row 		=  'setup/companies/_single_row';
				if($action === 'add' && $from_widget === 'y' )
				{
					$single_row = 'setup/companies/_single_row_widget';
				}
				$html = $this->load->view($single_row, ['record' => $record, 'widget_reference' => $widget_reference], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-company' : '#_data-row-' . $record->id,
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
				'form' 			=> $this->load->view('setup/companies/_form',
									[
										'form_elements' => $this->company_model->validation_rules,
										'address_elements' 	=> $this->address_model->v_rules_on_submit(),
										'record' 			=> $record
									], TRUE)
			]);
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
	                'upload_path' => self::$media_upload_path,
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
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('companies', 'delete.company') )
		{
			$this->dx_auth->deny_access();
		}

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
				delete_insqube_document(self::$media_upload_path . $record->picture);
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
		$record = $this->company_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		$data = [
			'record' 	=> $record,
			'branches' 	=>  $this->company_branch_model->get_by_company($record->id),
			'address_record' => $this->company_branch_model->get_ho_address($record->id)
		];

		$this->data['site_title'] = 'Company Details | ' . $record->name_en;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Company Details <small>' . $record->name_en . '</small>',
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
    	$address_record = NULL;
    	if($action === 'edit')
    	{
    		$branch_id 	= (int)$branch_id;
    		$record 	= $this->company_branch_model->find($branch_id);

    		// Address Record
			$address_record = $this->address_model->get_by_type(IQB_ADDRESS_TYPE_COMPANY_BRANCH, $record->id);

    		if( !$record || $record->company_id != $company_id || !$address_record )
    		{
    			$this->template->json([
	    			'status' 	=> 'error',
	    			'message' 	=> 'Either branch not found or supplied branch does not belong to specified company or Branch address does not exists!'
				], 404);
    		}
    	}

    	// JSON Data to pass to form
    	$json_data = [];

    	// Form Posted? Let's save the damn thing!
    	if($this->input->post())
    	{
    		$rules = array_merge($this->company_branch_model->validation_rules, $this->address_model->v_rules_on_submit(TRUE));
            $this->form_validation->set_rules($rules);
            $status = 'error';
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		$data['company_id'] 		= $company_id; // required on both add/edit for after add/edit tasks
        		$data['is_head_office'] 	= $data['is_head_office'] ?? 0; // If no headoffice, reset to 0

        		// Insert or Update?
				if($action === 'add')
				{
					$data['company_id'] = $company_id;
					$done = $this->company_branch_model->add($data);
				}
				else
				{
					// Now Update Data
					$done = $this->company_branch_model->edit($record->id, $address_record->id, $data);
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
					$records 	= $this->company_branch_model->get_by_company($company_id);

					$dom_box 	= '#search-result-company-branch';
					$dom_method = 'html';
					$list_view 	= 'setup/company_branches/_rows';
					$html 		= $this->load->view($list_view, ['records' => $records], TRUE);


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
										'record' 		=> $record,
										'address_elements' 	=> $this->address_model->v_rules_on_submit(),
									], TRUE)
			]);
    	}

    	// No form Submitted?
		$json_data['form'] = $this->load->view('setup/company_branches/_form_box',
		[
			'form_elements' => $this->company_branch_model->validation_rules,
			'address_elements' 	=> $action === 'edit'
										? $this->address_model->v_rules_edit($address_record)
										: $this->address_model->v_rules_add(),
			'record' 			=> $record,
			'address_record' 	=> $address_record
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
			], 403);
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
			], 403);
		}


		/**
		 * Can't Delete Head Office
		 */
		if( $this->company_branch_model->is_head_office($branch_id) )
		{
			$this->template->json([
    			'status' 	=> 'error',
    			'message' 	=> 'Sorry! You can not delete Head Office Branch.'
			], 403);
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

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->company_model->clear_cache();
        $this->company_branch_model->clear_cache();
        redirect($this->router->class);
    }

	// --------------------------------------------------------------------


}