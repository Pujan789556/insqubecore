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

		// Image Path
        $this->_upload_path = MEDIAPATH . 'companies/';
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
	function page( $next_id = 0, $refresh = FALSE, $ajax_extra = [] )
	{
		// If request is coming from refresh method, reset nextid
		$next_id = $refresh === FALSE ? (int)$next_id : 0;

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

		$data = [
			'records' => $records,
			'next_id' => $next_id
		];

		if ( $this->input->is_ajax_request() )
		{

			$view = $refresh === FALSE ? 'setup/companies/_rows' : 'setup/companies/_list';
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
		$data['filters'] = $this->_get_filter_elements();
		$data['filter_url'] = site_url('companies/filter/');

		$this->template->partial(
							'content_header',
							'setup/companies/_index_header',
							['content_header' => 'Manage Company'])
						->partial('content', 'setup/companies/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$select = ['' => 'Select ...'];
			$filters = [
				[
	                'field' => 'filter_ud_code',
	                'label' => 'Company UD Code',
	                'rules' => 'trim|integer|max_length[15]',
	                '_type'     => 'text',
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_type',
	                'label' => 'Company Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[B,L,R]',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', 'B' => 'Type B', 'L' => 'Type L', 'R' => 'Type R'],
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_pan_no',
	                'label' => 'Commission Group',
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
						'ud_code' 			=> $this->input->post('filter_ud_code') ?? NULL,
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'pan_no' 	=> $this->input->post('filter_pan_no') ?? NULL,
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
		$this->page(0, TRUE);
	}

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page(0, TRUE);
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
					return $this->page(0, TRUE, [
							'message' => $message,
							'status'  => $status,
							'hideBootbox' => true
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
	                'allowed_types' => 'gif|jpg|png',
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

		$this->data['site_title'] = 'Company Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Company Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Companies' => 'companies', 'Details' => NULL]
						])
						->partial('content', 'setup/companies/_details', compact('record'))
						->render($this->data);

    }
}