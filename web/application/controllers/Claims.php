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
	public function edit_draft($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('claims', 'edit.claim') )
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
		$json_data = $this->_save('edit_draft', $record->policy_id, $record);


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
	 * @return array
	 */
	private function _save($action, $policy_id, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add_draft', 'edit_draft')))
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
        		$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add_draft')
				{
					$data['policy_id'] = $policy_id;
					$done = $this->claim_model->add_draft($data);
				}
				else
				{
					// Now Update Data
					$data['policy_id'] = $record->policy_id;
					$done = $this->claim_model->edit_draft($record->id, $data);
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
				$single_row 	=  'claims/_single_row';
				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add_draft' ? '#search-result-claims' : '#_data-row-claims-' . $record->id,
					'method' 	=> $action === 'add_draft' ? 'prepend' : 'replaceWith',
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
		if( $this->dx_auth->is_authorized('claims', 'delete.claim') )
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
		// belongs_to_me( $record->branch_id );

		/**
		 * RI Transaction Detail Rows
		 */
		$data = [
			'record' 	=> $record
		];

		$this->template->json([
			'html' 	=> $this->load->view('claims/_details', $data, TRUE),
			'title' => 'RI Transaction Details - ' .  $record->id
		]);
    }

}















