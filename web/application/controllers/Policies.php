<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policies Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Policies extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Policies';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('object_model');
		$this->load->model('policy_txn_model');

		// Policy Configuration/Helper
		$this->load->config('policy');
		$this->load->helper('policy');
		$this->load->helper('object');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'policies/';
	}

	// --------------------------------------------------------------------
	// SEARCH OPERATIONS
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
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'explore.policy') )
		{
			$this->dx_auth->deny_access();
		}


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

		$records 	= $this->policy_model->rows($params);
		$records 	= $records ? $records : [];
		$total 		= count($records);

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
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-policy', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-policy' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'policies/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'policies/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('policies/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'policies/_list';
		}
		else
		{
			$view = 'policies/_rows';
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

		/**
		 * Filter Configurations
		 */
		$data['filters'] = $this->_get_filter_elements();
		$data['filter_url'] = site_url('policies/filter/');

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'policies/_index_header',
							['content_header' => 'Manage Policies'] + $dom_data)
						->partial('content', 'policies/_index', $data)
						->partial('dynamic_js', 'customers/_customer_js')
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$this->load->model('portfolio_model');

			$select = ['' => 'Select ...'];
			$filters = [
				[
	                'field' => 'filter_type',
	                'label' => 'Policy Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[N,R]',
	                '_id'       => 'filter-type',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', 'N' => 'New', 'R' => 'Renewal'],
	            ],
	            [
	                'field' => 'filter_status',
	                'label' => 'Policy Status',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[D,A,E]',
	                '_id'       => 'filter-status',
	                '_type'     => 'dropdown',
	                '_data'     => get_policy_status_dropdown(),
	            ],
				[
	                'field' => 'filter_portfolio_id',
	                'label' => 'Portfolio',
	                'rules' => 'trim|integer|max_length[11]',
	                '_id'       => 'filter-status',
	                '_type'     => 'dropdown',
	                '_data'     => $select + $this->portfolio_model->dropdown_parent(),
	            ],
	            [
		            'field' => 'filter_code',
		            'label' => 'Policy Code',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_start_date',
		            'label' => 'Policy Start Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_end_date',
		            'label' => 'Policy End Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" title="Customer Name, PAN, Citizenship, Passport etc..."'
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
						'code' 				=> $this->input->post('filter_code') ?? NULL,
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'company_reg_no' 	=> $this->input->post('filter_company_reg_no') ?? NULL,
						'citizenship_no' 	=> $this->input->post('filter_citizenship_no') ?? NULL,
						'passport_no' 		=> $this->input->post('filter_passport_no') ?? NULL,
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
	// CRUD OPERATIONS
	// --------------------------------------------------------------------


	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'add.policy') )
		{
			$this->dx_auth->deny_access();
		}

		$form_data = [
			'form_elements' => $this->policy_model->validation_rules('add_edit_draft'),
			'record' 		=> NULL
		];

		// Form Submitted? Save the data
		$this->_save('add', $form_data);
	}

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
		// Capture the ID
		$id = (int)$id;

		// If Submit, must match (post ID = method ID)
		if($this->input->post())
		{
			$post_id = (int)$this->input->post('id');

			if($post_id !== $id)
			{
				return $this->template->json([
					'status' => 'error',
					'message' => 'Data mismatch (post vs method param)'
				],404);
			}
		}

		// Valid Record ?
		$record = $this->policy_model->row($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Editable?
		 */
		is_policy_editable($record->status);


		// Validation Rule
		$v_rules = $this->policy_model->validation_rules('add_edit_draft', FALSE, $record);

		// Object Details
		$object_record = $this->object_model->row($record->object_id);
		$record->object_name = _OBJ_select_text($object_record);
		$form_data = [
			'form_elements' => $v_rules,
			'record' 		=> $record
		];

		// Form Submitted? Save the data
		$this->_save('edit', $form_data, $from_widget);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $form_data, $from_widget = 'n')
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			],404);
		}

		// Valid "from" ?
		if( !in_array($from_widget, array('y', 'n')))
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
		$record = $form_data['record'];

		if( $this->input->post() )
		{
			$done = FALSE;

			// These Rules are Sectioned, We need to merge Together
			$v_rules = $this->policy_model->validation_rules('add_edit_draft', TRUE, $record);
            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->policy_model->add_debit_note($data); // No Validation on Model
				}
				else
				{

					// Now Update Data
					$done = $this->policy_model->edit_debit_note($record->id, $data);


					/**
					 * Policy Package Changed?
					 * --------------------------
					 * If changed, we have to reset the premium info
					 */
					if($done)
					{
						$this->__reset_premium_on_debitnote_update($record, $data);
					}
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

				if($action === 'add')
				{
					$record = $this->policy_model->row($done);
					$html = $this->load->view('policies/_single_row', ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-policy',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * Widget or Row?
					 */
					$record = $from_widget === 'n'
								? $this->policy_model->row($record->id)
								: $this->policy_model->get($record->id);

					$view = $from_widget === 'n'
									? 'policies/_single_row'
									: 'policies/tabs/_tab_overview';

					$html = $this->load->view($view, ['record' => $record], TRUE);
					$ajax_data['updateSectionData']  = [
						'box' 		=> $from_widget === 'n' ? '#_data-row-policy-' . $record->id : '#tab-policy-overview-inner',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
				}
				return $this->template->json($ajax_data);
			}
			else
			{
				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'form' 			=> $this->load->view('policies/_form', $form_data, TRUE)
				]);
			}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Policy
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->policy_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Permissions
		 *
		 * Deletable Status
		 * 		draft
		 *
		 * Deletable Permission
		 * 		delete.draft.policy
		 */

		// Deletable Status?
		if( $record->status !== IQB_POLICY_STATUS_DRAFT )
		{
			$this->dx_auth->deny_access();
		}

		// Deletable Permission ?
		$__flag_authorized 		= FALSE;
		if( $this->dx_auth->is_authorized('policies', 'delete.draft.policy') )
		{
			$__flag_authorized = TRUE;
		}

		if( !$__flag_authorized )
		{
			$this->dx_auth->deny_access();
		}


		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Policy_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->policy_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-policy-'.$record->id
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
		 * Get Policy Packages for Portfolio
		 *
		 * Get the policy packages for specified portfolio
		 *
		 * @param integer $portfolio_id
		 * @param string 	$method 	Return Method
		 * @return mixed
		 */
		public function gppp($portfolio_id)
		{
			// Valid Record ?
			$portfolio_id = (int)$portfolio_id;

			// Policy Package Options
			$ppo = _OBJ_policy_package_dropdown($portfolio_id, false);

			if( !empty($ppo))
			{
				$this->template->json([
					'status' => 'success',
					'ppo' => $ppo
				]);
			}
			$this->template->render_404('', 'Incorrect Portfolio');
		}

		// --------------------------------------------------------------------

		/**
		 * Get Creditor Company Branches for Supplied  Creditor Company
		 *
		 * @param integer $portfolio_id
		 * @param string 	$method 	Return Method
		 * @return mixed
		 */
		public function gccbc($company_id)
		{
			// Valid Record ?
			$company_id = (int)$company_id;
			$this->load->model('company_branch_model');

			$options = $this->company_branch_model->dropdown_by_company($company_id);
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
					'message' => 'No Branch Found. Please ask your IT Support to add "Company Branch" of selected "Creditor Company" and try again.'
				], 404);
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Reset Premium on Debit Note Update
		 *
		 * Reset premium on change of any of the followings
		 * 	- portfolio
		 * 	- policy package
		 * 	- customer
		 * 	- policy object
		 * 	- flag direct discount/agent commission
		 *
		 * @param object $before_update Policy Record Before Update
		 * @param array $data Post Data
		 * @return void
		 */
		private function __reset_premium_on_debitnote_update($before_update, $data)
		{
			// Process data to get fractioned date/time
			$after_update = (object)$this->policy_model->before_update__defaults($data);

			$fields = ['portfolio_id', 'policy_package', 'customer_id', 'object_id', 'flag_dc', 'start_date', 'end_date'];
			$__flag_reset = FALSE;
			foreach($fields as $column)
			{
				if($before_update->{$column} != $after_update->{$column})
				{
					$__flag_reset = TRUE;
					break;
				}
			}
			if( $__flag_reset === TRUE )
			{

				return $this->policy_txn_model->reset($before_update->id);
			}
			return TRUE;
		}

	    // --------------------------------------------------------------------

		/**
		 * Callback: Valid Company Branch
		 *
		 * Logic:
		 * 		If flag_on_credit is set to Yes, you should supply both creditor company
		 * 		and its branch.
		 *
		 * @param type $branch_id
		 * @return type
		 */
	    public function _cb_valid_company_branch($branch_id)
	    {
	    	$branch_id = (int)$branch_id;
	    	$creditor_id = (int)$this->input->post('creditor_id');
	    	$this->load->model('company_branch_model');

	    	if( !$this->company_branch_model->valid_branch($creditor_id, $branch_id) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_company_branch', 'The supplied "Branch" does not belong to selected "Creditor Company".');
	            return FALSE;
	    	}
	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Validate Backdate
	     *
	     * If user has supplied backdate, please make sure that :
	     * 		1. The user is allowed to enter Backdate
	     * 		2. If so, the supplied date should be withing backdate limit
	     *
	     * @param date $date
	     * @return bool
	     */
	    public function _cb_valid_backdate($date)
	    {
	    	$timestamp 		= strtotime($date);

	    	/**
	    	 * Not a past date?
	    	 * ---------------
	    	 * Simply return true;
	    	 */
	    	$dateonly_timestamp = strtotime(date('Y-m-d', $timestamp));
	    	$today_timestamp 	= strtotime(date('Y-m-d'));
	    	if($today_timestamp <=  $dateonly_timestamp )
	    	{
	    		return TRUE;
	    	}


	    	/**
	    	 * Backdate Allowed?
	    	 */
	    	if( !$this->dx_auth->is_backdate_allowed() )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', 'You are not authorized to enter "Back Dates"');
	            return FALSE;
	    	}

	    	/**
	    	 * Backdate Limit Set by Administrator?
	    	 */
	    	$back_date_limit = $this->settings->back_date_limit;
	    	if( !$back_date_limit || !valid_date($back_date_limit) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', '"Back Date Limit" is not setup properly.<br/>Please contact Administrator for further assistance.');
	            return FALSE;
	    	}

	    	/**
	    	 * Within Backdate Range?
	    	 * ----------------------
	    	 * i.e. Back Date <= Supplied Date
	    	 */
	    	$back_date_timestamp = strtotime($back_date_limit);

	    	if($timestamp < $back_date_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', 'Supplied date(%s) can not exceed "Back Date Limit"');
	            return FALSE;
	    	}
	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Valid Duration
	     *
	     * Case I:
	     * 		Proposed Date <= Issued Date
	     *
	     * Case II:
	     * 		Issued Date <= Start Date
	     *
	     * Case III:
	     * 		Start Date < End Date
	     *
	     * Case IV:
	     * 		(End Date - Start Date) should not exceed the Portfolio's Default Duration
	     *
	     * @param date $end_datetime
	     * @return bool
	     */
	    public function _cb_valid_policy_duration($end_datetime)
	    {
	    	$proposed_date 		= $this->input->post('proposed_date');
	    	$issued_datetime 	= $this->input->post('issued_datetime');
	    	$start_datetime 	= $this->input->post('start_datetime');
	    	$portfolio_id 		= (int)$this->input->post('portfolio_id');

	    	$proposed_timestamp = strtotime($proposed_date);
	    	$issued_timestamp 	= strtotime($issued_datetime);
	    	$start_timestamp    = strtotime($start_datetime);
	        $end_timestamp      = strtotime($end_datetime);

	    	/**
	    	 * Case I: Proposed Date <= Issued Date
	    	 */
	    	if( $proposed_timestamp > $issued_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Proposed Date" must not exceed "Issued Date & Time"');
	            return FALSE;
	    	}

	    	/**
	    	 * Case II: Issued Date <= Start Date
	    	 */
	    	if( $issued_timestamp > $start_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Issued Date & Time" must not exceed "Start Date & Time"');
	            return FALSE;
	    	}


	    	/**
	    	 * Case III: Start Date < End Date
	    	 */
	    	if( $start_timestamp >= $end_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Start Date & Time" must not exceed "End Date & Time"');
	            return FALSE;
	    	}

	    	/**
	    	 * Case IV: (End Date - Start Date) should not exceed the Portfolio's Default Duration
	    	 */
	    	if( !$portfolio_id )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', 'Please select portfolio first to compute "Policy Short Term Info"');
	            return FALSE;
	    	}

	    	$fy_record = $this->fiscal_year_model->get_fiscal_year( $issued_datetime );
	    	try{

				$info 	= _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_datetime, $end_datetime );

			} catch (Exception $e){

				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Exception: ' . $e->getMessage()
				], 404);
			}

	        $difference         = $end_timestamp - $start_timestamp;
	        $days               = floor($difference / (60 * 60 * 24));
	        $default_duration 	= $info['default_duration'];

	    	if( $days > $default_duration )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', "End date should not be higher than portfolio's default duration ({$default_duration} days)");
	            return FALSE;
	    	}
	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Valid Object Defaults
	     *
	     * Checks the Object Validity for the given Policy
	     * 		1. Object Owner and Selected Customer Should Match
	     * 		2. If the object is already assigned to other policy which is not
	     * 			Canceled|Expired, You can not assign this object to new policy.
	     *		3. Object Portfolio/Subportfolio should match Object portfolio/subportfolio
	     *
	     * @param string $str
	     * @return bool
	     */
	    public function _cb_valid_object_defaults($object_id)
	    {
	    	$object_id 		= (int)$object_id;
	    	$customer_id 	= (int)$this->input->post('customer_id');
	    	$portfolio_id 		= (int)$this->input->post('portfolio_id');

	    	/**
	    	 * Case 1 : Check Ownership
	    	 * ------------------------
	    	 */
	    	if( !$object_id OR !$customer_id)
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'Customer and/or Object not supplied.');
	            return FALSE;
	    	}

	    	$object_record = $this->object_model->find($object_id);
	    	if(!$object_record)
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'You are trying to manipulate CUSTOMER & OBJECT, which unfortunately, DOES NOT WORK!');
	            return FALSE;
	    	}

	    	if( $object_record->customer_id != $customer_id )
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected Object does not belong to the selected Customer.');
	            return FALSE;
	    	}

	    	/**
	    	 * Case 2: Object Editable?
	    	 * ------------------------
	    	 *
	    	 *  ! IMPORTANT !
	    	 *
	    	 * Is this object is free to assign to new/editable policy?
	    	 *
	    	 * Logic:
	    	 * 	If this policy object is already assigned to a policy which is NOT (CANCELED|EXPIRED)
	    	 * 	OR
	    	 *  If this policy object is already assigned to a policy which is editable
	    	 *
	    	 * For that, Simply get the latest policy record of this object and check
	    	 *
	    	 */
	    	$id = $this->input->post('id') ?? NULL;
	    	$id = $id ? (int)$id : NULL;
	    	$policy_record = $this->object_model->get_latest_policy($object_id);

	    	if( $policy_record )
	    	{
	    		// Add Mode
	    		if(!$id)
	    		{
	    			// If found Editable Policy Record, It is already assigned to another policy which is working with this object
	    			if( !in_array( $policy_record->status, [IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED] ) )
		    		{
		    			$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected object is already assigned to another active Policy.');
		            	return FALSE;
		    		}
	    		}

	    		// Edit Mode
	    		else
	    		{
	    			// If policy Do not Match, THe policy status must be Expired|Canceled
	    			if( $policy_record->id != $id )
		    		{
		    			$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected object is already assigned to another Policy.');
		            	return FALSE;
		    		}
	    		}
	    	}

	    	/**
	    	 * Case 3 : Portfolio/Sub-portfolio Match between Policy-Object
	    	 * ------------------------------------------------------------
	    	 */
	    	if( $object_record->portfolio_id != $portfolio_id )
    		{
    			$this->form_validation->set_message('_cb_valid_object_defaults', "The object's Portfolio/Sub-portfolio MUST match with Policy's Portfolio/Sub-Portfolio");
            	return FALSE;
    		}

	        return TRUE;
	    }


	// --------------------------------------------------------------------
	//  POLICY DETAILS
	// --------------------------------------------------------------------


    /**
     * View Policy Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'explore.policy') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		try {

			$txn_record = $this->policy_txn_model->get_fresh_renewal_by_policy( $record->id, $record->ancestor_id ? IQB_POLICY_TXN_TYPE_RENEWAL : IQB_POLICY_TXN_TYPE_FRESH );

		} catch (Exception $e) {

			return $this->template->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 404);
		}



		$data = [
			'record' 		=> $record,
			'txn_record' 	=> $txn_record
		];

		$this->data['site_title'] = 'Policy Details | ' . $record->code;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Policy -' . $record->code,
								'breadcrumbs' => ['Policies' => 'policies', 'Details' => NULL]
						])
						->partial('content', 'policies/_details', $data)
						->render($this->data);

    }

    // --------------------------------------------------------------------
	//  POLICY PRINT
	// --------------------------------------------------------------------

	/**
	 * Print Policy Schedule
	 *
	 * @param integer $id  Policy ID
	 * @return void
	 */
    public function schedule($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'generate.policy.schedule') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$schedule_view = '';
		switch ($record->portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
					$schedule_view = 'policies/print/schedule_MOTOR';
				break;

			default:
				# code...
				break;
		}


		if( $schedule_view )
		{
			$this->load->library('pdf');
	        $mpdf = $this->pdf->load();
	        $mpdf->SetMargins(10, 10, 5);
	        $mpdf->margin_header = 0;
	        $mpdf->margin_footer = 2;
	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Schedule - {$record->code}");
	        $mpdf->SetAuthor($this->settings->orgn_name_en);

	        if( in_array($record->status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED, IQB_POLICY_STATUS_VERIFIED]))
	        {
	        	$mpdf->SetWatermarkText( 'DEBIT NOTE - ' . $this->settings->orgn_name_en );
	        }


	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $this->load->view( $schedule_view, ['record' => $record], TRUE);
	        $mpdf->WriteHTML($html);

	        $mpdf->Output();
		}
		else
		{
			$this->template->render_404('', 'No Schedule View Found!');
		}
    }


    // --------------------------------------------------------------------
	//  POLICY STATUS UPGRADE/DOWNGRADE
	// --------------------------------------------------------------------

	/**
	 * Upgrade/Downgrade Status of a Policy
	 *
	 * @param integer $id Policy ID
	 * @param char $to_status_code Status Code
	 * @return json
	 */
	public function status($id, $to_status_code)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);

		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($to_status_code, $record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->policy_model->update_status($record, $to_status_code) )
			{

				/**
				 * @TODO: Post Status Update Tasks
				 * example send SMS on policy activation etc ...
				 */

				/**
				 * Update View
				 */
				$record->status = $to_status_code;
				$view = 'policies/tabs/_tab_overview';
				$html = $this->load->view($view, ['record' => $record], TRUE);

				$ajax_data = [
					'message' 	=> 'Successfully Updated!',
					'status'  	=> 'success',
					'reloadRow' => true,
					'rowId' 	=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'row'		=> $html
				];
				return $this->template->json($ajax_data);
			}

		} catch (Exception $e) {

			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			], 400);
		}

		return $this->template->json([
			'status' 	=> 'error',
			'message' 	=> 'Could not be updated!'
		], 400);
	}

	// --------------------------------------------------------------------

		/**
		 * Check Status up/down permission
		 *
		 * @param alpha $to_updown_status Status Code to UP/DOWN
		 * @param object $record Policy Record
		 * @return mixed
		 */
		private function __check_status_permission($to_updown_status, $record)
		{
			/**
			 * Check Permission
			 * ------------------------------
			 *
			 * You need to have permission to modify the given status.
			 * Plus, you need to have some pre-requisite before you
			 * upgrade an status
			 *
			 * Case 1: Send To Verify
			 * 		- Check if premium info is not NULL
			 *
			 * Case 2: Verify
			 * 		- Check if premium info is not NULL
			 */

			$status_keys = array_keys(get_policy_status_dropdown(FALSE));

			// Valid Status Code?
			if( !in_array($to_updown_status, $status_keys ) )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Invalid Status Code!'
				], 403);
			}

			// Valid Permission?
			$__flag_valid_permission = FALSE;
			$permission_name 	= '';
			switch ($to_updown_status)
			{
				case IQB_POLICY_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_POLICY_STATUS_UNVERIFIED:
					$permission_name = 'status.to.unverified';
					break;

				case IQB_POLICY_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_POLICY_STATUS_PAID:
					$permission_name = 'status.to.paid';
					break;

				case IQB_POLICY_STATUS_ACTIVE:
					$permission_name = 'status.to.active';
					break;

				case IQB_POLICY_STATUS_CANCELED:
					$permission_name = 'status.to.cancel';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('policies', $permission_name) )
			{
				$__flag_valid_permission = TRUE;
			}

			if( !$__flag_valid_permission )
			{
				$this->dx_auth->deny_access();
			}


			/**
			 * Check Pre-Requisite
			 * --------------------
			 * 	Condition 1: Upgrade from "Draft" to "Unverified"
			 * 	Condition 2: Upgrade from "Unverified" to "Verified"
			 * 	- Check if Premium is NULL
			 */
			if (
				( $record->status === IQB_POLICY_STATUS_DRAFT && $to_updown_status === IQB_POLICY_STATUS_UNVERIFIED )
					||
				( $record->status === IQB_POLICY_STATUS_UNVERIFIED && $to_updown_status === IQB_POLICY_STATUS_VERIFIED )
			)
			{
				if((float)$record->cur_amt_total_premium == 0.00 )
				{
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Please Update Policy Premium First!'
					], 400);
				}
			}

			return $__flag_valid_permission;
		}

	// --------------------------------------------------------------------

}