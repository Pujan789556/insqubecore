<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endorsement Controller
 *
 * We use this controller to work with the policy premium.
 *
 * This controller falls under "Policy" category.
 *
 * @category 	Policy
 */

// --------------------------------------------------------------------

class Endorsements extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Endorsement';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('endorsement_model');
		$this->load->model('portfolio_setting_model');
		$this->load->model('object_model');

		// Policy Configuration/Helper
		$this->load->config('policy');
		$this->load->helper('policy');
		$this->load->helper('object');
	}

	// --------------------------------------------------------------------
	// SEARCH OPERATIONS
	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * List all Endorsement Records for supplied Policy
	 *
	 * @return JSON
	 */
	public function index($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('endorsements', 'explore.endorsement') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id 		= (int)$policy_id;
		$policy_record 	= $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		$records 	= $this->endorsement_model->rows($policy_id);

		$data = [
			'records' 					=> $records,
			'policy_record' 			=> $policy_record,
			'add_url' 					=> 'endorsements/add/' . $policy_id,
			'print_url' 				=> 'endorsements/print/all/' . $policy_id,
		];
		$html = $this->load->view('endorsements/_list_widget', $data, TRUE);
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
	public function flush($policy_id=NULL)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('endorsements', 'explore.endorsement') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'endrsmnt_' . $policy_id : NULL;
		$this->endorsement_model->clear_cache($cache_var);

		if($policy_id)
		{
			$ajax_data = $this->index($policy_id, TRUE);
			$json_data = [
				'status' => 'success',
				'message' 	=> 'Successfully flushed the cache.',
				'reloadRow' => true,
				'rowId' 	=> '#list-widget-endorsements',
				'row' 		=> $ajax_data['html']
			];

			return $this->template->json($json_data);
		}

		// Reload Index
		return $this->template->json([
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.'
		]);
	}

	// --------------------------------------------------------------------
	// MANAGE ENDORSEMENT
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
		$record = $this->endorsement_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Permissions & Editability
		 */
		_ENDORSEMENT_is_editable($record->status, $record->flag_current);


		/**
		 * Only Endorsement are editable
		 */
		if( $this->endorsement_model->is_first($record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Operation Not Permitted!',
				'message' 	=> 'You can not edit fresh/renewal record.'
			], 400);
		}


		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($record->policy_id);

		// Form Submitted? Save the data
		$txn_type = $record->txn_type;
		$this->_save($txn_type, $policy_record, 'edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('endorsements/forms/_form_endorsement',
			[
				'form_elements' => $this->endorsement_model->get_validation_rules($txn_type, $policy_record->portfolio_id, $policy_record),
				'record' 		=> $record,
				'policy_record' => $policy_record,
				'txn_type' 		=> $record->txn_type
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
	/**
	 * Add new endorsement
	 *
	 * @param int $policy_id
	 * @param int $txn_type
	 * @return mixed
	 */
	public function add($policy_id, $txn_type)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('endorsements', 'add.endorsement') )
		{
			$this->dx_auth->deny_access();
		}


		/**
		 * Valid Transaction Type?
		 */
		$txn_type = (int)$txn_type;
		if( !$this->endorsement_model->type_dropdown( FALSE, FALSE ) )
		{
			return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'Invalid Endorsement Type.'], 403);
		}

		/**
		 * Can I Add
		 */
		$policy_id = (int)$policy_id;
		if( !$this->_can_add_endorsement($policy_id) )
		{
			return $this->template->json([
				'status' => 'error',
				'title' => 'Action Not Permitted.',
				'message' => 'Either Policy or the last Endorsement is not complete (issued or active) OR Policy is Canceled/Expired.'], 403);
		}

		/**
		 * Policy Record? Active?
		 */
		$policy_record = $this->policy_model->get($policy_id);

		/**
		 * Policy Schedule Generated?
		 *
		 * !!! NOTE !!!
		 * MUST generate and save policy schedule PDF before adding any endorsement!!!
		 */
		if(!_POLICY__schedule_exists($policy_record->code))
		{
			return $this->template->json([
				'status' => 'error',
				'title' => 'Action Not Permitted.',
				'message' => 'Policy Schedule is not generated Yet. <br/>Please Click on <span class="btn btn-xs bg-navy btn-round"><i class="fa fa-print"></i> Schedule</span> Button once to generate and save Policy Schedule.'], 403);
		}

		$record 	= NULL;
		$this->_save($txn_type, $policy_record, 'add', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('endorsements/forms/_form_endorsement',
			[
				'form_elements' => $this->endorsement_model->get_validation_rules($txn_type, $policy_record->portfolio_id, $policy_record),
				'record' 		=> $record,
				'policy_record' => $policy_record,
				'txn_type' 		=> $txn_type
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Save Endorsement
		 *
		 * @param int $txn_type
		 * @param object $policy_record
		 * @param string $action
		 * @param object|null $record
		 * @return mixed
		 */
		private function _save($txn_type, $policy_record, $action, $record = NULL)
		{
			$post_data = $this->input->post();
			if( $this->input->post() )
			{
				/**
				 * Valid Policy ID?
				 * Coz we need it in ballback
				 */
				$policy_id = $post_data['policy_id'];
				if($policy_id != $policy_record->id)
				{
					return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'Invalid policy information.'], 403);
				}

				$rules = $this->endorsement_model->get_validation_rules($txn_type, $policy_record->portfolio_id, $policy_record, TRUE);
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() === TRUE )
	        	{
	        		$data = $this->_build_draft_data($rules, $post_data, $policy_record, $record);
	        		try {

						if($action == 'add')
		        		{
		        			$add_only_data 	= $this->_prepare_add_only_data($policy_record->id, $txn_type);
		        			$data 		 	= array_merge($add_only_data, $data);
		        			$done 			= $this->endorsement_model->add($data, $policy_record);
		        		}
		        		else
		        		{
		        			$data['txn_type'] = $record->txn_type; // Required to perform Befor save data function __refactor_dates()
		        			$done = $this->endorsement_model->edit($record->id, $data, $policy_record);
		        		}

					} catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
					}


	        		return $this->_return_on_save($action, $done, $policy_record->id, $record->id ?? NULL);
	        	}
	        	else
	        	{
	        		return $this->template->json(['status' => 'error', 'message' => validation_errors()]);
	        	}
			}
		}

		/**
		 * Save Endorsement
		 *
		 * @param int $txn_type
		 * @param object $policy_record
		 * @param string $action
		 * @param object|null $record
		 * @return mixed
		 */
		private function _save_OLD($txn_type, $policy_record, $action, $record = NULL)
		{
			$post_data = $this->input->post();
			if( $this->input->post() )
			{
				/**
				 * Valid Policy ID?
				 * Coz we need it in ballback
				 */
				$policy_id = $post_data['policy_id'];
				if($policy_id != $policy_record->id)
				{
					return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'Invalid policy information.'], 403);
				}

				$rules = $this->endorsement_model->get_validation_rules($txn_type, $policy_record->portfolio_id, $policy_record, TRUE);
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() === TRUE )
	        	{

	        		$data = $this->_prepare_data($txn_type, $post_data, $policy_record, $record);
	        		try {

						if($action == 'add')
		        		{
		        			$add_only_data 	= $this->_prepare_add_only_data($policy_record->id, $txn_type);
		        			$data 		 	= array_merge($add_only_data, $data);
		        			$done 			= $this->endorsement_model->add($data, $policy_record);
		        		}
		        		else
		        		{
		        			$data['txn_type'] = $record->txn_type; // Required to perform Befor save data function _assign_end_date()
		        			$done = $this->endorsement_model->edit($record->id, $data, $policy_record);
		        		}

					} catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
					}


	        		return $this->_return_on_save($action, $done, $policy_record->id, $record->id ?? NULL);
	        	}
	        	else
	        	{
	        		return $this->template->json(['status' => 'error', 'message' => validation_errors()]);
	        	}
			}
		}

	// --------------------------------------------------------------------

		private function _build_draft_data ($rules, $post_data, $policy_record, $record = NULL)
		{
			$data = [];
			foreach($rules as $single)
			{
				$field = $single['field'];
				$data[$field] = $post_data[$field] ?? NULL;
			}

			/**
			 * Nullify Agent if Blank
			 */
			if(!$data['agent_id'])
			{
				$data['agent_id'] = NULL;
			}


			/**
			 * Customer ID
			 *
			 * Same as Policy Customer ID (ownership transfer customer ID has a separate column)
			 */
			$data['customer_id'] = $policy_record->customer_id;


			/**
			 * SUM Insured - Object, NET
			 */



			return $data;
		}

		private function _prepare_add_only_data($policy_id, $txn_type)
		{
			return [
				'policy_id' 		=> $policy_id,
    			'txn_type'  		=> $txn_type,
    			'flag_ri_approval' 	=> $this->endorsement_model->get_flag_ri_approval_by_policy( $policy_id )
			];
		}

	// --------------------------------------------------------------------

		private function _return_on_save($action, $done, $policy_id, $id = NULL)
		{
			if(!$done)
			{
				return $this->template->json(['status' => 'error', 'message' => 'Could not update.']);
			}
			else
			{
				$status = 'success';
				$message = 'Successfully Updated.';

				if($action === 'add')
				{
					// Refresh the list page and close bootbox
					$return = $this->index($policy_id, TRUE);
					return $this->template->json([
						'status' 		=> $status,
						'message' 		=> $message,
						'reloadForm' 	=> false,
						'hideBootbox' 	=> true,
						'updateSection' => true,
						'updateSectionData' => [
							'box' 		=> '#tab-endorsements',
							'method' 	=> 'html',
							'html'		=> $return['html']
						]
					]);
				}
				else
				{
					// Get Updated Record
					$record 		= $this->endorsement_model->get($id);
					$policy_record 	= $this->policy_model->get($policy_id);
					$html 			= $this->load->view('endorsements/_single_row', ['record' => $record, 'policy_record' => $policy_record], TRUE);

					return $this->template->json([
						'status' 		=> $status,
						'message' 		=> $message,
						'reloadForm' 	=> false,
						'hideBootbox' 	=> true,
						'updateSection' => true,
						'updateSectionData' => [
							'box' 		=> '#_data-row-endorsements-' . $record->id,
							'method' 	=> 'replaceWith',
							'html'		=> $html
						]
					]);
				}
			}
		}

	// --------------------------------------------------------------------

		public function cb_valid_transfer_customer( $cutomer_id )
		{
			$policy_id = (int)$this->input->post('policy_id');

			$cutomer_id = (int)$cutomer_id;
			$current_customer_id = (int)$this->policy_model->get_customer_id($policy_id);

			/**
	    	 * Case I: Proposed Date <= Issued Date
	    	 */
	    	if( $cutomer_id === $current_customer_id )
	    	{
	    		$this->form_validation->set_message('cb_valid_transfer_customer', 'You can not transfer policy to same customer. Please select another customer.');
	            return FALSE;
	    	}

	    	return TRUE;
		}

	// --------------------------------------------------------------------

		public function _cb_valid_issued_date($issued_date)
		{
			$policy_id 		= (int)$this->input->post('policy_id');
			$start_date 	= $this->input->post('start_date') ?? NULL;
			$policy_record 	= $this->policy_model->find($policy_id);

			/**
	    	 * Case I:  Start Date < Issued Date
	    	 */
	    	if( $start_date !== NULL && strtotime($start_date) < strtotime($issued_date) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_issued_date', 'Issued Date can not exceed Start Date.');
	            return FALSE;
	    	}

	    	/**
	    	 * Case II: Issued Date < Policy Start Date
	    	 */
	    	if( strtotime($issued_date) < strtotime($policy_record->start_date) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_issued_date', 'Issued Date can not be earlier than Policy Start Date.');
	            return FALSE;
	    	}

	    	return TRUE;
		}

		public function _cb_valid_start_date($start_date)
		{
			$policy_id = (int)$this->input->post('policy_id');

			$issued_date 	= $this->input->post('issued_date');
			$end_date 		= $this->input->post('end_date') ?? NULL;
			$policy_record 	= $this->policy_model->find($policy_id);

			/**
	    	 * Case I: Start Date > END Date
	    	 */
	    	if( $end_date !== NULL && strtotime($start_date) > strtotime($end_date) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_start_date', 'Start Date can not exceed End Date.');
	            return FALSE;
	    	}

	    	/**
	    	 * Case II: Start Date > Policy END Date
	    	 */
	    	if( strtotime($start_date) > strtotime($policy_record->end_date) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_start_date', 'Endorsement Start Date can not exceed Policy End Date.');
	            return FALSE;
	    	}

	    	return TRUE;
		}

		public function _cb_valid_end_date($end_date)
		{
			$policy_id = (int)$this->input->post('policy_id');
			$start_date 	= $this->input->post('start_date');
			$policy_record 	= $this->policy_model->find($policy_id);

			/**
	    	 * Case I: END Date <= Policy END Date
	    	 */
	    	if( strtotime($end_date) <= strtotime($policy_record->end_date) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_end_date', 'Endorsement End Date must be greater than Policy End Date.');
	            return FALSE;
	    	}
	    	return TRUE;
		}
	// --------------------------------------------------------------------

		/**
		 * Can I add Endorsement
		 *
		 * Check whether I can add an endorsement for supplied policy.
		 * This is to validate if we already have an non-active endorsement.
		 *
		 * @param integer $policy_id
		 * @return bool
		 */
		private function _can_add_endorsement($policy_id)
		{
			$current_txn = $this->endorsement_model->get_current_endorsement($policy_id);

			/**
			 * Valid Status
			 */
			return $current_txn->status === IQB_ENDORSEMENT_STATUS_ACTIVE && $current_txn->policy_status === IQB_POLICY_STATUS_ACTIVE;
		}

	// --------------------------------------------------------------------

	public function premium_summary($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('endorsements', 'explore.endorsement') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->endorsement_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($record->policy_id);


		$data = [
			'endorsement_record' 	=> $record,
			'policy_record' 		=> $policy_record
		];

		$this->template->json([
			'html' 	=> $this->load->view('endorsements/snippets/_premium_summary', $data, TRUE),
			'title' => 'Endorsement Premium Distribution'
		]);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Endorsement Draft (Non Fresh/Renewal)
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		$id = (int)$id;
		$record = $this->endorsement_model->get($id);
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
		 * Deletable Type
		 * 		Endorsement General/Transactional
		 *
		 * Deletable Permission
		 * 		delete.draft.endorsement
		 */

		// Deletable Status?
		if(
			! _ENDORSEMENT_is_deletable($record->txn_type, $record->status)
							||
			!$this->dx_auth->is_authorized('endorsements', 'delete.draft.endorsement')
		)
		{
			$this->dx_auth->deny_access();
		}


		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];

		$done = $this->endorsement_model->delete($record);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-endorsements-'.$record->id
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
	// SAVE PREMIUM FUNCTIONS
	// --------------------------------------------------------------------

	/**
	 * Re-Build Endorsement Premium
	 *
	 *
	 * @param integer $id Endorsement ID
	 * @return void
	 */
	public function premium( $id )
	{

		// Valid Record ?
		$id = (int)$id;
		$record = $this->endorsement_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Permissions & Editability
		 */
		_ENDORSEMENT_is_editable($record->status, $record->flag_current);


		/**
		 * Valid Endorsement Type for Premium?
		 */
		if( !$this->endorsement_model->is_transactional($record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Transaction Type!'
			], 400);
		}

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($record->policy_id);
		if(!$policy_record)
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Corresponding policy record could not be found!'
			], 404);
		}


		// Post? Save Premium
		$this->_save_premium($policy_record, $record);


		// Render Form
		$this->_render_premium_form($policy_record, $record);
	}

	// --------------------------------------------------------------------

		/**
		 * Save/Update Policy Premium
		 *
		 * !!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record 	Policy Record
		 * @param object $record 		Endorsement Record
		 * @return mixed
		 */
		private function _save_premium($policy_record, $record)
		{
			if( $this->input->post() )
			{
				$portfolio_id = (int)$policy_record->portfolio_id;
				load_portfolio_helper($portfolio_id);

				// --------------------------------------------------------------------

				$done = FALSE;

				/**
				 * FAC-Inward Policy???
				 * ----------------------
				 * IF Policy is FAC-Inward, Regardless of Portfolio - Common to all portfolio
				 */
				if($policy_record->category == IQB_POLICY_CATEGORY_FAC_IN )
				{
					$done = $this->__save_premium_FAC_IN($policy_record, $record);
				}
				else
				{
					/**
					 * Save Premium Based on Type
					 */
					$txn_type = (int)$record->txn_type;
					switch ($txn_type)
					{
						case IQB_ENDORSEMENT_TYPE_FRESH:
						case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
						case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
							# code...
							break;

						case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
							$done = $this->__save_premium_ownership_transfer($policy_record, $record);
							break;


						case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
							$done = $this->__save_premium_terminate_and_refund($policy_record, $record);
							break;

						default:
							# code...
							break;
					}
				}



				if($done)
				{

					/**
					 * Build and Update Installments
					 */
					$record = $this->endorsement_model->get($record->id);
					try {

						$this->_save_installments($policy_record, $record);

					} catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
					}



					// Get Updated Record or Premium Box
					if( !$this->endorsement_model->is_first( $record->txn_type) )
					{
						return $this->template->json([
							'message' 		=> 'Successfully Updated.',
							'status'  			=> 'success',
							'reloadForm' 	=> false,
							'hideBootbox' 	=> true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_data-row-endorsements-' . $record->id,
								'method' 	=> 'replaceWith',
								'html'		=> $this->load->view('endorsements/_single_row', ['record' => $record], TRUE)
							]
						]);
					}
					else
					{
						return $this->template->json([
							'message' 		=> 'Successfully Updated.',
							'status'  		=> 'success',
							'updateSection' => true,
							'hideBootbox' 	=> true,
							'updateSectionData' => [
								/**
								 * Policy Cost Calculation Table
								 */
								'box' 		=> '#_premium-card',
								'method' 	=> 'replaceWith',
								'html'		=> $this->load->view('endorsements/_cost_calculation_table', ['endorsement_record' => $record, 'policy_record' => $policy_record], TRUE)
							]
						]);
					}
				}
			}
		}

			// --------------------------------------------------------------------

			/**
			 * Save Ownership Transfer - Premium
			 *
			 * @param object $policy_record
			 * @param object $record
			 * @return boolean
			 */
			private function __save_premium_ownership_transfer($policy_record, $record)
			{

				$v_rules =  $this->endorsement_model->get_fee_validation_rules(
								$record->txn_type,
								$record->portfolio_id,
								TRUE
							);
				$this->form_validation->set_rules($v_rules);
				if($this->form_validation->run() === TRUE )
	        	{
	        		$post_data 		= $this->input->post();
	        		$premium_data 	= [];

	        		foreach($v_rules as $single)
	        		{
	        			$field = $single['field'];
	        			$premium_data[$field] = $post_data[$field] ?? NULL;
	        		}
	        		return $this->endorsement_model->save_premium($record, $policy_record, $premium_data, $post_data);
	        	}
	        	else
	        	{
	        		return $this->template->json([
						'status' 	=> 'error',
						'title' 	=> 'Validation Error!',
						'message' 	=> validation_errors()
					]);
	        	}
			}

			// --------------------------------------------------------------------

			/**
			 * Save Ownership Transfer - Premium
			 *
			 * @param object $policy_record
			 * @param object $record
			 * @return boolean
			 */
			private function __save_premium_terminate_and_refund($policy_record, $record)
			{

				$v_rules =  $this->endorsement_model->get_fee_validation_rules(
								$record->txn_type,
								$record->portfolio_id,
								TRUE
							);
				$this->form_validation->set_rules($v_rules);
				if($this->form_validation->run() === TRUE )
	        	{
	        		$post_data 		= $this->input->post();
	        		$premium_data 	= [];

	        		foreach($v_rules as $single)
	        		{
	        			$field = $single['field'];
	        			$premium_data[$field] = $post_data[$field] ?? NULL;
	        		}
	        		return $this->endorsement_model->save_premium($record, $policy_record, $premium_data, $post_data);
	        	}
	        	else
	        	{
	        		return $this->template->json([
						'status' 	=> 'error',
						'title' 	=> 'Validation Error!',
						'message' 	=> validation_errors()
					]);
	        	}
			}

			// --------------------------------------------------------------------




		private function _save_premium_OLD($policy_record, $record)
		{
			if( $this->input->post() )
			{
				$portfolio_id = (int)$policy_record->portfolio_id;
				load_portfolio_helper($portfolio_id);

				$done = FALSE;

				/**
				 * FAC-Inward Policy???
				 * ----------------------
				 * IF Policy is FAC-Inward, Regardless of Portfolio - Common to all portfolio
				 */
				if($policy_record->category == IQB_POLICY_CATEGORY_FAC_IN )
				{
					$done = $this->__save_premium_FAC_IN($policy_record, $record);
				}


				/**
		         * AGRICULTURE - CROP SUB-PORTFOLIO
		         * ---------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
		        {
		            $done = __save_premium_AGR_CROP($policy_record, $record);
		        }

		        /**
		         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
		         * ---------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
		        {
		            $done = __save_premium_AGR_CATTLE($policy_record, $record);
		        }

		        /**
		         * AGRICULTURE - POULTRY SUB-PORTFOLIO
		         * -----------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
		        {
		            $done = __save_premium_AGR_POULTRY($policy_record, $record);
		        }

		        /**
		         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
		         * ----------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
		        {
		            $done = __save_premium_AGR_FISH($policy_record, $record);
		        }

		        /**
		         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
		         * -------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
		        {
		            $done = __save_premium_AGR_BEE($policy_record, $record);
		        }

				/**
				 * MOTOR PORTFOLIOS
				 * ----------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
				{
					$done = __save_premium_MOTOR( $policy_record, $record );
				}

				/**
		         * FIRE - FIRE
		         * -------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
		        {
		            $done = __save_premium_FIRE_FIRE( $policy_record, $record );
		        }

		        /**
		         * FIRE - HOUSEHOLDER
		         * -------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
		        {
		            $done = __save_premium_FIRE_HHP( $policy_record, $record );
		        }

		        /**
		         * FIRE - LOSS OF PROFIT
		         * ----------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
		        {
		            $done = __save_premium_FIRE_LOP( $policy_record, $record );
		        }

				/**
		         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
		         * --------------------------------------------------
		         */
		        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
		        {
		            $done = __save_premium_MISC_BRG( $policy_record, $record );
		        }

				/**
				 * MARINE PORTFOLIOS
				 * ---------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
				{
					$done = __save_premium_MARINE( $policy_record, $record );
				}

				/**
		         * ENGINEERING - BOILER EXPLOSION
		         * ------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
		        {
		            $done = __save_premium_ENG_BL( $policy_record, $record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR ALL RISK
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
		        {
		            $done = __save_premium_ENG_CAR( $policy_record, $record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
		         * ------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
		        {
		            $done = __save_premium_ENG_CPM( $policy_record, $record );
		        }

		        /**
		         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
		        {
		            $done = __save_premium_ENG_EEI( $policy_record, $record );
		        }

		        /**
		         * ENGINEERING - ERECTION ALL RISKS
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
		        {
					$done = __save_premium_ENG_EAR( $policy_record, $record );
		        }

		        /**
		         * ENGINEERING - MACHINE BREAKDOWN
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
		        {
		            $done = __save_premium_ENG_MB( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - BANKER'S BLANKET(BB)
		         * -------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
		        {
		            $done = __save_premium_MISC_BB( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
		        {
		            $done = __save_premium_MISC_GPA( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
		        {
		            $done = __save_premium_MISC_PA( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
		        {
		            $done = __save_premium_MISC_PL( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN TRANSIT
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
		        {
		            $done = __save_premium_MISC_CT( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN SAFE
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
		        {
		            $done = __save_premium_MISC_CS( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN COUNTER
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
		        {
		            $done = __save_premium_MISC_CC( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
		        {
		            $done = __save_premium_MISC_EPA( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
		        {
		            $done = __save_premium_MISC_TMI( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
		        {
		            $done = __save_premium_MISC_FG( $policy_record, $record );
		        }

		        /**
		         * MISCELLANEOUS - HEALTH INSURANCE (HI)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
		        {
		            $done = __save_premium_MISC_HI( $policy_record, $record );
		        }

				else
				{
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Endorsements::__save_premium() - No method defined for supplied portfolio!'
					], 400);
				}


				if($done)
				{

					/**
					 * Build and Update Installments
					 */
					$record = $this->endorsement_model->get($record->id);
					try {

						$this->_save_installments($policy_record, $record);

					} catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
					}



					// Get Updated Record or Premium Box
					if( !$this->endorsement_model->is_first( $record->txn_type) )
					{
						return $this->template->json([
							'message' 		=> 'Successfully Updated.',
							'status'  			=> 'success',
							'reloadForm' 	=> false,
							'hideBootbox' 	=> true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_data-row-endorsements-' . $record->id,
								'method' 	=> 'replaceWith',
								'html'		=> $this->load->view('endorsements/_single_row', ['record' => $record], TRUE)
							]
						]);
					}
					else
					{
						return $this->template->json([
							'message' 		=> 'Successfully Updated.',
							'status'  		=> 'success',
							'updateSection' => true,
							'hideBootbox' 	=> true,
							'updateSectionData' => [
								/**
								 * Policy Cost Calculation Table
								 */
								'box' 		=> '#_premium-card',
								'method' 	=> 'replaceWith',
								'html'		=> $this->load->view('endorsements/_cost_calculation_table', ['endorsement_record' => $record, 'policy_record' => $policy_record], TRUE)
							]
						]);
					}
				}
			}
		}

		/**
		 * Save FAC-Inward Policy premium
		 *
		 * @param object $policy_record
		 * @param object $record
		 * @return boolean
		 */
		private function __save_premium_FAC_IN($policy_record, $record)
		{
			$v_rules = $this->endorsement_model->fac_in_premium_v_rules();

			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$policy_object 	= get_object_from_policy_record($policy_record);
        		$post_data 		= $this->input->post();

        		$gross_full_amt_basic_premium = $post_data['gross_full_amt_basic_premium'];
        		$percent_ri_commission 	 = $post_data['percent_ri_commission'];

        		// Compute amt_ri_commission
        		$comm_percent 				= bcdiv($percent_ri_commission, 100.00, IQB_AC_DECIMAL_PRECISION);
        		$gross_full_amt_ri_commission 	= bcmul( $gross_full_amt_basic_premium, $comm_percent, IQB_AC_DECIMAL_PRECISION);

        		// Cost Calculation Table
        		$cost_calculation_table = [
        			[
        				'label' => 'FAC - Inward Premium (Rs.)',
        				'value' => $gross_full_amt_basic_premium
        			],
        			// [
        			// 	'label' => "Commission on FAC Accepted ({$percent_ri_commission}%)",
        			// 	'value' => $gross_full_amt_ri_commission
        			// ]
        		];

        		// Update only FAC IN related Fields
        		$premium_data = [

        			// Sum Insured
        			'amt_sum_insured_object' 	=> $policy_object->amt_sum_insured,
					'amt_sum_insured_net' 		=> $policy_object->amt_sum_insured,

					// Basic Premium
					'gross_full_amt_basic_premium' 		=> $gross_full_amt_basic_premium,
					'gross_computed_amt_basic_premium' 	=> $gross_full_amt_basic_premium,
					'refund_amt_basic_premium' 			=> 0.00,
					'net_amt_basic_premium' 			=> $gross_full_amt_basic_premium,

					// Pool Premium
					'gross_full_amt_pool_premium' 		=> 0.00,
					'gross_computed_amt_pool_premium' 	=> 0.00,
					'refund_amt_pool_premium' 			=> 0.00,
					'net_amt_pool_premium' 				=> 0.00,

					// NO COMMISSION
					'gross_full_amt_commissionable' 		=> NULL,
					'gross_computed_amt_commissionable' 	=> NULL,
					'refund_amt_commissionable' 			=> NULL,
					'net_amt_commissionable' 				=> NULL,

					'gross_full_amt_agent_commission' 		=> NULL,
					'gross_computed_amt_agent_commission' 	=> NULL,
					'refund_amt_agent_commission' 			=> NULL,
					'net_amt_agent_commission' 				=> NULL,

					// NO Direct Discount
					'gross_full_amt_direct_discount' 		=> NULL,
					'gross_computed_amt_direct_discount' 	=> NULL,
					'refund_amt_direct_discount' 			=> NULL,
					'net_amt_direct_discount' 				=> NULL,

					// NO Stamp Duty, NO VAT, NO Transfer fee
					// no cancelation fee
					'net_amt_stamp_duty' 		=> 0.00,
					'net_amt_vat' 				=> 0.00,
					'net_amt_transfer_fee' 		=> NULL,
					'net_amt_transfer_ncd' 		=> NULL,
					'net_amt_cancellation_fee' 	=> NULL,

					// Percent RI Commission
					'percent_ri_commission' 			=> $percent_ri_commission,
					'gross_full_amt_ri_commission' 		=> $gross_full_amt_ri_commission,
					'gross_computed_amt_ri_commission' 	=> $gross_full_amt_ri_commission,
					'refund_amt_ri_commission' 			=> 0.00,
					'net_amt_ri_commission' 			=> $gross_full_amt_ri_commission,

					// Other Fields
					'premium_compute_options' => json_encode([]),
					'cost_calculation_table' 	=> json_encode($cost_calculation_table),
				];

				return $this->endorsement_model->save($record->id, $premium_data);
        	}
        	else
        	{
        		return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Validation Error!',
					'message' 	=> validation_errors()
				]);
        	}
		}

		// --------------------------------------------------------------------

		/**
		 * Save Installments - Premium Upgrade/Downgrade
		 *
		 * Every portfolio is installment based.
		 * Default number of installment is 1.
		 *
		 * If you want to have multiple installments allowed for particular portoflio,
		 * you can do so by allowing multiple installments via settings:
		 *
		 * 	Master Setup >> Portfolio >> Portfolio Settings
		 *
		 * @param object $policy_record
		 * @param object $record
		 * @return mixed
		 */
		private function _save_installments($policy_record, $record)
		{
			$this->load->model('policy_installment_model');

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			/**
			 * Installment applies only on Regular/CO-Inusrance Policy
			 */
			if(
				in_array($policy_record->category, [IQB_POLICY_CATEGORY_REGULAR, IQB_POLICY_CATEGORY_CO_INSURANCE])
				&&
				$pfs_record->flag_installment === IQB_FLAG_YES
			){
				// Get Multiple Installments
				$dates 		= $this->input->post('installment_date') ?? NULL;
				$percents 	= $this->input->post('percent') ?? NULL;

				if(empty($dates) OR empty($percents))
				{
					throw new Exception("Exception [Controller:Endorsements][Method: _save_installments()]: No installment data found. <br/>You integrate and supply installment information on premium for of this PORTFOLIO.");
				}

				$installment_data = [
					'dates' 	=> $dates,
					'percents' 	=> $percents,
				];
			}
			else
			{
				// Single Installment
				$installment_data = [
					'dates' 	=> [date('Y-m-d')], // Today
					'percents' 	=> [100],
				];
			}

			/**
			 * Set Installment Type
			 */
			$installment_data['installment_type'] = $this->policy_installment_model->get_type( $record->txn_type );

			return $this->policy_installment_model->build($record, $installment_data);
		}

		// --------------------------------------------------------------------

		/**
		 * Save Installments - Ownership Transfer
		 *
		 * Every portfolio is installment based.
		 * Default number of installment is 1.
		 *
		 * If you want to have multiple installments allowed for particular portoflio,
		 * you can do so by allowing multiple installments via settings:
		 *
		 * 	Master Setup >> Portfolio >> Portfolio Settings
		 *
		 * @param object $policy_record
		 * @param object $record
		 * @return mixed
		 */
		private function _save_installment_OT($policy_record, $record)
		{
			$this->load->model('policy_installment_model');

			// Single Installment
			$installment_data = [
				'dates' 			=> [date('Y-m-d')], // Today
				'percents' 			=> [100],
				'installment_type' 	=> $this->policy_installment_model->get_type( $record->txn_type )
			];

			return $this->policy_installment_model->build($record, $installment_data);
		}

		// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check if total Installment is 100%
		 *
		 * @param mixed $str
		 * @return bool
		 */
		public function _cb_installment_complete($str)
		{
			$post_percent 		= $this->input->post('percent');
			$installment_dates 	= $this->input->post('installment_date');

			$default_count = count($installment_dates);
			$unique_count = count( array_unique( array_filter($installment_dates)) );

			// Installment date duplicate?
	        if( $unique_count != $default_count )
	        {
	            $this->form_validation->set_message('_cb_installment_complete', 'Duplicate/Empty Installment Date.');
	            return FALSE;
	        }

	        // Installment Date Order
	        $first_date = $installment_dates[0];
	        $invalid_date_order = FALSE;
	        for($i =0; $i < $default_count - 1; $i++)
	        {
	        	$second_date = $installment_dates[$i+1];
	        	if(strtotime($first_date) > strtotime($second_date))
	        	{
	        		$this->form_validation->set_message('_cb_installment_complete', 'Invalid installment date order. First installment date should be earlier than Second installment date and so on.');
	        		$invalid_date_order = TRUE;
	        		break;
	        	}
	        	// Check next consecutive pair [(a1,a2), (a2,a3), ... (an-1, an)] where a1 < a2 and so on
	        	$first_date = $second_date;
	        }
	        if( $invalid_date_order )
	        {
	        	return FALSE;
	        }



			$total = 0;
			foreach($post_percent as $percent)
			{
				$total += (float)$percent;
			}
			$total = (int)$total;

			// 100% ?
	        if( $total != 100 )
	        {
	            $this->form_validation->set_message('_cb_installment_complete', 'The TOTAL of all installment must be equal to 100.');
	            return FALSE;
	        }
	        return TRUE;
		}


	// --------------- END: SAVE PREMIUM FUNCTIONS --------------------



	/**
	 * Render Policy Txn Premium Form
	 *
	 * We will be generating premium or manually entering premium for -
	 * 	1. Fresh/Renewal Policy
	 * 	2. Transactional Endorsement
	 *
	 * @param object 	$policy_record 	Policy Record
	 * @param object 	$record Endorsement Record
	 * @param array 	$json_extra 	Extra Data to Pass as JSON
	 * @return type
	 */
	private function _render_premium_form($policy_record, $record, $json_extra=[])
	{

		/**
		 * IF Policy is FAC-Inward, It has a Different FORM
		 */
		if($policy_record->category == IQB_POLICY_CATEGORY_FAC_IN )
		{
			return $this->__render_premium_form_FAC_IN($policy_record, $record, $json_extra);
		}

		// --------------------------------------------------------------------

		/**
		 * Return the premium form based on type
		 */
		$txn_type = (int)$record->txn_type;
		switch ($txn_type)
		{
			case IQB_ENDORSEMENT_TYPE_FRESH:
			case IQB_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
			case IQB_ENDORSEMENT_TYPE_PREMIUM_REFUND:
				# code...
				break;

			case IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
				return $this->__premium_form_fee($policy_record, $record, $json_extra);
				break;


			case IQB_ENDORSEMENT_TYPE_REFUND_AND_TERMINATE:
				return $this->__premium_form_fee($policy_record, $record, $json_extra);
				break;

			default:
				# code...
				break;
		}





		/**
		 *  Let's Load The Endorsement Form For this Record
		 */
		$policy_object = get_object_from_policy_record($policy_record);

		// Let's get all risks of this portfolio
		$portfolio_risks = $this->portfolio_model->dropdown_risks($policy_record->portfolio_id);


		// Let's get the premium goodies for given portfolio
		$premium_goodies = $this->__premium_goodies($policy_record, $policy_object, $portfolio_risks);

		// Not Valid Goodies?
		if( $premium_goodies['status'] === 'error' )
		{
			return $this->template->json($premium_goodies, 400);
		}


		/**
		 * Portfolio Settings Record
		 */
		$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


		/**
         * Common Views:
         *
         * 	1. Premium Installment Section
         */
		$common_components = '';
        if($pfs_record->flag_installment === IQB_FLAG_YES )
		{
	        $common_components = $this->load->view('endorsements/forms/_form_txn_installments', [
	            'endorsement_record'    => $record,
	            'form_elements'     	=> $premium_goodies['validation_rules']['installments']
	        ], TRUE);
	    }


		/**
		 * Do we have to compute premium manually?
		 *
		 * If so, let's render manual premium form
		 */
		if( $this->endorsement_model->is_endorsement_manual($record->portfolio_id, $record->txn_type) )
		{
			$json_data['form'] = $this->load->view('endorsements/forms/_form_premium_manual', [
								                'form_elements'         => $this->endorsement_model->manual_premium_v_rules(),
								                'policy_record'         => $policy_record,
								                'endorsement_record'    => $record,
								                'common_components' 	=> $common_components
								            ], TRUE);
			$json_data = array_merge($json_data, $json_extra);
			$this->template->json($json_data);
			exit(0);
		}



		// Endorsement Form
		try{
			$form_view = _POLICY__partial_view__premium_form($policy_record->portfolio_id);
		}
		catch (Exception $e) {

			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}


		// Let's render the form
        $json_data['form'] = $this->load->view($form_view, [
								                'form_elements'         => $premium_goodies['validation_rules'],
								                'portfolio_risks' 		=> $portfolio_risks,
								                'policy_record'         => $policy_record,
								                'endorsement_record'    => $record,
								                'policy_object' 		=> $policy_object,
								                'tariff_record' 		=> $premium_goodies['tariff_record'],
								                'common_components' 	=> $common_components
								            ], TRUE);





        $json_data = array_merge($json_data, $json_extra);

        // Return HTML
        $this->template->json($json_data);
	}

		// --------------------------------------------------------------------

		/**
		 * Render Ownership Transfer Premium Form
		 *
		 * @param object $policy_record
		 * @param object $record
		 * @param type|array $json_extra
		 * @return void
		 */
		private function __premium_form_fee($policy_record, $record, $json_extra = [])
		{
			$json_data['form'] = $this->load->view('endorsements/forms/_form_premium_fee', [
                'form_elements'     => $this->endorsement_model->get_fee_validation_rules(
            									$record->txn_type,
            									$record->portfolio_id
            								),
                'policy_record'     => $policy_record,
                'record'    		=> $record
            ], TRUE);
			$json_data = array_merge($json_data, $json_extra);
			return $this->template->json($json_data);
		}

	// --------------------------------------------------------------------

	private function __render_premium_form_FAC_IN($policy_record, $record, $json_extra=[])
	{
		$json_data['form'] = $this->load->view('endorsements/forms/_form_premium_fac_in', [
								                'form_elements'         => $this->endorsement_model->fac_in_premium_v_rules(),
								                'policy_record'         => $policy_record,
								                'endorsement_record'    => $record
								            ], TRUE);
		$json_data = array_merge($json_data, $json_extra);
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------
	// PREMIUM GOODIES FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Get Policy Endorsement Goodies
		 *
		 * Get the following goodies for the Given Portfolio of Supplied Policy
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 * @param array $portfolio_risks Portfolio Risks
		 *
		 * @return	array
		 */
		private function __premium_goodies($policy_record, $policy_object, $portfolio_risks=[])
		{
			$goodies = [];
			$portfolio_id = (int)$policy_record->portfolio_id;

			load_portfolio_helper($portfolio_id);

			/**
	         * AGRICULTURE - CROP SUB-PORTFOLIO
	         * ---------------------------------
	         */
	        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
	        {
	            $goodies = _TXN_AGR_CROP_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
	         * ---------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
	        {
	            $goodies = _TXN_AGR_CATTLE_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - POULTRY SUB-PORTFOLIO
	         * -----------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
	        {
	            $goodies = _TXN_AGR_POULTRY_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
	         * ----------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
	        {
	            $goodies = _TXN_AGR_FISH_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
	         * -------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
	        {
	            $goodies = _TXN_AGR_BEE_premium_goodies($policy_record, $policy_object);
	        }


			/**
			 * MOTOR PORTFOLIOS
			 * ----------------
			 */
			else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
			{
				$goodies = _TXN_MOTOR_premium_goodies($policy_record, $policy_object);
			}

			/**
	         * FIRE - FIRE
	         * -------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
	        {
	            $goodies = _TXN_FIRE_FIRE_premium_goodies($policy_record, $policy_object, $portfolio_risks);
	        }

	        /**
	         * FIRE - HOUSEHOLDER
	         * -------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
	        {
	            $goodies = _TXN_FIRE_HHP_premium_goodies($policy_record, $policy_object, $portfolio_risks);
	        }

	        /**
	         * FIRE - LOSS OF PROFIT
	         * ----------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
	        {
	            $goodies = _TXN_FIRE_LOP_premium_goodies($policy_record, $policy_object);
	        }

			/**
	         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
	         * --------------------------------------------------
	         */
	        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
	        {
	            $goodies = _TXN_MISC_BRG_premium_goodies($policy_record, $policy_object);
	        }

			/**
			 * MARINE PORTFOLIOS
			 * ---------------
			 */
			else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
			{
				$goodies = _TXN_MARINE_premium_goodies($policy_record, $policy_object);
			}

			/**
			 * BOILER EXPLOSION (ENGINEERING)
			 * -----------------------------
			 */
			else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
			{
				$goodies = _TXN_ENG_BL_premium_goodies($policy_record, $policy_object);
			}

			/**
	         * ENGINEERING - CONTRACTOR ALL RISK
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
	        {
	            $goodies = _TXN_ENG_CAR_premium_goodies($policy_record, $policy_object);
	        }

			/**
			 * CONTRACTOR PLANT & MACHINARY (ENGINEERING)
			 * -------------------------------------------
			 */
			else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
			{
				$goodies = _TXN_ENG_CPM_premium_goodies($policy_record, $policy_object);
			}

			/**
	         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
	        {
	            $goodies = _TXN_ENG_EEI_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * ENGINEERING - ERECTION ALL RISKS
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
	        {
				$goodies = _TXN_ENG_EAR_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * ENGINEERING - MACHINE BREAKDOWN
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
	        {
	            $goodies = _TXN_ENG_MB_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - BANKER'S BLANKET(BB)
	         * -------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
	        {
	            $goodies = _TXN_MISC_BB_premium_goodies($policy_record, $policy_object, $portfolio_risks);
	        }

	        /**
	         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
	        {
	        	$goodies = _TXN_MISC_GPA_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
	        {
	            $goodies = _TXN_MISC_PA_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
	         * ----------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
	        {
	            $goodies = _TXN_MISC_PL_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - CASH IN TRANSIT
	         * -------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
	        {
	            $goodies = _TXN_MISC_CT_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - CASH IN SAFE
	         * -------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
	        {
	            $goodies = _TXN_MISC_CS_premium_goodies($policy_record, $policy_object, $portfolio_risks);
	        }

	        /**
	         * MISCELLANEOUS - CASH IN COUNTER
	         * -------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
	        {
	            $goodies = _TXN_MISC_CC_premium_goodies($policy_record, $policy_object, $portfolio_risks);
	        }

	        /**
	         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
	         * --------------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
	        {
	            $goodies = _TXN_MISC_EPA_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
	         * --------------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
	        {
	            $goodies = _TXN_MISC_TMI_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
	         * ----------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
	        {
	            $goodies = _TXN_MISC_FG_premium_goodies($policy_record, $policy_object);
	        }

	        /**
	         * MISCELLANEOUS - HEALTH INSURANCE (HI)
	         * ----------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
	        {
	            $goodies = _TXN_MISC_HI_premium_goodies($policy_record, $policy_object);
	        }

			/**
			 * Show error Message
			 */
			else
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Endorsements::__premium_goodies() - No data found for supplied portfolio!'
				], 400);
			}

			return $goodies;
		}

	//  ------------------- END: PREMIUM GOODIES FUNCTIONS -------------------------


	// --------------------------------------------------------------------
	//  ENDORSEMENT/TRANSACTION PRINT(PDF)
	// --------------------------------------------------------------------

	/**
	 * Print Endorsement(s)
	 *
	 * Based on the type supplied, either print all endorsement/transactions or single one
	 *
	 * 		$type 	all|single
	 * 		$key 	Policy ID (type=all), Policy TXN ID (type=single)
	 *
	 * Please note that if type is set "all", it will print all active endorsement only
	 *
	 * @param string $type
	 * @param integer $key
	 * @return void
	 */
	public function print($type, $key)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('endorsements', 'print.endorsement') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Valid Type?
		 */
		if( !in_array($type, ['all', 'single']) )
		{
			$this->template->render_404();
		}



		/**
		 * Get Transaction Records or Record based on type
		 */
		$key = (int)$key;
		if( $type === 'all' )
		{
			$where = [
				'P.id' 			=> $key,
				'E.status' 		=> IQB_ENDORSEMENT_STATUS_ACTIVE,
				'E.txn_type !=' => IQB_ENDORSEMENT_TYPE_FRESH
			];
		}
		else
		{
			$where = [
				'E.id' 	=> $key
			];
		}

		$records = $this->endorsement_model->schedule_list($where);
		if(!$records)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'No endorsement found'
			], 404);
		}

		/**
		 * Creditors
		 */
		$this->load->model('rel_policy_creditor_model');
		$creditors = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $records[0]->policy_id]);



		/**
		 * Schedule Language
		 */
		$portfolio_record = $this->portfolio_model->find($records[0]->portfolio_id);
		if( !$portfolio_record->schedule_lang )
		{
			return $this->template->json([
				'title'  => 'Incomplete Portfolio Setup!',
				'status' => 'error',
				'message' => "Schedule Language for {$portfolio_record->name_en} is missing. <br> Please contact Administrator."
			], 409);
		}

		$data = [
			'records' 	=> $records,
			'creditors' => $creditors,
			'type' 		=> $type,
			'lang' 		=> $portfolio_record->schedule_lang
		];


		/**
		 * Render Print View
		 */
		try {

			_ENDORSEMENT_endorsement_pdf($data);
		}
		catch (Exception $e) {

			return $this->template->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 404);
		}
	}



	//  ------------------- END: ENDORSEMENT/TRANSACTION PRINT(PDF) -------------------------




	// --------------------------------------------------------------------
	//  STATUS UPGRADE/DOWNGRADE
	// --------------------------------------------------------------------

	/**
	 * Upgrade/Downgrade Status of a Policy Txn Record
	 *
	 * @param integer $id Policy ID
	 * @param char $to_status_code Status Code
	 * @param string $ref 	Where does this requrest come from?
	 * @return json
	 */
	public function status($id, $to_status_code, $ref='tab-endorsements')
	{
		$id = (int)$id;
		$record = $this->endorsement_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// is This Current Transaction?
		if( $record->flag_current != IQB_FLAG_ON  )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Current Endorsement Record!'
			], 400);
		}

		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($to_status_code, $record);


		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($to_status_code, $record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->endorsement_model->update_status($record, $to_status_code) )
			{
				/**
				 * Updated Transaction & Policy Record
				 */
				$record = $this->endorsement_model->get($record->id);
				$policy_record 		= $this->policy_model->get($record->policy_id);



				/**
				 * Post Status Update Task
				 *
				 * 1. Save Installment Record on Ownership transfer
				 * 		Since this type does not have premium update function. So we have to do this while we verify it
				 */
				$txn_type = (int)$record->txn_type;
				if( $to_status_code == IQB_ENDORSEMENT_STATUS_VERIFIED && $txn_type == IQB_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER )
				{
					try { $this->_save_installment_OT($policy_record, $record); } catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
					}
				}

				/**
				 * Load Portfolio Specific Helper File
				 */
				try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
					return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
				}

				/**
				 * Refresh View
				 */
				// Replace the Row
				$html = $this->load->view(
											'endorsements/_single_row',
											['record' => $record, 'policy_record' => $policy_record],
										TRUE);

				return $this->template->json([
					'message' 	=> 'Successfully Updated!',
					'status'  	=> 'success',
					'multipleUpdate' => [
						[
							'box' 		=> '#_data-row-endorsements-' . $record->id,
							'method' 	=> 'replaceWith',
							'html' 		=> $html
						]
					]
				]);
			}

		} catch (Exception $e) {

			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Exception Occured.',
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
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __check_status_permission($to_updown_status, $terminate_on_fail = TRUE)
		{
			/**
			 * Check Permission
			 * ------------------------------
			 *
			 */
			$status_keys = array_keys(_ENDORSEMENT_status_dropdown(FALSE));

			// Valid Status Code?
			if( !in_array($to_updown_status, $status_keys ) )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Invalid Status Code!'
				], 403);
			}

			/**
			 * Admin?
			 */
			if($this->dx_auth->is_admin())
			{
				return TRUE;
			}


			// Valid Permission?
			$__flag_valid_permission = FALSE;
			$permission_name 	= '';
			switch ($to_updown_status)
			{
				case IQB_ENDORSEMENT_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_ENDORSEMENT_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_ENDORSEMENT_STATUS_RI_APPROVED:
					$permission_name = 'status.to.ri.approved';
					break;

				case IQB_ENDORSEMENT_STATUS_VOUCHERED:
					$permission_name = 'status.to.vouchered';
					break;

				case IQB_ENDORSEMENT_STATUS_INVOICED:
					$permission_name = 'status.to.invoiced';
					break;

				case IQB_ENDORSEMENT_STATUS_ACTIVE:
					$permission_name = 'status.to.active';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('endorsements', $permission_name) )
			{
				$__flag_valid_permission = TRUE;
			}

			if( !$__flag_valid_permission && $terminate_on_fail )
			{
				$this->dx_auth->deny_access();
			}

			return $__flag_valid_permission;
		}

		// --------------------------------------------------------------------

		/**
		 * Status Qualifies to UP/DOWN
		 *
		 * This is very important that not all type of policy txn has manu status
		 * up/down facility. So we have to look into the type of Policy Txn Record Type and
		 * follow the logic accordingly.
		 *
		 * @param alpha $to_updown_status Status Code to UP/DOWN
		 * @param object $record Endorsement Record
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __status_qualifies($to_updown_status, $record, $terminate_on_fail = TRUE)
		{
			$__flag_passed = $this->endorsement_model->status_qualifies($record->status, $to_updown_status);

			if( $__flag_passed )
			{
				/**
				 * FRESH/RENEWAL Endorsement
				 * 	Draft/Verified are automatically triggered from
				 * 	Policy Status Update Method
				 */
				if( $this->endorsement_model->is_first($record->txn_type) )
				{
					$__flag_passed = !in_array($to_updown_status, [
						IQB_ENDORSEMENT_STATUS_DRAFT,
						IQB_ENDORSEMENT_STATUS_VERIFIED,
						IQB_ENDORSEMENT_STATUS_ACTIVE
					]);
				}
			}


			/**
			 * Premium Must be Updated Before Verifying
			 */
			if(
				$__flag_passed
					&&
				$this->endorsement_model->is_premium_computed($record) === IQB_FLAG_NO
					&&
				$to_updown_status === IQB_ENDORSEMENT_STATUS_VERIFIED
			)
			{
				$__flag_passed 		= FALSE;
				$failed_message 	= 'Please Update Premium Information First!';
			}



			/**
			 * Can not Update Transactional Status Directly using status function
			 */
			if( $__flag_passed )
			{
				$__flag_passed = !in_array($to_updown_status, [
					IQB_ENDORSEMENT_STATUS_VOUCHERED,
					IQB_ENDORSEMENT_STATUS_INVOICED
				]);
			}

			/**
			 * General Endorsement
			 * Activate Status
			 *
			 * !!! If RI-Approval Constraint Required, It should Come from That Status else from Verified
			 */
			if( $__flag_passed && $to_updown_status === IQB_ENDORSEMENT_STATUS_ACTIVE && $record->txn_type == IQB_ENDORSEMENT_TYPE_GENERAL )
			{
				if( (int)$record->flag_ri_approval === IQB_FLAG_ON )
				{
					$__flag_passed = $record->status === IQB_ENDORSEMENT_STATUS_RI_APPROVED;
				}
				else
				{
					$__flag_passed = $record->status === IQB_ENDORSEMENT_STATUS_VERIFIED;
				}
			}


			if( !$__flag_passed && $terminate_on_fail )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Invalid Status Transaction',
					'message' 	=> $failed_message ?? 'You can not switch to the state from this state of transaction.'
				], 400);
			}

			return $__flag_passed;

		}

	// --------------------- END: STATUS UPGRADE/DOWNGRADE --------------------


	// --------------------------------------------------------------------
	//  OTHER GENERAL FUNCTIONS
	// --------------------------------------------------------------------

	public function template_reference($portfolio_id, $txn_type)
	{
		$portfolio_id 	= (int)$portfolio_id;
		$txn_type 		= (int)$txn_type;
		$this->load->model('endorsement_template_model');
        $template_dropdown = $this->endorsement_template_model->dropdown( $portfolio_id, $txn_type );

        $this->template->json([
			'status' 	=> 'success',
			'data' 		=> $template_dropdown
		]);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Endorsement template Body
	 *
	 * @param integer $endorsement_template_id
	 * @return json
	 */
	public function template_body($endorsement_template_id)
	{
		$this->load->model('endorsement_template_model');
		$record = $this->endorsement_template_model->row($endorsement_template_id);
		if( !$record )
		{
			$status = 'warning';
			$message = "Endorsement Template Not Found!";
			$body = '';

		}
		else{
			$status = 'success';
			$message = "Found!";
			$body = $record->body;
		}
		return $this->template->json(['status' => $status, 'message' => $message, 'body' => $body]);
	}

	// --------------------- END: OTHER GENERAL FUNCTIONS --------------------

}