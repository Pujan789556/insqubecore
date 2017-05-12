<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customers Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Customers extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Customers';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'customers',
		]);

		// Load Model
		$this->load->model('customer_model');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'customers/';
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
	// SEARCH/FILTER - FUNCTIONS
	// --------------------------------------------------------------------


	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */

	// $layout, $from_widget, $next_id, $ajax_extra
	function page( $layout='f', $from_widget='n', $next_id = 0, $ajax_extra = [] )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'explore.customer') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;
		$next_url_base = 'customers/page/r/'.$from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-customer', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-customer' 			// Filter Form ID
		];

		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id );
		$data = array_merge($data, $dom_data);
		// echo $this->db->last_query();exit;
		/**
		 * Widget Specific Data
		 */
		$data['_flag__show_widget_row'] = $from_widget === 'y';


		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = $from_widget === 'y' ? 'customers/_find_widget' : 'customers/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('customers/page/l/' . $from_widget )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'customers/_list';
		}
		else
		{
			$view = 'customers/_rows';
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
							'customers/_index_header',
							['content_header' => 'Manage Customers'] + $dom_data)
						->partial('content', 'customers/_index', $data)
						->partial('dynamic_js', 'customers/_customer_js')
						->render($this->data);
	}


		private function _get_filter_elements()
		{
			$select = ['' => 'Select ...'];
			$filters = [
				[
	                'field' => 'filter_type',
	                'label' => 'Customer Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[I,C]',
	                '_id'       => 'filter-type',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', 'I' => 'Individual', 'C' => 'Company'],
	            ],
				[
	                'field' => 'filter_code',
	                'label' => 'Customer Code',
	                'rules' => 'trim|alpha_numeric|max_length[12]',
	                '_type'     => 'text',
	            ],
	            [
		            'field' => 'filter_company_reg_no',
		            'label' => 'Company Reg Number',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' => 'yes'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_citizenship_no',
		            'label' => 'Citizenship Number',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_passport_no',
		            'label' => 'Passport Number',
		            'rules' => 'trim|alpha_dash|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes'],
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" data-title="Customer Name, PAN, Citizenship, Passport etc..."'
				],
			];
			return $filters;
		}

		private function _get_filter_data( $next_url_base, $next_id = 0)
		{
			$params = [];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'code' 				=> $this->input->post('filter_code') ?? NULL,
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'company_reg_no' 	=> $this->input->post('filter_company_reg_no') ?? NULL,
						'citizenship_no' 	=> $this->input->post('filter_citizenship_no') ?? NULL,
						'passport_no' 		=> $this->input->post('filter_passport_no') ?? NULL,
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
			$records = $this->customer_model->rows($params);
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
				'next_id'  => $next_id,
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id ) : NULL
			];
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
		$this->page('l', 'n');
	}

	// --------------------------------------------------------------------

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l', 'n');
	}

	// --------------------------------------------------------------------
	// CRUD - FUNCTIONS
	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id, $from_widget = 'n')
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'edit.customer') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->customer_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $from_widget);


		// No form Submitted?
		$json_data['form'] = $this->load->view('customers/_form_box',
			[
				'form_elements' => $this->customer_model->validation_rules,
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
	public function add( $from_widget='n' )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'add.customer') )
		{
			$this->dx_auth->deny_access();
		}

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record, $from_widget);


		// No form Submitted?
		$json_data['form'] = $this->load->view('customers/_form_box',
			[
				'form_elements' => $this->customer_model->validation_rules,
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
	 * @return array
	 */
	private function _save($action, $record = NULL, $from_widget='n')
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		// Valid "from" ?
		if( !in_array($from_widget, array('y', 'n')))
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

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;

			$rules = array_merge($this->customer_model->validation_rules, get_contact_form_validation_rules());
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
						$done = $this->customer_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->customer_model->log_activity($done, 'C'): '';
					}
					else
					{
						// Now Update Data
						$done = $this->customer_model->update($record->id, $data, TRUE) && $this->customer_model->log_activity($record->id, 'E');
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
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				if($action === 'add')
				{
					$record = $this->customer_model->find($done);
					$single_row = $from_widget === 'y' ? 'customers/_single_row_widget' : 'customers/_single_row';
					$html = $this->load->view($single_row, ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-customer',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * Widget or Row?
					 */
					$record = $this->customer_model->find($record->id);
					$view 	= $from_widget === 'n' ? 'customers/_single_row' : 'customers/snippets/_widget_profile';
					$html 	= $this->load->view($view , ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> $from_widget === 'n' ? '#_data-row-customer-' . $record->id : '#iqb-widget-customer-profile',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
				}
				return $this->template->json($ajax_data);
			}

			// Form
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 			=> $this->load->view('customers/_form',
									[
										'form_elements' => $this->customer_model->validation_rules,
										'record' 		=> $record
									], TRUE)
			]);
		}

		return $return_data;
	}

		/**
		 * Sub-function: Upload Customer Profile Picture
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
	 * Delete a Customer
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'delete.customer') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->customer_model->find($id);
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
		if( !safe_to_delete( 'Customer_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->customer_model->delete($record->id);

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
	// DETAILS EXPLORATION
	// --------------------------------------------------------------------

    /**
     * View Customer Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'explore.customer') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->customer_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Helpers
		$this->load->helper('object');

		// Prepare Data
		$this->load->model('object_model');
		$data = [
			'record' 		=> $record,
			'objects' 		=> $this->object_model->get_by_customer($record->id)
		];



		$this->data['site_title'] = 'Customer Details | ' . $record->full_name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Customer Details <small>' . $record->full_name . '</small>',
								'breadcrumbs' => ['Customers' => 'customers', 'Details' => NULL]
						])
						->partial('content', 'customers/_details', $data)
						->partial('dynamic_js', 'customers/_customer_js')
						->render($this->data);

    }
}