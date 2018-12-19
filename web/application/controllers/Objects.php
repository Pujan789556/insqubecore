<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Objects Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Objects extends MY_Controller
{
	/**
	 * Files Upload Path - Data (Invoices)
	 */
	public static $data_upload_path = INSQUBE_DATA_ROOT . 'objects/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Objects';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'objects',
		]);

		// Helper
		$this->load->config('policy');
		$this->load->helper('policy');
		$this->load->helper('object');

		// Load Model
		$this->load->model('object_model');
		$this->load->model('portfolio_model');

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
	function page( $layout='f', $from_widget='n', $customer_id=0, $next_id = 0,  $ajax_extra = [] )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('objects', 'explore.object') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$customer_id = (int)$customer_id; // Required if request is coming from find widget
		$next_id = (int)$next_id;
		$next_url_base = 'objects/page/r/'.$from_widget . '/' . $customer_id;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-object', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-object' 			// Filter Form ID
		];

		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id );
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
			/**
			 * Request Specific things
			 * If request is coming from widget, we need to have customer record
			 * to have add button on find widget
			 */
			$customer_record = null;
			if($from_widget === 'y')
			{
				$view = 'objects/_find_widget';

				$this->load->model('customer_model');
				$customer_record = $this->customer_model->find($customer_id);
				if(!$customer_record)
				{
					$this->template->render_404('','Please select customer first.');
				}
			}
			else
			{
				$view = 'objects/_index';
			}

			$data = array_merge($data, [
				'filters' 			=> $this->_get_filter_elements(),
				'filter_url' 		=> site_url('objects/page/l/' . $from_widget . '/' . $customer_id),
				'customer_record' 	=> $customer_record
			]);
		}
		else if($layout === 'l')
		{
			$view = 'objects/_list';
		}
		else
		{
			$view = 'objects/_rows';
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
							'objects/_index_header',
							['content_header' => 'Manage Objects'] + $dom_data)
						->partial('content', 'objects/_index', $data)
						->render($this->data);
	}

	// Find Widget
	function find( $customer_id, $portfolio_id )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('objects', 'explore.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Customer?
		$this->load->model('customer_model');
		$customer_id = (int)$customer_id;
		$customer_record = $this->customer_model->find($customer_id);
		if(!$customer_record)
		{
			$this->template->render_404('','Please select customer first.');
		}

		// Valid Portfolio
		$portfolio_id 		= (int)$portfolio_id;
		$portfolio_record 	= $this->portfolio_model->find($portfolio_id);
		if(!$portfolio_record)
		{
			$this->template->render_404('', 'Please select customer first.');
		}
		$data = [
			'records' 					=> $this->object_model->get_by_customer($customer_record->id, $portfolio_id),
			'customer_record' 			=> $customer_record,
			'portfolio_record' 		 	=> $portfolio_record,
			'add_url' 					=> 'objects/add/' . $customer_id . '/y/' . $portfolio_id,
			'_flag__show_widget_row' 	=> TRUE
		];
		$html = $this->load->view('objects/_find_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];
		$this->template->json($ajax_data);
	}


		private function _get_filter_elements($portfolio_id=0)
		{
			$portfolio_id = $portfolio_id ? $portfolio_id : '';

			$filters = [
				[
	                'field' => 'filter_portfolio',
	                'label' => 'Portfolio',
	                'rules' => 'trim|integer|max_length[11]',
	                '_id'       => 'filter-portfolio',
	                '_type'     => 'dropdown',
	                '_default' 	=> $portfolio_id,
	                '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(),
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" data-title="Keywords..."'
				],
			];
			return $filters;
		}

		private function _get_filter_data( $next_url_base, $next_id = 0, $portfolio_id=0, $customer_id =0)
		{
			$params = [];

			$portfolio_id = $portfolio_id ? $portfolio_id : NULL;
			$customer_id = $customer_id ? $customer_id : NULL;

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements($portfolio_id);
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'portfolio_id' 		=> $portfolio_id ?? ($this->input->post('filter_portfolio') ?? NULL),
						'customer_id'		=> $customer_id,
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
			else
			{
				$params = [
					'portfolio_id' 		=> $portfolio_id,
					'customer_id'		=> $customer_id
				];
			}

			$next_id = (int)$next_id;
			if( $next_id )
			{
				$params['next_id'] = $next_id;
			}

			/**
			 * Get Search Result
			 */
			$records = $this->object_model->rows($params);
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

	/**
	 * Get all Objects by Customer
	 *
	 * @param int $customer_id
	 * @param int $flush_cache
	 * @return JSON
	 */
	function by_customer($customer_id, $flush_cache = 0)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('policies', 'explore.object', TRUE);

		$customer_id 	= (int)$customer_id;

		/**
		 * Clear Cache??
		 */
		if($flush_cache)
		{
			$cache_var = 'object_cst_' . $customer_id;
			$this->object_model->clear_cache($cache_var);
		}

		$records = $this->object_model->get_by_customer($customer_id);
		$data = [
			'records' 					=> $records,
			'customer_id' 				=> $customer_id,
			'next_id' 					=> NULL,
			'add_url' 					=> 'objects/add/' . $customer_id
		];
		$html = $this->load->view('objects/_customer/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------
	// CRUD OPERATIONS
	// --------------------------------------------------------------------


	/**
	 * Edit a Recrod
	 *
	 * !!! IMPORTANT NOTE !!!
	 * We edit only object attributes not the object's portfolio
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id, $from_widget = 'n')
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('objects', 'edit.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->object_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		/**
		 * Is object editable?
		 * --------------------
		 *
		 * Edit Constraints:
		 * 		1. Flag Lock is ON
		 * 			- You can not edit object if it's lock flag is ON.
		 * 			- This flag is set ON once a policy is verified.
		 *
		 * Note:
		 * 		- Upon policy expire/cancel, the object lock flag should be released
		 */
		try {
			if(!$this->object_model->is_editable($record))
			{
				$this->template->json(['status' => 'error', 'title' => 'Operatiion Not Permitted.', 'message' => 'This object is not editable.'], 404);
			}
		} catch (Exception $e) {
			$this->template->json(['status' => 'error', 'title' => 'Exception Occured', 'message' => $e->getMessage()], 404);
		}


		// Valid Customer Record ?
		$this->load->model('customer_model');
		$customer_record = $this->customer_model->find($record->customer_id);
		if(!$customer_record)
		{
			$this->template->render_404();
		}

		/**
		 * Portfolio Record
		 */
		$portfolio_record 		= $this->portfolio_model->find($record->portfolio_id);

		/**
		 * Prepare Common Form Data to pass to form view
		 */
		$action_url = 'objects/edit/' . $record->id . '/' . $from_widget;
		$v_rules = $this->object_model->validation_rules['edit'];
		$form_data = [
			'form_elements' 	=> $v_rules,
			'record' 			=> $record,
			'portfolio_record' 	=> $portfolio_record,
			'action' 			=> 'edit',
			'action_url' 		=> $action_url,
			'from_widget' 		=> $from_widget,

			// Attribute Elements
			'html_form_attribute_components' => $this->get_attribute_form($record->portfolio_id, 'html', json_decode($record->attributes))
		];

		// Form Submitted? Save the data else load the form
		$this->_save($customer_record, $form_data, $v_rules);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * Object Form is Called From Two Places
     *
     * a. Customer Object Tab
     *      In this case, you can create object of any portfolio. So you must choose portfolio first.
     *
     * b. Pollicy Add Form (Add Widget)
     *      In this case, you have both the customer and portfolio selected. You will only need the object
     *      attributes of specified portfolio
	 *
	 * @return void
	 */
	public function add( $customer_id, $from_widget='n', $portfolio_id = 0)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('objects', 'add.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid From Widget Param?
		if( !in_array($from_widget, ['y','n']) )
		{
			$this->template->render_404();
		}

		// Valid Customer Record ?
		$this->load->model('customer_model');
		$customer_id 		= (int)$customer_id;
		$customer_record 	= $this->customer_model->find($customer_id);
		if(!$customer_record)
		{
			$this->template->render_404();
		}

		$record = NULL;

		/**
		 * If we are calling from Widget, We must have portfolio
		 */
		$portfolio_record 		= NULL;
		$portfolio_id 			= (int)$portfolio_id;
		if( $from_widget === 'y' )
		{
			$portfolio_record 		= $this->portfolio_model->find($portfolio_id);

			// Both record must exist and MUST match parent child relation
			if( !$portfolio_record )
			{
				$this->template->render_404('', 'Please supply a valid Portfolio');
			}
		}

		// If coming from widget, we only need object attributes
		$html_form_attribute_components = '';
		if($from_widget === 'y')
		{
			$html_form_attribute_components = $this->get_attribute_form($portfolio_id, 'html');
		}

		/**
		 * Prepare Common Form Data to pass to form view
		 */
		$action_url = 'objects/add/' . $customer_id . '/' . $from_widget . '/' . $portfolio_id;
		$v_rules = $this->object_model->validation_rules[$from_widget === 'n' ? 'add' : 'add_widget'];
		$form_data = [
			'form_elements' 	=> $v_rules,
			'record' 			=> $record,
			'portfolio_record' 	=> $portfolio_record,
			'action' 			=> 'add',
			'action_url' 		=> $action_url,
			'from_widget' 		=> $from_widget,

			'html_form_attribute_components' => $html_form_attribute_components
		];

		// Form Submitted? Save the data else load the form
		$this->_save($customer_record, $form_data, $v_rules);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object $customer_record Customer Record
	 * @param object|null $record Record Object or NULL
	 * @param char 	$from_widget
	 * @return array
	 */
	private function _save($customer_record, $form_data, $v_rules)
	{
		// Valid action?
		$action 		= $form_data['action'];
		$from_widget 	= $form_data['from_widget'];
		if( !in_array($action, array('add', 'edit')) || !in_array($from_widget, ['y', 'n'])  )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			],404);
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$record = $form_data['record'];
			if($action === 'add')
			{
				$portfolio_id = (int)$this->input->post('portfolio_id');

				if( !$portfolio_id )
				{
					return $this->template->json([
						'title' => 'Validation Error!',
						'status' => 'error',
						'message' => 'Please select Portfolio first.'
					],404);
				}
			}
			else
			{
				$portfolio_id = (int)$record->portfolio_id;
			}

			$done = FALSE;

			/**
			 * Object Validation Rules
			 */
			try {

				$obj_v_rules 	= _OBJ_validation_rules($portfolio_id, TRUE);

			} catch (Exception $e) {

				return $this->template->json(['status' => 'error', 'title' => 'Exception Occured', 'message' => $e->getMessage()], 404);
			}


			$v_rules 	= array_merge($v_rules, $obj_v_rules);
            $this->form_validation->set_rules($v_rules);

			if($this->form_validation->run() === TRUE )
        	{
        		$object_data = [];
        		$data = $this->input->post();

        		if($action === 'add')
				{
					$object_data = [
						'portfolio_id' 		=> $data['portfolio_id'],
						'customer_id'  		=> $customer_record->id
					];
				}


				/**
				 * Perform Object Pre Save Tasks
				 */
				try {

					$data = _OBJ_pre_save_tasks($portfolio_id, $data, $record);

				} catch (Exception $e) {

					return $this->template->json(['status' => 'error', 'title' => 'Exception Occured!', 'message' => $e->getMessage()], 404);
				}


				// Object attributes
        		$object_data['attributes'] = json_encode($data['object']);

        		/**
				 * Compute Sum Insured Amount & SI Breakdown
				 */
        		try {

					$si_data 	 = _OBJ_compute_sum_insured_amount($portfolio_id, $data['object']);
					$object_data = array_merge($object_data, $si_data);

				} catch (Exception $e) {

					return $this->template->json(['status' => 'error', 'title' => 'Exception Occured!', 'message' => $e->getMessage()], 404);
				}

        		// echo '<pre>'; print_r($object_data);exit;

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->object_model->insert($object_data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->object_model->update($record->id, $object_data, TRUE);
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
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'hideBootbox' => true
				];

				$record 	= $this->object_model->row($action === 'add' ? $done : $record->id);
				if($action === 'add')
				{
					$single_row = $from_widget === 'y' ? 'objects/_single_row_widget' : 'objects/_single_row';
					$html = $this->load->view($single_row, ['record' => $record], TRUE);

					$ajax_data['updateSection'] = true;
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-object',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * From Widget or List
					 */
					$single_row = $from_widget === 'y' ? 'objects/snippets/_object_card' : 'objects/_single_row';
					$html = $this->load->view($single_row, ['record' => $record, '__flag_object_editable' => TRUE], TRUE);

					$ajax_data['multipleUpdate'] =[
						[
							'box' 		=> $from_widget === 'n' ? '#_data-row-object-' . $record->id : '#iqb-object-card',
							'html' 		=> $html,
							'method' 	=> 'replaceWith'
						],

						// Since Every Edit of Object Resets the Premium, Let's Update the section if present
						[
							'box' 		=> '#_premium-details',
							'html' 		=> '<tr><td colspan="2" class="text-muted text-center">No Premium Information Found!</td></tr>',
							'method' 	=> 'html'
						]
					];
				}
				return $this->template->json($ajax_data);
			}
			else
			{
				// echo validation_errors();exit;
				$attributes = $record ? json_decode($record->attributes) : NULL;
				$form_data['html_form_attribute_components'] = $this->get_attribute_form($portfolio_id, 'html', $attributes);

				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'form' 			=> $this->load->view('objects/_form', $form_data, TRUE)
				]);
			}
		}


		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('objects/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod from Endorsement
	 *
	 *
	 * @param integer $policy_id
	 * @param integer $endorsement_id
	 * @param integer $id
	 * @return void
	 */
	public function edit_endorsement($policy_id, $endorsement_id, $id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('objects', 'edit.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->object_model->get_for_endorsement($policy_id, $endorsement_id, $id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Object not found!'
			],404);
		}

		 // The above query validates the flag_current, so we get directly txn data here
		$this->load->model('endorsement_model');
		$endorsement_record = $this->endorsement_model->get($endorsement_id);
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
		belongs_to_me($record->branch_id);


		/**
		 * Editable Permission? We should check permission of Txn not of Policy
		 */
		_ENDORSEMENT_is_editable($endorsement_record->status, $endorsement_record->flag_current);


		/**
		 * Endorsement Type Allows Object to Edit?
		 */
		if( !_ENDORSEMENT_is_object_editable_by_type($endorsement_record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Invalid Endorsement Type!',
				'message' 	=> 'You <strong>CAN NOT EDIT</strong> object information for this type of Transaction/Endorsement.'
			],403);
		}


		/**
         * Do we have audit data available? If yes, pass it instead of policy's original data
         *
         * !!!NOTE: We need to pass the original record for getting old data. That's why clone.
         */
		$edit_record 	= clone $record;
        $audit_record 	= $endorsement_record->audit_object ? json_decode($endorsement_record->audit_object) : NULL;
        if($audit_record)
        {
            // Get the New data
            $new_data = (array)$audit_record->new;

            // Overwrite the Policy record with this data
            foreach($new_data as $key=>$value)
            {
            	$edit_record->{$key} = $value;
            }
        }


        /**
		 * Portfolio Record
		 */
		$portfolio_record 		= $this->portfolio_model->find($record->portfolio_id);


		/**
		 * Prepare Common Form Data to pass to form view
		 */
		$from_widget = 'n';
		$action_url = current_url();
		$v_rules = $this->object_model->validation_rules['edit'];
		$form_data = [
			'form_elements' 	=> $v_rules,
			'record' 			=> $edit_record,
			'portfolio_record' 	=> $portfolio_record,
			'action' 			=> 'edit',
			'action_url' 		=> $action_url,
			'from_widget' 		=> $from_widget,

			// Attribute Elements
			'html_form_attribute_components' => $this->get_attribute_form($record->portfolio_id, 'html', json_decode($edit_record->attributes))
		];

		// Form Submitted? Save the data
		$this->_save_endorsement($form_data, $v_rules, $record, $endorsement_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record from Endorsement
	 *
	 */
	private function _save_endorsement($form_data, $v_rules, $record, $endorsement_record)
	{
		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done = FALSE;

			/**
			 * Object Validation Rules
			 */
			try {

				$obj_v_rules 	= _OBJ_validation_rules($record->portfolio_id, TRUE);

			} catch (Exception $e) {

				return $this->template->json(['status' => 'error', 'title' => 'Exception Occured', 'message' => $e->getMessage()], 404);
			}

			$v_rules 	= array_merge($v_rules, $obj_v_rules);
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		/**
				 * Perform Object Pre Save Tasks
				 */
				try {

					$data = _OBJ_pre_save_tasks($record->portfolio_id, $data, $record);

				} catch (Exception $e) {

					return $this->template->json(['status' => 'error', 'title' => 'Exception Occured!', 'message' => $e->getMessage()], 404);
				}

        		/**
        		 * Prepare Post Data
        		 */
        		$post_data['attributes'] 		= json_encode($data['object']);

        		/**
				 * Compute Sum Insured Amount
				 */
        		try {

					$si_data 	 = _OBJ_compute_sum_insured_amount($record->portfolio_id, $data['object']);
					$post_data   = array_merge($post_data, $si_data);

				} catch (Exception $e) {

					return $this->template->json(['status' => 'error', 'title' => 'Exception Occured!', 'message' => $e->getMessage()], 404);
				}

				/**
				 * Audit Data
				 */
				$audit_data = [
        			'endorsement_id' 	=> $endorsement_record->id,
        			'object_id'  		=> $record->id,
        			'audit_object' 		=> $this->_get_endorsement_audit_data($record, $post_data)
        		];


        		/**
        		 * Endorsement Data
        		 */
        		$si_latest 	= $si_data['amt_sum_insured'];
        		$si_old 	= $record->amt_sum_insured;
        		$endorsement_data = [
        			'amt_sum_insured_object' => $si_latest,
        			'amt_sum_insured_net' 	 => $si_latest - $si_old
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
					/**
					 * Update Endorsement Data
					 */
					$this->endorsement_model->save($endorsement_record->id, $endorsement_data);

					$status = 'success';
					$message = 'Successfully Updated.';
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
			'form' => $this->load->view('objects/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

		private function _get_endorsement_audit_data($old_record, $post_data)
		{
			$old_data 	= [];
			$new_data 	= [];
			$old_record = (array)$old_record;
			foreach(Object_model::$endorsement_fields as $key)
			{
				$old_data[$key] = $old_record[$key];
				$new_data[$key] = $post_data[$key] ?? NULL;
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
		if( !$this->dx_auth->is_authorized('objects', 'delete.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->object_model->find($id);
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
		if( !safe_to_delete( 'Object_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->object_model->delete($record->id);

		if($done)
		{

			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-object-'.$record->id
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
	// CRUD HELPER - FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Get Attribute Form, Sub-portfolio Dropdown
		 *
		 * @param integer $portfolio_id Portfolio ID
		 * @param string 	$method 	Return Method
		 * @param object 	$attributes 	Attribute Object
		 * @return json
		 */
		public function get_attribute_form( $portfolio_id, $method  = 'json', $attributes = NULL )
		{
			// Valid Record ?
			$portfolio_id 	= (int)$portfolio_id;

			/**
			 * Validation Rules
			 */
			try {

				$form_elements 	= _OBJ_validation_rules($portfolio_id);

			} catch (Exception $e) {

				return $this->template->json(['status' => 'error', 'title' => 'Exception Occured', 'message' => $e->getMessage()], 404);
			}

			/**
			 * Object Attribute Form
			 */
			try {

				$form_partial 	= _OBJ_attribute_form($portfolio_id);

			} catch (Exception $e) {

				return $this->template->json(['status' => 'error', 'title' => 'Exception Occured', 'message' => $e->getMessage()], 404);
			}


			// No form Submitted?
			$html = $this->load->view($form_partial,
				[
					'form_elements' => $form_elements,
					'record' 		=> $attributes
				], TRUE);

			if($method === 'html')
			{
				return $html;
			}

			// Return HTML
			$this->template->json(['html' => $html]);

		}

	// --------------------------------------------------------------------



	// --------------------------------------------------------------------
	// DETAILS EXPLORATION
	// --------------------------------------------------------------------


	public function popup($id)
	{
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('objects', 'explore.object') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->object_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		// belongs_to_me( $record->branch_id );

		try {

			$view = OBJECT__popup_view($record->portfolio_id);

		} catch (Exception $e) {

			return $this->template->json([
						'status' => 'error',
						'message' => $e->getMessage()
					], 500);
		}

		/**
		 * Return the Popup
		 */
		$this->template->json([
			'html' 	=> $this->load->view($view,['record' => $record], TRUE),
			'title' => 'Policy Object Details - ' .  $record->id
		]);
	}

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
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('objects', 'explore.object') )
		{
			$this->dx_auth->deny_access();
		}

		$this->template->render_404();

  //   	$id = (int)$id;
		// $record = $this->object_model->find($id);
		// if(!$record)
		// {
		// 	$this->template->render_404();
		// }


		// $this->data['site_title'] = 'Customer Details | ' . $record->full_name;
		// $this->template->partial(
		// 					'content_header',
		// 					'templates/_common/_content_header',
		// 					[
		// 						'content_header' => 'Customer Details <small>' . $record->full_name . '</small>',
		// 						'breadcrumbs' => ['Objects' => 'objects', 'Details' => NULL]
		// 				])
		// 				->partial('content', 'objects/_details', compact('record'))
		// 				->partial('dynamic_js', 'objects/_object_js')
		// 				->render($this->data);

    }

    // --------------------------------------------------------------------

    /**
     * Download a file related to Object
     *
     * @param string $filename
     * @return void
     */
	public function download($filename)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('objects', 'explore.object') )
		{
			$this->dx_auth->deny_access();
		}

		// Let's Download
		$this->load->helper('download');
        $download_file = self::$data_upload_path . $filename;
        if( file_exists($download_file) )
        {
            force_download($download_file, NULL, true);
        }
        else
        {
        	$this->template->render_404('', "Sorry! File Not Found.");
        }
	}

	// --------------------------------------------------------------------
	// PORTFOLIO - MOTOR - CRUD HELPER FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Motor Object Callback - Valid Vehicle engine number?
		 *
		 * @param type $engine_no
		 * @param type|null $id
		 * @return type
		 */
		public function _cb_motor_duplicate_engine_no($engine_no, $id=NULL)
		{
			$engine_no = $engine_no ??  $this->input->post('object[engine_no]');
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	        if( $this->object_model->_cb_motor_duplicate(['_motor_engine_no' => $engine_no], $id))
	        {
	            $this->form_validation->set_message('_cb_motor_duplicate_engine_no', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

		/**
		 * Motor Object Callback - Valid Vehicle chasis number?
		 *
		 * @param type $chasis_no
		 * @param type|null $id
		 * @return type
		 */
		public function _cb_motor_duplicate_chasis_no($chasis_no, $id=NULL)
		{
			$chasis_no = $chasis_no ??  $this->input->post('object[chasis_no]');
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	        if( $this->object_model->_cb_motor_duplicate(['_motor_chasis_no' => $chasis_no], $id))
	        {
	            $this->form_validation->set_message('_cb_motor_duplicate_chasis_no', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

		/**
		 * Motor Object Callback - Valid Vehicle registration number?
		 *
		 * @param type $reg_no
		 * @param type|null $id
		 * @return type
		 */
		public function _cb_motor_duplicate_reg_no($reg_no, $id=NULL)
		{
			$reg_no = $reg_no ??  $this->input->post('object[reg_no]');
			$reg_no_prefix = $this->input->post('object[reg_no_prefix]');
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	        if( $this->object_model->_cb_motor_duplicate(['_motor_reg_no' => $reg_no, '_motor_reg_no_prefix' => $reg_no_prefix], $id))
	        {
	            $this->form_validation->set_message('_cb_motor_duplicate_reg_no', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

		/**
		 * Motor Object Callback - Valid vehicle registration prefix?
		 *
		 * @param type $reg_no_prefix
		 * @return type
		 */
		public function _cb_motor_valid_reg_prefix($reg_no_prefix)
		{
			$this->load->model('vehicle_reg_prefix_model');

			$reg_no_prefix = $reg_no_prefix ??  $this->input->post('object[reg_no_prefix]');

	        if( !$this->vehicle_reg_prefix_model->exists(['name_en' => $reg_no_prefix]))
	        {
	            $this->form_validation->set_message('_cb_motor_valid_reg_prefix', 'Invalid Vehicle Registration Type.');
	            return FALSE;
	        }
	        return TRUE;
		}

		/**
		 * Lookup - Vehicle Registration Prefix
		 *
		 * @param string $query
		 * @return JSON
		 */
		public function motor_lookup_reg_no_prefix($query)
		{
			// Url Decode
			$query = urldecode($query);

			// Remove .json from query string
			$query = str_replace('.JSON', '', strtoupper($query));

			$this->load->model('vehicle_reg_prefix_model');
			$list = $this->vehicle_reg_prefix_model->lookup($query);
			echo json_encode($list);
			exit(0);
		}



	// --------------------------------------------------------------------
	//  PORTFOLIO - AGRICULTURE - CRUD HELPER FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Get Beema Samiti - Agriculture Breed Data by Category
		 * @param type $bs_agro_category_id
		 * @return type
		 */
		public function dd_agro_breed($bs_agro_category_id)
		{
			// Valid Record ?
			$bs_agro_category_id = (int)$bs_agro_category_id;
			$this->load->helper('ph_agr');

			$options = _OBJ_AGR_breed_dropdown($bs_agro_category_id);
			if( !empty($options))
			{
				$this->template->json([
					'status' => 'success',
					'options' => $options
				]);
			}
			else
			{
				$this->template->json([
					'status' => 'error',
					'message' => 'No breed found for supplied agriculture item category. Please ask your IT Support to add "Beema Samiti >> Agriculture Categories >> Breed" of selected "Agriculture Category" and try again.'
				], 404);
			}
		}
}