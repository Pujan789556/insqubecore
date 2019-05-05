<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customers Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Customers extends MY_Controller
{
	/**
	 * Files Upload Path
	 */
	public static $media_upload_path = INSQUBE_MEDIA_ROOT . 'media/customers/';

	// --------------------------------------------------------------------

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

		// URL Base
		$this->_url_base 		 = 	$this->router->class;
		$this->_view_base 		 =  $this->router->class;

		$this->data['_url_base'] 	= $this->_url_base; // for view to access
		$this->data['_view_base'] 	= $this->_view_base;
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
	function page( $layout='f', $from_widget='n', $next_id = 0, $widget_reference = '' )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('customers', 'explore.customer') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= $this->_url_base . '/page/r/' . $from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-customer', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-customer',		// Filter Form ID
			'DOM_RowBoxId'			=> 'box-customers-rows' 				// Row Box ID
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
			$view = $from_widget === 'y' ? $this->_view_base . '/_find_widget' : $this->_view_base . '/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->_url_base . '/page/l/' . $from_widget . '/0/' . $widget_reference)
			]);
		}
		else if($layout === 'l')
		{
			$view = $this->_view_base . '/_list';
		}
		else
		{
			$view = $this->_view_base . '/_rows';
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
							$this->_view_base . '/_index_header',
							['content_header' => 'Manage Customers'] + $dom_data)
						->partial('content', $this->_view_base . '/_index', $data)
						->partial('dynamic_js', $this->_view_base . '/_customer_js')
						->render($this->data);
	}


		private function _get_filter_elements()
		{
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
		            '_extra_attributes' => ['data-hideonload' => 'yes', 'data-ref' => 'C'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_identification_no',
		            'label' => 'Citizenship/Passport Number',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes', 'data-ref' => 'I'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_dob',
		            'label' => 'Date of Birth',
		            'rules' => 'trim|alpha_dash|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes', 'data-ref' => 'I'],
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
						'code' 				=> $this->input->post('filter_code') ?? NULL,
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'company_reg_no' 	=> $this->input->post('filter_company_reg_no') ?? NULL,
						'identification_no' => $this->input->post('filter_identification_no') ?? NULL,
						'dob' 				=> $this->input->post('filter_dob') ?? NULL,
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
				'records' 			=> $records,
				'widget_reference' 	=> $widget_reference,
				'next_id'  => $next_id,
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id  . '/' . $widget_reference ) : NULL
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
		$this->page('l');
	}

	// --------------------------------------------------------------------

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
	// CRUD - FUNCTIONS
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
		if( !$this->dx_auth->is_authorized('customers', 'add.customer') )
		{
			$this->dx_auth->deny_access();
		}

		$record 		= NULL;
		$address_record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record, $address_record, $from_widget, $widget_reference);

		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_box',
			[
				'form_elements' 	=> $this->customer_model->v_rules('add'),
				'address_elements' 	=> $this->address_model->v_rules_add(),
				'record' 			=> $record,
				'action' 			=> 'add'
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
	public function edit($id, $from_widget = 'n', $widget_reference = '')
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('customers', 'edit.customer') )
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

		// Address Record
		$address_record = $this->address_model->get_by_type(IQB_ADDRESS_TYPE_CUSTOMER, $record->id);

		/**
		 * Locked Customer???
		 *
		 * NOTE: Admin Can Edit Locked Customer Information
		 */
		if(!$this->dx_auth->is_admin() && $record->flag_locked == IQB_FLAG_ON )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Unauthorized Action!',
				'message' 	=> 'You can not edit locked Customer.'
			], 403);
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $address_record, $from_widget, $widget_reference);


		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_box',
			[
				'form_elements' 	=> $this->customer_model->v_rules('edit'),
				'address_elements' 	=> $this->address_model->v_rules_edit($address_record),
				'record' 			=> $record,
				'address_record' 	=> $address_record,
				'action' 			=> 'edit'
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}



	// --------------------------------------------------------------------

	/**
	 * Edit a Customer App Identity
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit_app_identity($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('customers', 'edit.customer.api.identity') )
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


		/**
		 * Locked Customer???
		 *
		 * NOTE: Admin Can Edit Locked Customer Information
		 */
		if(!$this->dx_auth->is_admin() && $record->flag_locked == IQB_FLAG_ON )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Unauthorized Action!',
				'message' 	=> 'You can not edit locked Customer.'
			], 403);
		}

		// Form Submitted? Save the data
		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->customer_model->v_rules('app_identity');
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		// Now Update Data
        		$mobile_identity = $this->input->post('mobile_identity');
        		if(!$mobile_identity)
        		{
        			$mobile_identity = NULL; // You can remove mobile identity too.
        		}
				$done = $this->customer_model->change_app_identity($record->id, $mobile_identity);

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

				if($status === 'success' )
				{
					$ajax_data = [
						'message' => $message,
						'status'  => $status,
						'updateSection' => true,
						'hideBootbox' => true
					];

					$record 	= $this->customer_model->row( $record->id );
					$single_row =  $this->_view_base . '/_single_row';
					$view_data 	= [
						'record' 			=> $record,
						'address_record' 	=> $this->address_model->parse_address_record($record),
						'widget_reference' 	=> ''
					];

					$html = $this->load->view($single_row, $view_data, TRUE);
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#_data-row-customer-' . $record->id,
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];

					return $this->template->json($ajax_data);
				}
        	}
        	else
        	{
        		$status = 'error';
				$message = 'Validation Error.';
        	}

			$json_data = [
				'status' 	 => $status,
				'message' 	 => $message,
				'reloadForm' => true
			];
		}

		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_app_identity',
			[
				'form_elements' 	=> $this->customer_model->v_rules('app_identity'),
				'record' 			=> $record,
				'action' 			=> 'app_identity'
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Verify Customr KYC
	 *
	 * @param integer $id
	 * @return void
	 */
	public function verify_kyc($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('customers', 'verify.customer.kyc') )
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


		/**
		 * Locked Customer???
		 *
		 * NOTE: Admin Can Edit Locked Customer Information
		 */
		if(!$this->dx_auth->is_admin() && $record->flag_locked == IQB_FLAG_ON )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Unauthorized Action!',
				'message' 	=> 'You can not edit locked Customer.'
			], 403);
		}

		// Form Submitted? Save the data
		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->customer_model->v_rules('verify_kyc');
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		// Now Update Data
        		$flag_kyc_verified = $this->input->post('flag_kyc_verified');
				$done = $this->customer_model->verify_kyc($record->id, $flag_kyc_verified);

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

				if($status === 'success' )
				{
					$ajax_data = [
						'message' => $message,
						'status'  => $status,
						'updateSection' => true,
						'hideBootbox' => true
					];

					$record 	= $this->customer_model->row( $record->id );
					$single_row =  $this->_view_base . '/_single_row';
					$view_data 	= [
						'record' 			=> $record,
						'address_record' 	=> $this->address_model->parse_address_record($record),
						'widget_reference' 	=> ''
					];

					$html = $this->load->view($single_row, $view_data, TRUE);
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#_data-row-customer-' . $record->id,
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];

					return $this->template->json($ajax_data);
				}
        	}
        	else
        	{
        		$status = 'error';
				$message = 'Validation Error.';
        	}

			$json_data = [
				'status' 	 => $status,
				'message' 	 => $message,
				'reloadForm' => true
			];
		}

		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_verify_kyc',
			[
				'form_elements' 	=> $this->customer_model->v_rules('verify_kyc'),
				'record' 			=> $record,
				'action' 			=> 'verify_kyc'
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

		// Valid action?
		if( !in_array($action, array('add', 'edit', 'app_identity')) || !in_array($from_widget, array('y', 'n')))
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

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;

			$rules = array_merge($this->customer_model->v_rules($action), $this->address_model->v_rules_on_submit([],TRUE));
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
						// Add KYC Verfied Flag
						$data['flag_kyc_verified'] = IQB_FLAG_ON;
						$done = $this->customer_model->add($data);
					}
					else
					{
						// Now Update Data
						$done = $this->customer_model->edit($record->id, $address_record->id, $data);
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

				$record 			= $this->customer_model->row( $action === 'add' ? $done : $record->id );
				$single_row 		=  $this->_view_base . '/_single_row';
				if($action === 'add' && $from_widget === 'y' )
				{
					$single_row = $this->_view_base . '/_single_row_widget';
				}
				else if($action === 'edit' && $from_widget === 'y' )
				{
					$single_row = $this->_view_base . '/snippets/_widget_profile';
				}

				$view_data = [
					'record' => $record,
					'address_record' => $this->address_model->parse_address_record($record),
					'widget_reference' => $widget_reference
				];

				$html = $this->load->view($single_row, $view_data, TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add'
										? '#box-customers-rows'
										: ( $from_widget === 'n' ? '#_data-row-customer-' . $record->id : '#iqb-widget-customer-profile' ),
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
				'form' 			=> $this->load->view($this->_view_base . '/_form',
									[
										'form_elements' 	=> $this->customer_model->v_rules($action),
										'address_elements' 	=> $this->address_model->v_rules_on_submit(),
										'record' 			=> $record,
										'action' 			=> $action
									], TRUE)
			]);
		}

		return $return_data;
	}

		public function _cb_valid_mobile_identity($mobile_identity)
		{
			$id   = (int)$this->input->post('id');

			/**
			 * Already exists on Mobile App User Databae?
			 */
	        $this->load->model('api/app_user_model', 'app_user_model');
	        if($id)
	        {
	        	$where = [
		        	'mobile'    		=> $mobile_identity,
	                'auth_type' 		=> IQB_API_AUTH_TYPE_CUSTOMER,
	                'auth_type_id !=' 	=> $id,
		        ];
	        }
	        else
	        {
	        	$where = [
		        	'mobile'    	=> $mobile_identity
		        ];
	        }
	        if( $this->app_user_model->check_duplicate($where) )
	        {
	            $this->form_validation->set_message('_cb_valid_mobile_identity', 'The %s already exists in Mobile App User. The %s must be unique to all mobile User.');
	            return FALSE;
	        }

			/**
			 * Already exists on Customer Databae?
			 */
	        if( $this->customer_model->check_duplicate(['mobile_identity' => $mobile_identity], $id))
	        {
	            $this->form_validation->set_message('_cb_valid_mobile_identity', 'The %s already exists. The %s must be unique to all customer.');
	            return FALSE;
	        }

	        return TRUE;
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
	 * Edit a Recrod from Endorsement
	 *
	 *
	 * @param integer $policy_id
	 * @param integer $txn_id
	 * @param integer $id
	 * @return void
	 */
	public function edit_endorsement($policy_id, $txn_id, $id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('customers', 'edit.customer') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->customer_model->get_for_endorsement($policy_id, $txn_id, $id);

		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Object not found!'
			],404);
		}

		// Address Record
		$address_record = $this->address_model->get_by_type(IQB_ADDRESS_TYPE_CUSTOMER, $record->id);

		 // The above query validates the flag_current, so we get directly txn data here
		$this->load->model('endorsement_model');
		$endorsement_record = $this->endorsement_model->get($txn_id);
		if(!$endorsement_record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Endorsement not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->policy_branch_id);


		/**
		 * Editable Permission? We should check permission of Txn not of Policy
		 */
		_ENDORSEMENT_is_editable($endorsement_record->status, $endorsement_record->flag_current);


		/**
		 * Endorsement Type Allows Customer to Edit?
		 */
		if( !_ENDORSEMENT_is_customer_editable($endorsement_record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Invalid Endorsement Type!',
				'message' 	=> 'You <strong>CAN NOT EDIT</strong> customer information for this type of Transaction/Endorsement.'
			],403);
		}

		/**
         * Do we have audit data available? If yes, pass it instead of policy's original data
         *
         * !!!NOTE: We need to pass the original record for getting old data. That's why clone.
         */
		$edit_record 		 = clone $record;
		$edit_address_record = clone $address_record;
        $audit_record 		 = $endorsement_record->audit_customer ? json_decode($endorsement_record->audit_customer) : NULL;
        if($audit_record)
        {
            // New Data
            $new_data_customer = (array)$audit_record->new->customer;
            $new_data_address = (array)$audit_record->new->address;

            // Overwrite the Customer Record with New Data
            foreach($new_data_customer as $key=>$value)
            {
            	$edit_record->{$key} = $value;
            }

            // Overwrite the Address Record with New Data
            foreach($new_data_address as $key=>$value)
            {
            	$edit_address_record->{$key} = $value;
            }
        }

		/**
		 * Prepare Common Form Data to pass to form view
		 */
		$v_rules 	= $this->customer_model->v_rules('endorsement');
		$form_data = [
			'form_elements' 	=> $v_rules,
			'address_elements' 	=> $this->address_model->v_rules_edit($edit_address_record),
			'record' 			=> $edit_record,
			'address_record' 	=> $edit_address_record,
			'action'			=> 'edit'
		];

		// Form Submitted? Save the data
		$this->_save_endorsement($form_data, $v_rules, $record, $address_record, $endorsement_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record from Endorsement
	 *
	 */
	private function _save_endorsement($form_data, $v_rules, $record, $address_record, $endorsement_record)
	{
		/**
		 * Form Submitted?
		 */
		$edit_record = $form_data['record'];
		if( $this->input->post() )
		{
			$done = FALSE;

			// Extract Old Profile Picture if any
			$picture = $edit_record->picture ?? NULL;

			// $v_rules = array_merge($v_rules, get_contact_form_validation_rules());
			$v_rules = array_merge($v_rules, $this->address_model->v_rules_on_submit([],TRUE));
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{

        		/**
				 * Upload Image If any?
				 */
				$upload_result 	= $this->_upload_profile_picture($picture);
				$status 		= $upload_result['status'];
				$message 		= $upload_result['message'];
				$files 			= $upload_result['files'];
				$picture 		= $status === 'success' ? $files[0] : $picture;

				if( $status === 'success' || $status === 'no_file_selected')
	            {
	            	$post_data = $this->input->post();
        			$post_data['picture'] = $picture;

        			$audit_data = [
	        			'endorsement_id' 	=> $endorsement_record->id,
	        			'customer_id'  		=> $record->id,
	        			'audit_customer' 		=> $this->_get_endorsement_audit_data($record, $address_record, $post_data)
	        		];

	        		/**
	        		 * Save Data (Insert if new, Update else)
	        		 */
	        		$this->load->model('audit_endorsement_model');
	        		if($endorsement_record->audit_endorsement_id)
	        		{
	        			$done = $this->audit_endorsement_model->update($endorsement_record->audit_endorsement_id, $audit_data, TRUE);
	        		}
	        		else
	        		{
	        			$done = $this->audit_endorsement_model->insert($audit_data, TRUE);
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

				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'updateSection' => false,
					'hideBootbox' 	=> true
				]);
        	}
        	else
        	{
        		return $this->template->json([
					'status' 		=> 'error',
					'message' 		=> validation_errors()
				]);
        	}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view($this->_view_base . '/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

		private function _get_endorsement_audit_data($old_record, $old_address_record, $post_data)
		{
			$old_data 	= [];
			$new_data 	= [];
			$old_record 		= (array)$old_record;
			$old_address_record = (array)$old_address_record;

			// Prepare Contact Data
			// $post_data 	= $this->customer_model->prepare_contact_data($post_data);

			// Customer Data
			foreach($this->customer_model->endorsement_fields['customer'] as $key)
			{
				$old_data['customer'][$key] = $old_record[$key] ?? NULL;
				$new_data['customer'][$key] = $post_data[$key] ?? NULL;
			}

			// Address Data
			foreach($this->customer_model->endorsement_fields['address'] as $key)
			{
				$old_data['address'][$key] = $old_record[$key] ?? NULL;
				$new_data['address'][$key] = $post_data[$key] ?? NULL;
			}

			return json_encode([
				'new' => $new_data,
				'old' => $old_data
			]);
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
				delete_insqube_document(self::$media_upload_path . $record->picture);
			}

			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-customer-'.$record->id
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

		$address_record = $this->address_model->get_by_type(IQB_ADDRESS_TYPE_CUSTOMER, $record->id);

		// Helpers
		$this->load->helper('object');

		// Prepare Data
		$this->load->model('object_model');
		$data = [
			'record' 		=> $record,
			'address_record' => $address_record
		];



		$this->data['site_title'] = 'Customer Details | ' . $record->full_name_en;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Customer Details <small>' . $record->full_name_en . '</small>',
								'breadcrumbs' => ['Customers' => 'customers', 'Details' => NULL]
						])
						->partial('content', $this->_view_base . '/_details', $data)
						->partial('dynamic_js', $this->_view_base . '/_customer_js')
						->render($this->data);

    }
}