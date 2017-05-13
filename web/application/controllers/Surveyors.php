<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Surveyors Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Surveyors extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | Surveyors';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('surveyor_model');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'surveyors/';
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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('surveyors', 'explore.surveyor') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;
		$next_url_base = 'surveyors/page/r/'.$from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-surveyor', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-surveyor' 			// Filter Form ID
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
			$view = $from_widget === 'y' ? 'setup/surveyors/_find_widget' : 'setup/surveyors/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->router->class . '/page/l/' . $from_widget . '/0/' . $widget_reference)
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/surveyors/_list';
		}
		else
		{
			$view = 'setup/surveyors/_rows';
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
							'setup/surveyors/_index_header',
							['content_header' => 'Manage Surveyor'] + $dom_data)
						->partial('content', 'setup/surveyors/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$filters = [
				[
	                'field' => 'filter_type',
	                'label' => 'Surveyor Type',
	                'rules' => 'trim|integer|exact_length[1]|in_list[1,2]',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', '1' => 'Individual', '2' => 'Company'],
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
			        'label' => 'Surveyor Name',
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
						'type' 		=> $this->input->post('filter_type') ?? NULL,
						'active' 	=> $this->input->post('filter_active') ?? NULL,
						'keywords' 	=> $this->input->post('filter_keywords') ?? ''
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
			$records = $this->surveyor_model->rows($params);
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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('surveyors', 'edit.surveyor') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->surveyor_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $from_widget, $widget_reference);

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/surveyors/_form_box',
			[
				'form_elements' => $this->surveyor_model->validation_rules,
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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('surveyors', 'add.surveyor') )
		{
			$this->dx_auth->deny_access();
		}

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record, $from_widget, $widget_reference);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/surveyors/_form_box',
			[
				'form_elements' => $this->surveyor_model->validation_rules,
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

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;


			/**
			 * Validate Post before Uploading Picture
			 */
			$rules = array_merge($this->surveyor_model->validation_rules, get_contact_form_validation_rules());
			$this->form_validation->set_rules($rules);

			if( $this->form_validation->run() === TRUE )
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
						$done = $this->surveyor_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->surveyor_model->log_activity($done, 'C'): '';
					}
					else
					{
						// Now Update Data
						$done = $this->surveyor_model->update($record->id, $data, TRUE) && $this->surveyor_model->log_activity($record->id, 'E');
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

				$record 			= $this->surveyor_model->find( $action === 'add' ? $done : $record->id );
				$single_row 		=  'setup/surveyors/_single_row';
				if($action === 'add' && $from_widget === 'y' )
				{
					$single_row = 'setup/surveyors/_single_row_widget';
				}
				$html = $this->load->view($single_row, ['record' => $record, 'widget_reference' => $widget_reference], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-surveyor' : '#_data-row-' . $record->id,
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
				'form' 			=> $this->load->view('setup/surveyors/_form',
									[
										'form_elements' => $this->surveyor_model->validation_rules,
										'record' 		=> $record
									], TRUE)
			]);
		}

		return $return_data;
	}

		/**
		 * Sub-function: Upload Surveyor Profile Picture
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
	 * Delete a Surveyor
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('surveyors', 'delete.surveyor') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->surveyor_model->find($id);
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
		if( !safe_to_delete( 'Surveyor_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->surveyor_model->delete($record->id);

		if($done)
		{
			/**
			 * Delete Media if any
			 */
			if($record->picture)
			{
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
     * View Surveyor Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->surveyor_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		$this->data['site_title'] = 'Surveyor Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Surveyor Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Surveyors' => 'surveyors', 'Details' => NULL]
						])
						->partial('content', 'setup/surveyors/_details', compact('record'))
						->render($this->data);

    }
}