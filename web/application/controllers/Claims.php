<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Claims Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Claims extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Claims';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'claims',
			'level_1' => 'claims'
		]);

		// Load Model
		$this->load->model('claim_model');
		$this->load->model('claim_surveyor_model');
		$this->load->model('policy_model');

		// Helper
		$this->load->helper('claim');

	}

	// --------------------------------------------------------------------
	// SEARCH/LIST WIDGET FUNCTIONS
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
	public function page( $layout='f', $next_id = 0,  $do_filter = TRUE )
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('claims', 'explore.claim', TRUE);

		// dom data
		$dom_data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-claims', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-claims', 		// Filter Form ID
		];

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

		$records 	= $this->claim_model->rows($params);
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

		$data = [
			'policy_id' => NULL,
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'claims/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'claims/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('claims/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'claims/_list';
		}
		else
		{
			$view = 'claims/_rows';
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
						'claims/_index_header',
						['content_header' => 'Manage Claims'] + $data)
					->partial('content', 'claims/_index', $data)
					->render($this->data);
	}


		private function _get_filter_elements()
		{
			$filters = [
				[
					'field' => 'filter_policy_id',
			        'label' => 'Policy ID',
			        'rules' => 'trim|integer|max_length[20]',
	                '_type'     => 'text',
	                '_required' => false
				],
	            [
					'field' => 'filter_policy_code',
			        'label' => 'Policy Code',
			        'rules' => 'trim|max_length[40]',
	                '_type'     => 'text',
	                '_required' => false
				]
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
						'policy_id' 	=> $this->input->post('filter_policy_id') ?? NULL,
						'policy_code' 	=> $this->input->post('filter_policy_code') ?? NULL,
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

	/**
	 * Get all Invoice for Supplied Policy
	 *
	 * @param int $policy_id
	 * @param bool $data_only Return Data Only
	 * @return JSON
	 */
	function by_policy($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('claims', 'explore.claim', TRUE);

		$policy_id 	= (int)$policy_id;
		$records = $this->claim_model->rows_by_policy($policy_id);
		$data = [
			'add_url' 					=> 'claims/add/' . $policy_id,
			'records' 					=> $records,
			'policy_id' 				=> $policy_id,
			'next_id' 					=> NULL
		];

		$html = $this->load->view('claims/_policy/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		// Return if Ajax Data Only is Set
		if($data_only) return $ajax_data;

		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Flush Cache - Per Policy
	 *
	 * @param type $policy_id
	 * @return type
	 */
	public function flush_by_policy($policy_id)
	{
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('claims', 'explore.claim', TRUE);

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'claim_list_by_policy_' . $policy_id : NULL;

		$this->claim_model->clear_cache($cache_var);

		$ajax_data = $this->by_policy($policy_id, TRUE);
		$json_data = [
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.',
			'reloadRow' => true,
			'rowId' 	=> '#list-widget-policy-claims',
			'row' 		=> $ajax_data['html']
		];

		return $this->template->json($json_data);
	}

	// --------------------------------------------------------------------
	// CRUD FUNCTIONS
	// --------------------------------------------------------------------


	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add($policy_id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'add.claim') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Policy Eligible?
		 */
		$policy_id = (int)$policy_id;
		if( !$this->_is_policy_eligible($policy_id) )
		{
			return $this->template->json(['status' => 'error', 'title' => 'Invalid Action', 'message' => 'You can not add new claim on a non-active policy.'], 403);
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('add_draft', $policy_id);


		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_draft',
			[
				'form_elements' 		=> $this->_v_rules('add_draft'),
				'record' 				=> NULL,
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
	public function edit_draft($id, $ref)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'edit.claim.draft') )
		{
			$this->dx_auth->deny_access();
		}

		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}


		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Is Claim Editable?
		 */
		CLAIM__is_editable($record->status);



		// Form Submitted? Save the data
		$json_data = $this->_save('edit_draft', $record->policy_id, $record, $ref);


		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_draft',
			[
				'form_elements' 		=> $this->_v_rules('add_draft'),
				'record' 				=> $record,
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

		/**
		 * Is policy eligible?
		 *
		 * Policy must be in an active state.
		 *
		 * @param integer $policy_id
		 * @return bool
		 */
		private function _is_policy_eligible($policy_id)
		{
			$policy_status = $this->policy_model->get_status($policy_id);

			return $policy_status === IQB_POLICY_TXN_STATUS_ACTIVE;
		}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param int $policy_id Policy ID
	 * @param object|null $record Record Object or NULL
	 * @param char $ref Request reference (l|d) i.e. from list or detail page
	 * @return array
	 */
	private function _save($action, $policy_id, $record = NULL, $ref = 'l')
	{
		// Valid action?
		if( !in_array($action, array('add_draft', 'edit_draft', 'close_claim', 'withdraw_claim', 'assign_surveyors', 'update_assessment', 'update_settlement', 'update_scheme')))
		{
			$this->template->json([
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

			$rules = $this->_v_rules($action, TRUE);

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data 				= $this->input->post();
        		$done 				= false;

        		switch ($action)
        		{
        			case 'add_draft':
        				$data['policy_id'] 	= $policy_id;
						$done = $this->claim_model->add_draft($data);
        				break;

    				case 'edit_draft':
    					$data['policy_id'] 	= $policy_id;
						$done = $this->claim_model->edit_draft($record->id, $data);
        				break;

    				case 'close_claim':
    					$update_data = [
							'status' 		 => IQB_CLAIM_STATUS_CLOSED,
							'status_remarks' => $data['status_remarks']
    					];
    					$done = $this->claim_model->update_data($record->id, $update_data, $policy_id);
        				break;

    				case 'withdraw_claim':
    					$update_data = [
							'status' 		 => IQB_CLAIM_STATUS_WITHDRAWN,
							'status_remarks' => $data['status_remarks']
    					];
						$done = $this->claim_model->update_data($record->id, $update_data, $policy_id);
        				break;

    				case 'assign_surveyors':
    					$done = $this->claim_surveyor_model->assign_to_claim($record->id, $data);
    					break;

					case 'update_assessment':
						$update_data = [
							'assessment_brief' 	=> $data['assessment_brief'],
							'other_info' 		=> $data['other_info'],
							'supporting_docs' 	=> implode(',', $data['supporting_docs'])
    					];
    					$done = $this->claim_model->update_data($record->id, $update_data, $policy_id);
						break;

					case 'update_settlement':
						$done = $this->claim_model->update_settlement($record->id, $data, $policy_id);
						break;

					case 'update_scheme':
						$update_data = [
							'claim_scheme_id' 	=> $data['claim_scheme_id']
    					];
    					$done = $this->claim_model->update_data($record->id, $update_data, $policy_id);
						break;

        			default:
        				# code...
        				break;
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
        		$message = validation_errors();

        	}

        	if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 		= $this->claim_model->row($action === 'add_draft' ? $done : $record->id);

				$view_data 	= ['record' => $record];
				$partial_view =  'claims/_single_row';

				// If Reference is from Details Page
				if($ref == 'd')
				{
					$partial_view 	=  'claims/_details';
					$view_data = array_merge( $view_data,
									[
									'surveyors' 		=> $this->claim_surveyor_model->get_many_by_claim($record->id),
									'draft_elements' 	=> $this->claim_model->draft_v_rules()
									]);
				}

				// DOM Box
				$method = 'replaceWith';
				if($action === 'add_draft')
				{
					$box = '#search-result-claims';
					$method = 'prepend';
				}
				else if($ref == 'l')
				{
					$box = '#_data-row-claims-' . $record->id;
				}
				else
				{
					$box = '#claim-details';
				}

				// Get the html
				$html = $this->load->view($partial_view, $view_data, TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $box,
					'method' 	=> $method,
					'html'		=> $html
				];

				return $this->template->json($ajax_data);
			}
			else
			{
				return $this->template->json([
					'status' => $status,
					'message' => $message
				]);
			}
		}
		return $return_data;
	}


		private function _v_rules($action, $formatted=FALSE)
		{
			$rules = [];
			switch($action)
			{
				case 'add_draft':
				case 'edit_draft':
					$rules = $this->claim_model->draft_v_rules($formatted);
					break;

				case 'close_claim':
					$rules = $this->claim_model->close_v_rules($formatted);
					break;

				case 'withdraw_claim':
					$rules = $this->claim_model->withdraw_v_rules($formatted);
					break;

				case 'assign_surveyors':
					$rules = $this->claim_surveyor_model->validation_rules;
					break;

				case 'update_assessment':
					$rules = $this->claim_model->assessment_v_rules($formatted);
					break;

				case 'update_settlement':
					$rules = $this->claim_model->settlement_v_rules($formatted);
					break;

				case 'update_scheme':
					$rules = $this->claim_model->scheme_v_rules($formatted);
					break;

				default:
					break;
			}
			return $rules;
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
		$record = $this->claim_model->get($id);
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
		if( $record->status !== IQB_CLAIM_STATUS_DRAFT )
		{
			$this->dx_auth->deny_access();
		}

		// Deletable Permission ?
		$__flag_authorized 		= FALSE;
		if( $this->dx_auth->is_authorized('claims', 'delete.claim.draft') )
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
		if( !safe_to_delete( 'Claim_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->claim_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-claims-'.$record->id
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
	//  STATUS UP/DOWN METHODS
	// --------------------------------------------------------------------

	/**
	 * Revert claim status to Draft
	 *
	 * @param int $id
	 * @return json
	 */
	public function to_draft($id, $ref)
	{
		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}


		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($record->status);



		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($record->status, IQB_CLAIM_STATUS_DRAFT );


		/**
		 * Let's Update the Status
		 */
		$data = [
			'status' => IQB_CLAIM_STATUS_DRAFT
		];
		$done = $this->claim_model->update_data($id, $data, $record->policy_id);


		if( $done )
		{
			$record 		= $this->claim_model->get($record->id);
			$view_data 		= ['record' => $record];
			$partial_view 	=  'claims/_single_row';

			// If Reference is from Details Page
			if($ref == 'd')
			{
				$partial_view 	=  'claims/_details';
				$view_data = array_merge( $view_data,
								[
								'surveyors' 		=> $this->claim_surveyor_model->get_many_by_claim($record->id),
								'draft_elements' 	=> $this->claim_model->draft_v_rules()
								]);
			}

			// DOM Box
			$method = 'replaceWith';
			if($ref == 'l')
			{
				$box = '#_data-row-claims-' . $record->id;
			}
			else
			{
				$box = '#claim-details';
			}

			// Get the html
			$html = $this->load->view($partial_view, $view_data, TRUE);

			$ajax_data = [
				'message' 	=> 'Successfully Updated!',
				'status'  	=> 'success',
				'multipleUpdate' => [
					[
						'box' 		=> $box,
						'method' 	=> $method,
						'html'		=> $html
					]
				]
			];
			return $this->template->json($ajax_data);
		}
		else
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Could not update.'
			]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Verify Claim
	 *
	 * @param int $id
	 * @return json
	 */
	public function verify($id, $ref)
	{
		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}


		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($record->status);



		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($record->status, IQB_CLAIM_STATUS_VERIFIED );


		/**
		 * Let's Update the Status
		 */
		$done = $this->claim_model->verify($record);

		if( $done )
		{
			$record 		= $this->claim_model->get($record->id);
			$view_data 		= ['record' => $record];
			$partial_view 	=  'claims/_single_row';

			// If Reference is from Details Page
			if($ref == 'd')
			{
				$partial_view 	=  'claims/_details';
				$view_data = array_merge( $view_data,
								[
								'surveyors' 		=> $this->claim_surveyor_model->get_many_by_claim($record->id),
								'draft_elements' 	=> $this->claim_model->draft_v_rules()
								]);
			}

			// DOM Box
			$method = 'replaceWith';
			if($ref == 'l')
			{
				$box = '#_data-row-claims-' . $record->id;
			}
			else
			{
				$box = '#claim-details';
			}

			// Get the html
			$html = $this->load->view($partial_view, $view_data, TRUE);

			$ajax_data = [
				'message' 	=> 'Successfully Updated!',
				'status'  	=> 'success',
				'multipleUpdate' => [
					[
						'box' 		=> $box,
						'method' 	=> $method,
						'html'		=> $html
					]
				]
			];
			return $this->template->json($ajax_data);
		}
		else
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Could not update.'
			]);
		}
	}

	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Close a Claim
	 *
	 * @param int $id
	 * @return json
	 */
	public function close($id, $ref)
	{
		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}


		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($record->status);



		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($record->status, IQB_CLAIM_STATUS_CLOSED );



		// Form Submitted? Save the data
		$json_data = $this->_save('close_claim', $record->policy_id, $record, $ref);


		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_close_withdraw',
			[
				'form_elements' 		=> $this->_v_rules('close_claim'),
				'record' 				=> $record,
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------


	/**
	 * Withdraw a Claim
	 *
	 * @param int $id
	 * @return json
	 */
	public function withdraw($id, $ref)
	{
		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}


		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($record->status);



		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($record->status, IQB_CLAIM_STATUS_WITHDRAWN );



		// Form Submitted? Save the data
		$json_data = $this->_save('withdraw_claim', $record->policy_id, $record, $ref);


		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_close_withdraw',
			[
				'form_elements' 		=> $this->_v_rules('withdraw_claim'),
				'record' 				=> $record,
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}


		// --------------------------------------------------------------------

		/**
		 * Check Status up/down permission
		 *
		 * @param alpha $status Status Code to UP/DOWN
		 * @return mixed
		 */
		private function __check_status_permission($status)
		{
			$status_keys = array_keys(CLAIM__status_dropdown(FALSE));

			// Valid Status Code?
			if( !in_array($status, $status_keys ) )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Invalid Status Code!'
				], 403);
			}

			// Valid Permission?
			$__flag_valid_permission = FALSE;
			$permission_name 	= '';
			switch ($status)
			{
				case IQB_CLAIM_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_CLAIM_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_CLAIM_STATUS_APPROVED:
					$permission_name = 'status.to.approved';
					break;

				case IQB_CLAIM_STATUS_SETTLED:
					$permission_name = 'status.to.settled';
					break;

				case IQB_CLAIM_STATUS_WITHDRAWN:
					$permission_name = 'status.to.withdrawn';
					break;

				case IQB_CLAIM_STATUS_CLOSED:
					$permission_name = 'status.to.closed';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('claims', $permission_name) )
			{
				$__flag_valid_permission = TRUE;
			}

			if( !$__flag_valid_permission )
			{
				$this->dx_auth->deny_access();
			}

			return $__flag_valid_permission;
		}

		// --------------------------------------------------------------------

		/**
		 * Status Qualifies to UP/DOWN
		 *
		 * @param char $current_status Current Status of Claim
		 * @param char $to_status Status to UP/Down
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		public function __status_qualifies($current_status, $to_status, $terminate_on_fail = TRUE)
	    {
	        $flag_qualifies = FALSE;

	        switch ($to_status)
	        {
	            case IQB_CLAIM_STATUS_DRAFT:
	                $flag_qualifies = $current_status === IQB_CLAIM_STATUS_VERIFIED;
	                break;

	            case IQB_CLAIM_STATUS_VERIFIED:
	                $flag_qualifies = $current_status === IQB_CLAIM_STATUS_DRAFT;
	                break;

	            case IQB_CLAIM_STATUS_APPROVED:
	                $flag_qualifies = $current_status === IQB_CLAIM_STATUS_VERIFIED;
	                break;

                case IQB_CLAIM_STATUS_SETTLED:
	                $flag_qualifies = $current_status === IQB_CLAIM_STATUS_APPROVED;
	                break;

                case IQB_CLAIM_STATUS_WITHDRAWN:
                case IQB_CLAIM_STATUS_CLOSED:
	                $flag_qualifies = $current_status === IQB_CLAIM_STATUS_VERIFIED;
	                break;

	            default:
	                break;
	        }

	        if( !$flag_qualifies && $terminate_on_fail )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Invalid Status Transaction',
					'message' 	=> 'You can not swith to the state from this state of transaction.'
				], 400);
			}


	        return $flag_qualifies;
	    }


	// --------------------------------------------------------------------


	/**
	 * Assignn Surveyors on a Claim
	 *
	 * @param int $id
	 * @return json
	 */
	public function surveyors($id, $ref)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'assign.claim.surveyors') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}

		/**
		 * Status Qualifies
		 */
		if($record->status !== IQB_CLAIM_STATUS_VERIFIED)
		{
			$this->dx_auth->deny_access();
		}


		/**
		 * Get surveyors assigned to this claim
		 */
		$surveyors = $this->claim_surveyor_model->get_many_by_claim($record->id);



		// Form Submitted? Save the data
		$json_data = $this->_save('assign_surveyors', $record->policy_id, $record, $ref);



		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_surveyors',
			[
				'form_elements' => $this->claim_surveyor_model->validation_rules,
				'record' 		=> $record,
				'surveyors' 	=> $surveyors
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

		public function cb_surveyor_duplicate($str)
		{
			$surveyor_id = $this->input->post('surveyor_id');
			$survey_type = $this->input->post('survey_type');

			$total_count = count($surveyor_id);
			$complex_list = [];
			for($i = 0; $i< $total_count; $i++ )
			{
				$complex_list[] = implode('-', [$surveyor_id[$i], $survey_type[$i]]);
			}


			// Check duplicate Entries
			$unique_count = count( array_unique($complex_list) );
			if( $unique_count !== $total_count )
			{
				$this->form_validation->set_message('cb_surveyor_duplicate', 'Surveyor can not be Duplicate/Empty.');
	            return FALSE;
			}

			return TRUE;
		}

	// --------------------------------------------------------------------

	/**
	 * Update Survey assessment
	 *
	 * @param int $id
	 * @return json
	 */
	public function assessment($id, $ref)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'update.claim.assessment') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}

		/**
		 * Status Qualifies
		 */
		if($record->status !== IQB_CLAIM_STATUS_VERIFIED)
		{
			$this->dx_auth->deny_access();
		}


		// Form Submitted? Save the data
		$json_data = $this->_save('update_assessment', $record->policy_id, $record, $ref);

		// Supporting Documents (On Edit mode)
		$form_elements 	= $this->_v_rules('update_assessment');
		$supporting_docs = explode(',', $record->supporting_docs);
		$form_elements[2]['_checkbox_value'] = $supporting_docs;

		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_assessment',
			[
				'form_elements' => $form_elements,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Update Survey settlement
	 *
	 * @param int $id
	 * @return json
	 */
	public function settlement($id, $ref)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'update.claim.settlement') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}

		/**
		 * Status Qualifies
		 */
		if($record->status !== IQB_CLAIM_STATUS_VERIFIED)
		{
			$this->dx_auth->deny_access();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('update_settlement', $record->policy_id, $record, $ref);

		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_settlement',
			[
				'form_elements' => $this->_v_rules('update_settlement'),
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Update Survey scheme
	 *
	 * @param int $id
	 * @return json
	 */
	public function scheme($id, $ref)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'update.claim.scheme') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get Record
		 */
		$id 	= (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Claim not found!'
			],404);
		}

		/**
		 * Status Qualifies
		 */
		if($record->status !== IQB_CLAIM_STATUS_VERIFIED)
		{
			$this->dx_auth->deny_access();
		}


		// Form Submitted? Save the data
		$json_data = $this->_save('update_scheme', $record->policy_id, $record, $ref);

		// No form Submitted?
		$json_data['form'] = $this->load->view('claims/forms/_form_scheme',
			[
				'form_elements' => $this->_v_rules('update_scheme'),
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}


	// --------------------------------------------------------------------
	//  DETAILS
	// --------------------------------------------------------------------


    /**
     * View Invoice Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('claims', 'explore.claim', TRUE);

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->claim_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );


		/**
		 * Get surveyors assigned to this claim
		 */
		$surveyors = $this->claim_surveyor_model->get_many_by_claim($record->id);

		/**
		 * RI Transaction Detail Rows
		 */
		$data = [
			'record' 		=> $record,
			'surveyors' 	=> $surveyors,
			'draft_elements' => $this->claim_model->draft_v_rules(),
		];

		$page_header = 'Claim Details - <span id="page-title-claim-code">' . $record->claim_code . '</span>';

		$this->data['site_title'] = 'Claim Details | ' . $record->claim_code;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => $page_header,
								'breadcrumbs' => ['Claims' => 'claims', 'Details' => NULL]
						])
						->partial('content', 'claims/_details', $data)
						->render($this->data);
    }

}














