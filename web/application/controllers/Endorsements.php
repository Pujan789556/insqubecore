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
	/**
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'endorsements/';

	// --------------------------------------------------------------------

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
		if( _ENDORSEMENT_is_first($record->txn_type) )
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
				'form_elements' => $this->endorsement_model->get_v_rules($txn_type),
				'record' 		=> $record,
				'policy_record' => $policy_record
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
		$txn_types = array_keys( _ENDORSEMENT_type_eonly_dropdown(FALSE) );
		if( !in_array($txn_type, $txn_types) )
		{
			return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'Invalid Endorsement Type.'], 403);
		}

		/**
		 * Can I Add
		 */
		$policy_id = (int)$policy_id;
		if( !$this->_can_add_endorsement($policy_id) )
		{
			return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'You can not add new endorsement as you have unfinished current endorsement.'], 403);
		}

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($policy_id);


		$record 	= NULL;
		$this->_save($txn_type, $policy_record, 'add', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('endorsements/forms/_form_endorsement',
			[
				'form_elements' => $this->endorsement_model->get_v_rules($txn_type),
				'record' 		=> $record,
				'policy_record' => $policy_record
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

				$rules = $this->endorsement_model->get_v_rules($txn_type, TRUE);
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() === TRUE )
	        	{
	        		$data = $this->_prepare_data($txn_type, $post_data);

	        		if($action == 'add')
	        		{
	        			$common_data 	= $this->_prepare_common_on_add($policy_record->id, $txn_type);
	        			$data 		 	= array_merge($common_data, $data);
	        			$done 			= $this->endorsement_model->save_endorsement($data, TRUE);
	        		}
	        		else
	        		{
	        			// Now Update Data
	        			$data['txn_date'] = date('Y-m-d');
						$done = $this->endorsement_model->update($record->id, $data, TRUE);

						// Reset Premium
						$this->endorsement_model->reset($record->id);
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

		private function _prepare_common_on_add($policy_id, $txn_type)
		{
			return [
				'policy_id' 		=> $policy_id,
    			'txn_type'  		=> $txn_type,
    			'txn_date' 			=> date('Y-m-d'),
    			'flag_ri_approval' 	=> $this->endorsement_model->get_flag_ri_approval_by_policy( $policy_id )
			];
		}

	// --------------------------------------------------------------------

		private function _prepare_data($txn_type, $post_data)
		{
			$data = [];
			switch ($txn_type)
			{
				case IQB_POLICY_ENDORSEMENT_TYPE_GENERAL:
					$data = $this->_prepare_data_general($post_data);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
					$data = $this->_prepare_data_ownership_transfer($post_data);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
					$data = $this->_prepare_data_premium_upgrade($post_data);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
					$data = $this->_prepare_data_premium_refund($post_data);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE:
					$data = $this->_prepare_data_terminate($post_data);
					break;

				default:
					# code...
					break;
			}
			return $data;
		}

			private function _prepare_data_general($post_data)
			{
				return [
					'txn_details' => $post_data['txn_details']
				];
			}

			private function _prepare_data_ownership_transfer($post_data)
			{
				$fields = ['txn_details', 'amt_transfer_fee', 'amt_transfer_ncd', 'amt_stamp_duty', 'transfer_customer_id'];
				$data = [];
				foreach($fields as $key)
				{
					$data[$key] = $post_data[$key] ?? NULL;
				}
				return $data;
			}

			private function _prepare_data_premium_upgrade($post_data)
			{
				$fields = ['txn_details', 'computation_basis', 'amt_stamp_duty'];
				$data = [];
				foreach($fields as $key)
				{
					$data[$key] = $post_data[$key] ?? NULL;
				}
				return $data;
			}

			private function _prepare_data_premium_refund($post_data)
			{
				$fields = ['txn_details', 'computation_basis', 'amt_stamp_duty', 'flag_terminate'];
				$data = [];
				foreach($fields as $key)
				{
					$data[$key] = $post_data[$key] ?? NULL;
				}
				return $data;
			}

			private function _prepare_data_terminate($post_data)
			{
				return [
					'txn_details' => $post_data['txn_details']
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
			$current_txn = $this->endorsement_model->get_current_endorsement_by_policy($policy_id);

			return $current_txn->status === IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE;
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
			$record->status !== IQB_POLICY_ENDORSEMENT_STATUS_DRAFT
							||
			! _ENDORSEMENT_is_deletable_by_type($record->txn_type)
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
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($record->policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}


		/**
		 * Valid Transaction Type for Premium Computation?
		 */
		if( !_ENDORSEMENT_is_premium_computable_by_type($record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Transaction Type!'
			], 400);
		}

		// Post? Save Premium
		$this->__save_premium($policy_record, $record);


		// Render Form
		$this->__render_premium_form($policy_record, $record);
	}

	// --------------------------------------------------------------------

		/**
		 * Save/Update Policy Premium
		 *
		 * !!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record 	Policy Record
		 * @param object $endorsement_record 		Endorsement Record
		 * @return mixed
		 */
		private function __save_premium($policy_record, $endorsement_record)
		{
			if( $this->input->post() )
			{
				$portfolio_id = (int)$policy_record->portfolio_id;
				load_portfolio_helper($portfolio_id);

				$done = FALSE;

				/**
		         * AGRICULTURE - CROP SUB-PORTFOLIO
		         * ---------------------------------
		         */
		        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
		        {
		            $done = __save_premium_AGR_CROP($policy_record, $endorsement_record);
		        }

		        /**
		         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
		         * ---------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
		        {
		            $done = __save_premium_AGR_CATTLE($policy_record, $endorsement_record);
		        }

		        /**
		         * AGRICULTURE - POULTRY SUB-PORTFOLIO
		         * -----------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
		        {
		            $done = __save_premium_AGR_POULTRY($policy_record, $endorsement_record);
		        }

		        /**
		         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
		         * ----------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
		        {
		            $done = __save_premium_AGR_FISH($policy_record, $endorsement_record);
		        }

		        /**
		         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
		         * -------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
		        {
		            $done = __save_premium_AGR_BEE($policy_record, $endorsement_record);
		        }

				/**
				 * MOTOR PORTFOLIOS
				 * ----------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
				{
					$done = __save_premium_MOTOR( $policy_record, $endorsement_record );
				}

				/**
		         * FIRE - FIRE
		         * -------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
		        {
		            $done = __save_premium_FIRE_FIRE( $policy_record, $endorsement_record );
		        }

		        /**
		         * FIRE - HOUSEHOLDER
		         * -------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
		        {
		            $done = __save_premium_FIRE_HHP( $policy_record, $endorsement_record );
		        }

		        /**
		         * FIRE - LOSS OF PROFIT
		         * ----------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
		        {
		            $done = __save_premium_FIRE_LOP( $policy_record, $endorsement_record );
		        }

				/**
		         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
		         * --------------------------------------------------
		         */
		        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
		        {
		            $done = __save_premium_MISC_BRG( $policy_record, $endorsement_record );
		        }

				/**
				 * MARINE PORTFOLIOS
				 * ---------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
				{
					$done = __save_premium_MARINE( $policy_record, $endorsement_record );
				}

				/**
		         * ENGINEERING - BOILER EXPLOSION
		         * ------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
		        {
		            $done = __save_premium_ENG_BL( $policy_record, $endorsement_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR ALL RISK
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
		        {
		            $done = __save_premium_ENG_CAR( $policy_record, $endorsement_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
		         * ------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
		        {
		            $done = __save_premium_ENG_CPM( $policy_record, $endorsement_record );
		        }

		        /**
		         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
		        {
		            $done = __save_premium_ENG_EEI( $policy_record, $endorsement_record );
		        }

		        /**
		         * ENGINEERING - ERECTION ALL RISKS
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
		        {
					$done = __save_premium_ENG_EAR( $policy_record, $endorsement_record );
		        }

		        /**
		         * ENGINEERING - MACHINE BREAKDOWN
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
		        {
		            $done = __save_premium_ENG_MB( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - BANKER'S BLANKET(BB)
		         * -------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
		        {
		            $done = __save_premium_MISC_BB( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
		        {
		            $done = __save_premium_MISC_GPA( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
		        {
		            $done = __save_premium_MISC_PA( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
		        {
		            $done = __save_premium_MISC_PL( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN TRANSIT
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
		        {
		            $done = __save_premium_MISC_CT( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN SAFE
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
		        {
		            $done = __save_premium_MISC_CS( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN COUNTER
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
		        {
		            $done = __save_premium_MISC_CC( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
		        {
		            $done = __save_premium_MISC_EPA( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
		        {
		            $done = __save_premium_MISC_TMI( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
		        {
		            $done = __save_premium_MISC_FG( $policy_record, $endorsement_record );
		        }

		        /**
		         * MISCELLANEOUS - HEALTH INSURANCE (HI)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
		        {
		            $done = __save_premium_MISC_HI( $policy_record, $endorsement_record );
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
					$endorsement_record = $this->endorsement_model->get($endorsement_record->id);
					try {

						$this->_save_installments($policy_record, $endorsement_record);

					} catch (Exception $e) {
						return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
					}

					$ajax_data = [
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
							'html'		=> $this->load->view('endorsements/_cost_calculation_table', ['endorsement_record' => $endorsement_record, 'policy_record' => $policy_record], TRUE)
						]
					];

					return $this->template->json($ajax_data);
				}
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Save Premium Installments
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
		 * @param object $endorsement_record
		 * @return mixed
		 */
		private function _save_installments($policy_record, $endorsement_record)
		{
			$this->load->model('policy_installment_model');

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);
			if($pfs_record->flag_installment === IQB_FLAG_YES )
			{
				// Get Multiple Installments
				$dates = $this->input->post('installment_date') ?? NULL;
				$percents = $this->input->post('percent') ?? NULL;

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
					'dates' 	=> [$policy_record->issued_date],
					'percents' 	=> [100],
				];
			}

			/**
			 * Set Installment Type
			 */
			$installment_data['installment_type'] = _POLICY_INSTALLMENT_type_by_endorsement_type( $endorsement_record->txn_type );

			return $this->policy_installment_model->build($endorsement_record, $installment_data);
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
	 * @param object 	$endorsement_record Endorsement Record
	 * @param array 	$json_extra 	Extra Data to Pass as JSON
	 * @return type
	 */
	private function __render_premium_form($policy_record, $endorsement_record, $json_extra=[])
	{
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


		// Endorsement Form
		try{
			$form_view = _POLICY__partial_view__premium_form($policy_record->portfolio_id);
		}
		catch (Exception $e) {

			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}

		/**
         * Common Views:
         *
         * 	1. Premium Installment Section
         */
		$common_components = '';
        if($pfs_record->flag_installment === IQB_FLAG_YES )
		{
	        $common_components = $this->load->view('endorsements/forms/_form_txn_installments', [
	            'endorsement_record'        => $endorsement_record,
	            'form_elements'     => $premium_goodies['validation_rules']['installments']
	        ], TRUE);
	    }

		// Let's render the form
        $json_data['form'] = $this->load->view($form_view, [
								                'form_elements'         => $premium_goodies['validation_rules'],
								                'portfolio_risks' 		=> $portfolio_risks,
								                'policy_record'         => $policy_record,
								                'endorsement_record'        	=> $endorsement_record,
								                'policy_object' 		=> $policy_object,
								                'tariff_record' 		=> $premium_goodies['tariff_record'],
								                'common_components' 	=> $common_components
								            ], TRUE);





        $json_data = array_merge($json_data, $json_extra);

        // Return HTML
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
				'ENDRSMNT.status' 	=> IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE
			];
		}
		else
		{
			$where = [
				'ENDRSMNT.id' 	=> $key
			];
		}

		$records = $this->endorsement_model->get_many_by($where);

		$data = [
			'records' 	=> $records,
			'type' 		=> $type
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
		$endorsement_record = $this->endorsement_model->get($id);
		if(!$endorsement_record)
		{
			$this->template->render_404();
		}

		// is This Current Transaction?
		if( $endorsement_record->flag_current != IQB_FLAG_ON  )
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
		$this->__check_status_permission($to_status_code, $endorsement_record);


		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($to_status_code, $endorsement_record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->endorsement_model->update_status($endorsement_record, $to_status_code) )
			{
				/**
				 * Updated Transaction & Policy Record
				 */
				$endorsement_record = $this->endorsement_model->get($endorsement_record->id);
				$policy_record = $this->policy_model->get($endorsement_record->policy_id);


				/**
				 * Post Tasks on Transaction Activation
				 * -------------------------------------
				 *
				 * If this is not a General Endorsement Transaction, we also have to update the
				 * 	- policy (from audit_policy field if any data)
				 * 	- object (from audit_object field if any data)
				 * 	- customer (from audit_customer field if any data)
				 * 	- SEND SMS on General Transaction Activation
				 */
				if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_GENERAL && $to_status_code ==IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE )
				{
					$this->_sms_activation($endorsement_record, $policy_record);
				}


				/**
				 * Load Portfolio Specific Helper File
				 */
				try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
					return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
				}

				/**
				 * What to reload/render after success?
				 * -----------------------------------
				 * 	1. RI Approval Triggered from Policy Overview Tab
				 */
				if( $ref == 'tab-policy-overview' )
				{
					/**
					 * Update View
					 */
					$view = 'policies/tabs/_tab_overview';
					$html = $this->load->view($view, ['record' => $policy_record, 'endorsement_record' => $endorsement_record], TRUE);

					$ajax_data = [
						'message' 	=> 'Successfully Updated!',
						'status'  	=> 'success',
						'multipleUpdate' => [
							[
								'box' 		=> '#tab-policy-overview-inner',
								'method' 	=> 'replaceWith',
								'html' 		=> $html
							],
							[
								'box' 		=> '#page-title-policy-code',
								'method' 	=> 'html',
								'html' 		=> $policy_record->code
							]
						]
					];
					return $this->template->json($ajax_data);
				}
				else
				{
					// Replace the Row
					$html = $this->load->view('endorsements/_single_row', ['record' => $endorsement_record, 'policy_record' => $policy_record], TRUE);
					return $this->template->json([
						'message' 	=> 'Successfully Updated!',
						'status'  	=> 'success',
						'multipleUpdate' => [
							[
								'box' 		=> '#_data-row-endorsements-' . $endorsement_record->id,
								'method' 	=> 'replaceWith',
								'html' 		=> $html
							]
						]
					]);
				}
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
				case IQB_POLICY_ENDORSEMENT_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED:
					$permission_name = 'status.to.ri.approved';
					break;

				case IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED:
					$permission_name = 'status.to.vouchered';
					break;

				case IQB_POLICY_ENDORSEMENT_STATUS_INVOICED:
					$permission_name = 'status.to.invoiced';
					break;

				case IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE:
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
		 * @param object $endorsement_record Endorsement Record
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __status_qualifies($to_updown_status, $endorsement_record, $terminate_on_fail = TRUE)
		{
			$__flag_passed = $this->endorsement_model->status_qualifies($endorsement_record->status, $to_updown_status);

			if( $__flag_passed )
			{
				/**
				 * FRESH/RENEWAL Endorsement
				 * 	Draft/Verified are automatically triggered from
				 * 	Policy Status Update Method
				 */
				if( _ENDORSEMENT_is_first($endorsement_record->txn_type) )
				{
					$__flag_passed = !in_array($to_updown_status, [
						IQB_POLICY_ENDORSEMENT_STATUS_DRAFT,
						IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED,
						IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE
					]);
				}
			}


			/**
			 * Premium Must be Updated Before Verifying
			 */
			if(
				$__flag_passed && _ENDORSEMENT_is_premium_computable_by_type($endorsement_record->txn_type)
				&&
				$to_updown_status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED
				&&
				!$endorsement_record->amt_total_premium
			)
			{
				$__flag_passed 		= FALSE;
				$failed_message 	= 'Please Update Policy Premium First!';
			}



			/**
			 * Can not Update Transactional Status Directly using status function
			 */
			if( $__flag_passed )
			{
				$__flag_passed = !in_array($to_updown_status, [
					IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED,
					IQB_POLICY_ENDORSEMENT_STATUS_INVOICED
				]);
			}

			/**
			 * General Endorsement
			 * Activate Status
			 *
			 * !!! If RI-Approval Constraint Required, It should Come from That Status else from Verified
			 */
			if( $__flag_passed && $to_updown_status === IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE && $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_GENERAL )
			{
				if( (int)$endorsement_record->flag_ri_approval === IQB_FLAG_ON )
				{
					$__flag_passed = $endorsement_record->status === IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED;
				}
				else
				{
					$__flag_passed = $endorsement_record->status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED;
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
	//  POLICY Voucher & Invoice
	// --------------------------------------------------------------------

    	/**
         * Send Activation SMS
         * ---------------------
         * Case 1: Fresh/Renewal/Transactional - After making payment, it gets activated automatically
         * Case 2: General Endorsement - After activating
         *
         * @param object $endorsement_record
         * @param object $policy_record
         * @return bool
         */
    	private function _sms_activation($endorsement_record, $policy_record, $invoice_record = NULL)
    	{
    		$customer_name 		= $policy_record->customer_name;
    		$customer_contact 	= $policy_record->customer_contact ? json_decode($policy_record->customer_contact) : NULL;
    		$mobile 			= $customer_contact->mobile ? $customer_contact->mobile : NULL;

    		if( !$mobile )
    		{
    			return FALSE;
    		}

    		$message = "Dear {$customer_name}," . PHP_EOL;

    		if( in_array($endorsement_record->txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_FRESH, IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL]) )
        	{
        		$message .= "Your Policy has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $invoice_record->amount . PHP_EOL .
        					"Expires on : " . $policy_record->end_date . PHP_EOL;
        	}
        	else if( $endorsement_record->txn_type == IQB_POLICY_TXN_TYPE_ET )
        	{
        		$message .= "Your Policy Endorsement has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Amount Paid(Rs): " . $invoice_record->amount . PHP_EOL;
        	}
        	else
        	{
        		$message .= "Your Policy Endorsement has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL;
        	}

        	/**
        	 * Add Signature
        	 */
        	$message .= PHP_EOL . SMS_SIGNATURE;

        	/**
        	 * Let's Fire the SMS
        	 */
        	$this->load->helper('sms');
        	$result = send_sms($mobile, $message);
    	}
}