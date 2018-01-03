<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Transaction Controller
 *
 * We use this controller to work with the policy premium.
 *
 * This controller falls under "Policy" category.
 *
 * @category 	Policy
 */

// --------------------------------------------------------------------

class Policy_txn extends MY_Controller
{
	/**
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'policy_txn/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Policy Transaction';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('policy_txn_model');
		$this->load->model('portfolio_setting_model');
		// $this->load->model('premium_model');
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
	 * List all Policy Transaction Records for supplied Policy
	 *
	 * @return JSON
	 */
	public function index($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_txn', 'explore.transaction') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id 		= (int)$policy_id;
		$policy_record 	= $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		$records 	= $this->policy_txn_model->rows($policy_id);

		$data = [
			'records' 					=> $records,
			'policy_record' 			=> $policy_record,
			'add_url' 					=> 'policy_txn/add_endorsement/' . $policy_id,
			'print_url' 				=> 'policy_txn/print/all/' . $policy_id,
		];
		$html = $this->load->view('policy_txn/_list_widget', $data, TRUE);
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
		if( !$this->dx_auth->is_authorized('policy_txn', 'explore.transaction') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'policy_txn_' . $policy_id : NULL;
		$this->policy_txn_model->clear_cache($cache_var);

		if($policy_id)
		{
			$ajax_data = $this->index($policy_id, TRUE);
			$json_data = [
				'status' => 'success',
				'message' 	=> 'Successfully flushed the cache.',
				'reloadRow' => true,
				'rowId' 	=> '#list-widget-policy_txn',
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
	public function edit_endorsement($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->policy_txn_model->get($id);
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
		is_policy_txn_editable($record->status, $record->flag_current);

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($record->policy_id);

		// Form Submitted? Save the data
		$this->_save_endorsement('edit', $policy_record, $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('policy_txn/forms/_form_endorsement',
			[
				'form_elements' => $this->policy_txn_model->validation_rules,
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
	public function add_endorsement($policy_id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_txn', 'add.transaction') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Can I Add
		 */
		$policy_id = (int)$policy_id;
		if( !$this->_can_add_endorsement($policy_id) )
		{
			return $this->template->json(['status' => 'error', 'title' => 'OOPS!', 'message' => 'You can not add new endorsement as you have unfinished current endorsement.'], 403);
		}

		$record = NULL;

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($policy_id);

		// Form Submitted? Save the data
		$this->_save_endorsement('add', $policy_record, $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('policy_txn/forms/_form_endorsement',
			[
				'form_elements' => $this->policy_txn_model->validation_rules,
				'record' 		=> $record,
				'policy_record' => $this->policy_model->get($policy_id)
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

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
			$current_txn = $this->policy_txn_model->get_current_txn_by_policy($policy_id);

			return $current_txn->status === IQB_POLICY_TXN_STATUS_ACTIVE;
		}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save_endorsement($action, $policy_record, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return $this->template->json(['status' => 'error', 'message' => 'Invalid Action!']);
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done = FALSE;

			/**
			 * Validation Rules - Set according to the endorsement type
			 */
			$txn_type = (int)$this->input->post('txn_type');
			$v_rules_all = $this->policy_txn_model->validation_rules;
			if( $txn_type == IQB_POLICY_TXN_TYPE_ET )
			{
				$v_rules = array_merge($v_rules_all['basic'], $v_rules_all['transaction']);
			}
			else
			{
				$v_rules = $v_rules_all['basic'];
			}

			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
				$data = $this->input->post();
				$data['policy_id'] = $policy_record->id;

				/**
				 * Prepare Data - Based on Type
				 */
				if( $txn_type !== IQB_POLICY_TXN_TYPE_ET )
				{
					// Nullify All the transactional Fields
					$txn_fields = ['amt_total_premium', 'amt_pool_premium', 'amt_commissionable', 'amt_agent_commission', 'amt_stamp_duty', 'amt_vat'];
					foreach($txn_fields as $key)
					{
						$data[$key] = NULL;
					}
				}
				else
				{
					/**
					 * Compute Sum Insured, Agent Commission, VAT
					 */
					$data = $this->_prepare_et_data($policy_record, $data);
				}

        		// Insert or Update?
				if($action === 'add')
				{
					// echo '<pre>'; print_r($data);exit;

					/**
					 * RI-Approval Required?
					 *
					 * If Fresh/Renewal Transaction has RI-Approval Flag set, we must set this
					 * for all the subsequent endorsements regardless of their types
					 */
					$data['flag_ri_approval'] = $this->policy_txn_model->get_flag_ri_approval_by_policy( $policy_record->id );

					$done = $this->policy_txn_model->save_endorsement($data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->policy_txn_model->update($record->id, $data, TRUE);
				}

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
						$return = $this->index($policy_record->id, TRUE);
						return $this->template->json([
							'status' 		=> $status,
							'message' 		=> $message,
							'reloadForm' 	=> false,
							'hideBootbox' 	=> true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#tab-policy-transactions',
								'method' 	=> 'html',
								'html'		=> $return['html']
							]
						]);
					}
					else
					{
						// Get Updated Record
						$record 		= $this->policy_txn_model->get($record->id);
						$policy_record 	= $this->policy_model->get($policy_record->id);
						$html 			= $this->load->view('policy_txn/_single_row', ['record' => $record, 'policy_record' => $policy_record], TRUE);

						return $this->template->json([
							'status' 		=> $status,
							'message' 		=> $message,
							'reloadForm' 	=> false,
							'hideBootbox' 	=> true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_data-row-policy_txn-' . $record->id,
								'method' 	=> 'replaceWith',
								'html'		=> $html
							]
						]);
					}
				}
        	}
        	else
        	{
        		return $this->template->json(['status' => 'error', 'message' => validation_errors()]);
        	}
		}
	}
		/**
		 * Prepare Transactional Endorsement Data
		 *
		 * Compute Sum Insured, Agent Commission, VAT
		 *
		 * @param object $policy_record
		 * @param array $data
		 * @return array
		 */
		private function _prepare_et_data($policy_record, $data)
		{
			/**
			 * Agent Commission
			 */
			$data['amt_agent_commission'] = NULL;
			if( !empty($policy_record->agent_id) && $policy_record->flag_dc ==  IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
			{
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);
				$data['amt_agent_commission'] 	= ( $data['amt_commissionable'] * $pfs_record->agent_commission)/100.00;
			}

			/**
			 * Compute VAT
			 */
			$this->load->helper('account');
			$data['amt_vat'] = ac_compute_tax(IQB_AC_DNT_ID_VAT, $data['amt_total_premium']+ $data['amt_stamp_duty']);

			return $data;
		}

	// --------------------------------------------------------------------

	/**
	 * Delete a Policy Transaction Draft (Non Fresh/Renewal)
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		$id = (int)$id;
		$record = $this->policy_txn_model->get($id);
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
		 * 		delete.draft.transaction
		 */

		// Deletable Status?
		if(
			$record->status !== IQB_POLICY_TXN_STATUS_DRAFT
							||
			!in_array($record->txn_type, [IQB_POLICY_TXN_TYPE_ET, IQB_POLICY_TXN_TYPE_EG])
							||
			!$this->dx_auth->is_authorized('policy_txn', 'delete.draft.transaction')
		)
		{
			$this->dx_auth->deny_access();
		}


		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];

		$done = $this->policy_txn_model->delete($record);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-policy_txn-'.$record->id
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
	 * Re-Build Policy Transaction Premium
	 *
	 * This method is used to edit the transaction table.
	 * This method only applies for Fresh/Renewal Record.
	 *
	 * !!! Important: Fresh/Renewal Only
	 *
	 * @param char $txn_type Transaction Type
	 * @param integer $id Policy ID
	 * @return void
	 */
	public function premium( $txn_type, $policy_id )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_txn', 'edit.draft.transaction') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Transaction Type (Fresh & Renewal Only)
		if( !in_array( $txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL] ) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Transaction Type!'
			], 400);
		}

		// Policy Record
		$policy_id = (int)$policy_id;
		$policy_record = $this->policy_model->get($policy_id);
		if( !$policy_record )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Policy Record!'
			], 400);
		}

		// Current Policy Transaction Record and Has valid Type?
		$txn_record = $this->policy_txn_model->get_current_txn_by_policy($policy_record->id);
		if( !$txn_record || !in_array( $txn_record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL] ) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Current Policy Transaction Record! OR Invalid Record Type!'
			], 400);
		}


		// Record Editable?
		if( !$this->policy_txn_model->is_editable($txn_record->status) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'You can only edit Draft Transaction!'
			], 400);
		}

		/**
		 * Policy Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		// Post? Save Premium
		$this->__save_premium($policy_record, $txn_record);


		// Render Form
		$this->__render_premium_form($policy_record, $txn_record);
	}

	// --------------------------------------------------------------------

		/**
		 * Save/Update Policy Premium
		 *
		 * !!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record 	Policy Record
		 * @param object $txn_record 		Policy Transaction Record
		 * @return mixed
		 */
		private function __save_premium($policy_record, $txn_record)
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
		            $done = __save_premium_AGR_CROP($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
		         * ---------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
		        {
		            $done = __save_premium_AGR_CATTLE($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - POULTRY SUB-PORTFOLIO
		         * -----------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
		        {
		            $done = __save_premium_AGR_POULTRY($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
		         * ----------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
		        {
		            $done = __save_premium_AGR_FISH($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
		         * -------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
		        {
		            $done = __save_premium_AGR_BEE($policy_record, $txn_record);
		        }

				/**
				 * MOTOR PORTFOLIOS
				 * ----------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
				{
					$done = __save_premium_MOTOR( $policy_record, $txn_record );
				}

				/**
		         * FIRE - FIRE
		         * -------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
		        {
		            $done = __save_premium_FIRE_FIRE( $policy_record, $txn_record );
		        }

		        /**
		         * FIRE - HOUSEHOLDER
		         * -------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
		        {
		            $done = __save_premium_FIRE_HHP( $policy_record, $txn_record );
		        }

		        /**
		         * FIRE - LOSS OF PROFIT
		         * ----------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
		        {
		            $done = __save_premium_FIRE_LOP( $policy_record, $txn_record );
		        }

				/**
		         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
		         * --------------------------------------------------
		         */
		        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
		        {
		            $done = __save_premium_MISC_BRG( $policy_record, $txn_record );
		        }

				/**
				 * MARINE PORTFOLIOS
				 * ---------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
				{
					$done = __save_premium_MARINE( $policy_record, $txn_record );
				}

				/**
		         * ENGINEERING - BOILER EXPLOSION
		         * ------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
		        {
		            $done = __save_premium_ENG_BL( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR ALL RISK
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
		        {
		            $done = __save_premium_ENG_CAR( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
		         * ------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
		        {
		            $done = __save_premium_ENG_CPM( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
		        {
		            $done = __save_premium_ENG_EEI( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - ERECTION ALL RISKS
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
		        {
					$done = __save_premium_ENG_EAR( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - MACHINE BREAKDOWN
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
		        {
		            $done = __save_premium_ENG_MB( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - BANKER'S BLANKET(BB)
		         * -------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
		        {
		            $done = __save_premium_MISC_BB( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
		        {
		            $done = __save_premium_MISC_GPA( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
		        {
		            $done = __save_premium_MISC_PA( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
		        {
		            $done = __save_premium_MISC_PL( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN TRANSIT
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
		        {
		            $done = __save_premium_MISC_CT( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN SAFE
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
		        {
		            $done = __save_premium_MISC_CS( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - CASH IN COUNTER
		         * -------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
		        {
		            $done = __save_premium_MISC_CC( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
		        {
		            $done = __save_premium_MISC_EPA( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
		         * --------------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
		        {
		            $done = __save_premium_MISC_TMI( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
		        {
		            $done = __save_premium_MISC_FG( $policy_record, $txn_record );
		        }

		        /**
		         * MISCELLANEOUS - HEALTH INSURANCE (HI)
		         * ----------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
		        {
		            $done = __save_premium_MISC_HI( $policy_record, $txn_record );
		        }

				else
				{
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Policy_txn::__save_premium() - No method defined for supplied portfolio!'
					], 400);
				}


				if($done)
				{

					/**
					 * Build and Update Installments
					 */
					// Get Updated TXN Record
					$txn_record 		= $this->policy_txn_model->get($txn_record->id);
					try {

						$this->_save_installments($policy_record, $txn_record);

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
							'html'		=> $this->load->view('policy_txn/_cost_calculation_table', ['txn_record' => $txn_record, 'policy_record' => $policy_record], TRUE)
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
		 * @param object $txn_record
		 * @return mixed
		 */
		private function _save_installments($policy_record, $txn_record)
		{
			$this->load->model('policy_txn_installment_model');

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
					throw new Exception("Exception [Controller:Policy_txn][Method: _save_installments()]: No installment data found. <br/>You integrate and supply installment information on premium for of this PORTFOLIO.");
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

			return $this->policy_txn_installment_model->build($txn_record, $installment_data);
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
	 * @param object 	$txn_record Policy Transaction Record
	 * @param array 	$json_extra 	Extra Data to Pass as JSON
	 * @return type
	 */
	private function __render_premium_form($policy_record, $txn_record, $json_extra=[])
	{
		/**
		 *  Let's Load The Policy Transaction Form For this Record
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


		// Policy Transaction Form
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
	        $common_components = $this->load->view('policy_txn/forms/_form_txn_installments', [
	            'txn_record'        => $txn_record,
	            'form_elements'     => $premium_goodies['validation_rules']['installments']
	        ], TRUE);
	    }

		// Let's render the form
        $json_data['form'] = $this->load->view($form_view, [
								                'form_elements'         => $premium_goodies['validation_rules'],
								                'portfolio_risks' 		=> $portfolio_risks,
								                'policy_record'         => $policy_record,
								                'txn_record'        	=> $txn_record,
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
		 * Get Policy Policy Transaction Goodies
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
					'message' 	=> 'Policy_txn::__premium_goodies() - No data found for supplied portfolio!'
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
		if( !$this->dx_auth->is_authorized('policy_txn', 'print.endorsement') )
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
				'PTXN.status' 	=> IQB_POLICY_TXN_STATUS_ACTIVE
			];
		}
		else
		{
			$where = [
				'PTXN.id' 	=> $key
			];
		}

		$records = $this->policy_txn_model->get_many_by($where);

		$data = [
			'records' 	=> $records,
			'type' 		=> $type
		];


		/**
		 * Render Print View
		 */
		try {

			_POLICY__endorsement_pdf($data);
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
	public function status($id, $to_status_code, $ref='tab-policy-transactions')
	{
		$id = (int)$id;
		$txn_record = $this->policy_txn_model->get($id);
		if(!$txn_record)
		{
			$this->template->render_404();
		}

		// is This Current Transaction?
		if( $txn_record->flag_current != IQB_FLAG_ON  )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Current Policy Transaction Record!'
			], 400);
		}

		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($to_status_code, $txn_record);


		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($to_status_code, $txn_record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->policy_txn_model->update_status($txn_record, $to_status_code) )
			{
				/**
				 * Updated Transaction & Policy Record
				 */
				$txn_record = $this->policy_txn_model->get($txn_record->id);
				$policy_record = $this->policy_model->get($txn_record->policy_id);


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
				if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_EG && $to_status_code ==IQB_POLICY_TXN_STATUS_ACTIVE )
				{
					$this->_sms_activation($txn_record, $policy_record);
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
					$html = $this->load->view($view, ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);

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
					$html = $this->load->view('policy_txn/_single_row', ['record' => $txn_record, 'policy_record' => $policy_record], TRUE);
					return $this->template->json([
						'message' 	=> 'Successfully Updated!',
						'status'  	=> 'success',
						'multipleUpdate' => [
							[
								'box' 		=> '#_data-row-policy_txn-' . $txn_record->id,
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
			$status_keys = array_keys(get_policy_txn_status_dropdown(FALSE));

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
				case IQB_POLICY_TXN_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_POLICY_TXN_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_POLICY_TXN_STATUS_RI_APPROVED:
					$permission_name = 'status.to.ri.approved';
					break;

				case IQB_POLICY_TXN_STATUS_VOUCHERED:
					$permission_name = 'status.to.vouchered';
					break;

				case IQB_POLICY_TXN_STATUS_INVOICED:
					$permission_name = 'status.to.invoiced';
					break;

				case IQB_POLICY_TXN_STATUS_ACTIVE:
					$permission_name = 'status.to.active';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('policy_txn', $permission_name) )
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
		 * @param object $txn_record Policy Transaction Record
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __status_qualifies($to_updown_status, $txn_record, $terminate_on_fail = TRUE)
		{
			$__flag_passed = $this->policy_txn_model->status_qualifies($txn_record->status, $to_updown_status);

			if( $__flag_passed )
			{
				/**
				 * FRESH/RENEWAL Policy Transaction
				 * 	Draft/Verified are automatically triggered from
				 * 	Policy Status Update Method
				 */
				if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_FRESH  || $txn_record->txn_type == IQB_POLICY_TXN_TYPE_RENEWAL )
				{
					$__flag_passed = !in_array($to_updown_status, [
						IQB_POLICY_TXN_STATUS_DRAFT,
						IQB_POLICY_TXN_STATUS_VERIFIED,
						IQB_POLICY_TXN_STATUS_ACTIVE
					]);
				}
			}

			/**
			 * Can not Update Transactional Status Directly using status function
			 */
			if( $__flag_passed )
			{
				$__flag_passed = !in_array($to_updown_status, [
					IQB_POLICY_TXN_STATUS_VOUCHERED,
					IQB_POLICY_TXN_STATUS_INVOICED
				]);
			}

			/**
			 * General Endorsement
			 * Activate Status
			 *
			 * !!! If RI-Approval Constraint Required, It should Come from That Status else from Verified
			 */
			if( $__flag_passed && $to_updown_status === IQB_POLICY_TXN_STATUS_ACTIVE && $txn_record->txn_type == IQB_POLICY_TXN_TYPE_EG )
			{
				if( (int)$txn_record->flag_ri_approval === IQB_FLAG_ON )
				{
					$__flag_passed = $txn_record->status === IQB_POLICY_TXN_STATUS_RI_APPROVED;
				}
				else
				{
					$__flag_passed = $txn_record->status === IQB_POLICY_TXN_STATUS_VERIFIED;
				}
			}


			if( !$__flag_passed && $terminate_on_fail )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Invalid Status Transaction',
					'message' 	=> 'You can not swith to the state from this state of transaction.'
				], 400);
			}

			return $__flag_passed;

		}

	// --------------------- END: STATUS UPGRADE/DOWNGRADE --------------------


	// --------------------------------------------------------------------
	//  POLICY Voucher & Invoice
	// --------------------------------------------------------------------

	/**
	 * Generate Voucher
	 *
	 * @param integer $id Policy TXN ID
	 * @return mixed
	 */
	public function voucher($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_txn', 'generate.policy.voucher') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		$id 		= (int)$id;
		$txn_record = $this->policy_txn_model->get( $id );
		if(!$txn_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($txn_record->policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */

		$authorized_status = ( $txn_record->status === IQB_POLICY_TXN_STATUS_RI_APPROVED )
								||
							 ( $txn_record->status === IQB_POLICY_TXN_STATUS_VERIFIED && (int)$txn_record->flag_ri_approval === IQB_FLAG_OFF );
		if( !$authorized_status )
		{
			return $this->template->json([
				'title' 	=> 'Unauthorized Transaction Status!',
				'status' 	=> 'error',
				'message' 	=> 'This transaction does not have authorized status to perform this action.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Load voucher models
		 */
		$this->load->model('ac_voucher_model');
		$this->load->model('rel_policy_txn__voucher_model');

		/**
		 * Check if Voucher already generated for this Transaction
		 */
		if( $this->rel_policy_txn__voucher_model->voucher_exists($txn_record->id))
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'Voucher already exists for this Transaction/Endorsement.'
			], 404);
		}


		// --------------------------------------------------------------------

		/**
		 * Fiscal Year Record
		 */
		$fy_record = $this->fiscal_year_model->get($policy_record->fiscal_yr_id);

		// --------------------------------------------------------------------

		/**
		 * Portfoliio Record
		 */
		$this->load->model('portfolio_model');
		$this->load->model('portfolio_setting_model');
		$portfolio_record = $this->portfolio_model->find($policy_record->portfolio_id);

		// --------------------------------------------------------------------

		/**
		 * Portfolio Setting Record
		 */
		$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);
		if( !$pfs_record )
		{
			return $this->template->json([
				'title' 	=> 'Portfolio Setting Missing!',
				'status' 	=> 'error',
				'message' 	=> "Please add portfolio settings for fiscal year({$fy_record->code_np}) for portfolio ({$portfolio_record->name_en})"
			], 404);
		}

		// --------------------------------------------------------------------


		/**
		 * Let's Build Policy Voucher
		 */
		$narration = 'POLICY VOUCHER - POLICY CODE : ' . $policy_record->code;
		$voucher_data = [
            'voucher_date'      => date('Y-m-d'),
            'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_PRI,
            'narration'         => $narration,
            'flag_internal'     => IQB_FLAG_ON
        ];

		// --------------------------------------------------------------------


        /**
         * Voucher Amount Computation
         */
        $gross_premium_amount 		= $txn_record->amt_total_premium;
        $stamp_income_amount 		= $txn_record->amt_stamp_duty;
        $vat_payable_amount 		= $txn_record->amt_vat;

        $beema_samiti_service_charge_amount 		= ($gross_premium_amount * $pfs_record->bs_service_charge) / 100.00;
        $total_to_receive_from_insured_party_amount = $gross_premium_amount + $stamp_income_amount + $vat_payable_amount;
        $agent_commission_amount 					= $txn_record->amt_agent_commission ?? NULL;

		// --------------------------------------------------------------------

        /**
         * Debit Rows
         */
        $dr_rows = [

        	/**
        	 * Accounts
        	 */
        	 'accounts' => 	[
	        	// Insured Party
	        	IQB_AC_ACCOUNT_ID_INSURED_PARTY,

	        	// Expense - Beema Samiti Service Charge
	        	IQB_AC_ACCOUNT_ID_EXPENSE_BS_SERVICE_CHARGE
	        ],

	        /**
	         * Party Types
	         */
	        'party_types' => [
	        	// Insured Party -- Customer
	        	IQB_AC_PARTY_TYPE_CUSTOMER,

	        	// Beema Samiti Service Charge -- Company
	        	IQB_AC_PARTY_TYPE_COMPANY,
	        ],

	        /**
	         * Party IDs
	         */
	        'parties' => [

	        	// Insured Party -- Customr ID
	        	$policy_record->customer_id,

	        	// Beema Samiti Service Charge -- Beema Samiti ID
	        	IQB_COMPANY_ID_BEEMA_SAMITI
	        ],

	        /**
	         * Amounts
	         */
	        'amounts' => [

	        	// Insured Party -- Amount Received From Insured Party
	        	$total_to_receive_from_insured_party_amount,

	        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
	        	$beema_samiti_service_charge_amount
	        ]

        ];

		// --------------------------------------------------------------------

        /**
         * Check if portfolio has "Direct Premium Income Account ID"
         */
        if( !$portfolio_record->account_id_dpi )
        {
			return $this->template->json([
				'title' 	=> 'Direct Premium Income Account Missing!',
				'status' 	=> 'error',
				'message' 	=> "Please add portfolio 'Direct Premium Income Account' for portfolio ({$portfolio_record->name_en}) from Master Setup > Portfolio"
			], 404);
        }

        /**
         * Credit Rows
         */
        $cr_rows = [

        	/**
        	 * Accounts
        	 */
        	 'accounts' => 	[
	        	// Vat Payable
	        	IQB_AC_ACCOUNT_ID_VAT_PAYABLE,

	        	// Stamp Income
	        	IQB_AC_ACCOUNT_ID_STAMP_INCOME,

	        	// Direct Premium Income Portfolio Wise
	        	$portfolio_record->account_id_dpi,

	        	// Liability - Service fee Beema Samiti
	        	IQB_AC_ACCOUNT_ID_LIABILITY_BS_SERVICE_CHARGE
	        ],

	        /**
	         * Party Types
	         */
	        'party_types' => [
	        	// Vat Payable -- NULL
	        	NULL,

	        	// Stamp Income -- NULL
	        	NULL,

	        	// Direct Premium Income -- NULL
	        	NULL,

	        	// Beema Samiti Service Charge -- Company
	        	IQB_AC_PARTY_TYPE_COMPANY
	        ],

	        /**
	         * Party IDs
	         */
	        'parties' => [

	        	// Vat Payable -- NULL
	        	NULL,

	        	// Stamp Income -- NULL
	        	NULL,

	        	// Direct Premium Income -- NULL
	        	NULL,

	        	// Beema Samiti Service Charge -- Beema Samiti ID
	        	IQB_COMPANY_ID_BEEMA_SAMITI
	        ],

	        /**
	         * Amounts
	         */
	        'amounts' => [

	        	// Vat Payable -- Vat Amount
	        	$vat_payable_amount,

	        	// Stamp Income -- Stamp Income Amount
	        	$stamp_income_amount,

	        	// Direct Premium Income -- Gross Premium Amount
	        	$gross_premium_amount,

	        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
	        	$beema_samiti_service_charge_amount
	        ]

        ];

		// --------------------------------------------------------------------

        /**
         * Additional Debit/Credit Rows if Agent Commission Apply?
         *
         * NOTE: You must have $agent_commission_amount (NOT NULL or Non Zero Value)
         */
        if( $agent_commission_amount &&  $policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION && $policy_record->agent_id )
        {
        	// Agency Commission
        	$dr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION;

        	// Agency Commission -- Agent
        	$dr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;

        	// Agency Commission -- Agent ID
        	$dr_rows['parties'][] = $policy_record->agent_id;

        	// Agency Commission -- Agent Commission Amount
        	$dr_rows['amounts'][] = $agent_commission_amount;



        	// Agent TDS, Agent Commission Payable
        	$cr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_TDS_AGENCY_COMMISSION;
        	$cr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION_PAYABLE;

        	// Agent TDS -- Agent, Agent Commission Payable -- Agent
        	$cr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;
        	$cr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;

        	// Agent TDS -- Agent ID, Agent Commission Payable -- Agent ID
        	$cr_rows['parties'][] = $policy_record->agent_id;
        	$cr_rows['parties'][] = $policy_record->agent_id;

        	// Agent TDS -- TDS Amount, Agent Commission Payable -- Agent Payable Amount
        	$this->load->model('ac_duties_and_tax_model');
        	$agent_tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_AC, $agent_commission_amount);
        	$agent_commission_payable_amount = $agent_commission_amount - $agent_tds_amount;
        	$cr_rows['amounts'][] = $agent_tds_amount;
        	$cr_rows['amounts'][] = $agent_commission_payable_amount;
        }

		// --------------------------------------------------------------------

        /**
         * Format Data
         */
        $voucher_data['account_id']['dr'] 	= $dr_rows['accounts'];
        $voucher_data['party_type']['dr'] 	= $dr_rows['party_types'];
        $voucher_data['party_id']['dr'] 	= $dr_rows['parties'];
        $voucher_data['amount']['dr'] 		= $dr_rows['amounts'];

        $voucher_data['account_id']['cr'] 	= $cr_rows['accounts'];
        $voucher_data['party_type']['cr'] 	= $cr_rows['party_types'];
        $voucher_data['party_id']['cr'] 	= $cr_rows['parties'];
        $voucher_data['amount']['cr'] 		= $cr_rows['amounts'];


		// --------------------------------------------------------------------

        /**
		 * Save Voucher and Its Relation with Policy
		 */

		try {

			/**
			 * Task 1: Save Voucher and Generate Voucher Code
			 */
			$voucher_id = $this->ac_voucher_model->add($voucher_data, $policy_record->id);

		} catch (Exception $e) {

			return $this->template->json([
				'title' 	=> 'Exception Occured!',
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			]);
		}

		$flag_exception = FALSE;
		$message = '';


		/**
		 * --------------------------------------------------------------------
		 * Post Voucher Add Tasks
		 *
		 * NOTE
		 * 		We perform post voucher add tasks which are mainly to insert
		 * 		voucher internal relation with policy txn record and  update
		 * 		policy status.
		 *
		 * 		Please note that, if any of transaction fails or exception
		 * 		happens, we rollback and disable voucher. (We can not delete
		 * 		voucher as we need to maintain sequential order for audit trail)
		 * --------------------------------------------------------------------
		 */


		/**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            // $this->db->trans_start();
            $this->db->trans_begin();


                // --------------------------------------------------------------------

            	/**
				 * Task 2: Add Voucher-Policy Transaction Relation
				 */

				try {

					$relation_data = [
						'policy_txn_id' => $txn_record->id,
						'voucher_id' 	=> $voucher_id
					];
					$this->rel_policy_txn__voucher_model->add($relation_data);

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

                // --------------------------------------------------------------------

				/**
				 * Task 4: Update Transaction Status to "Vouchered", Clean Cache
				 */
				if( !$flag_exception )
				{
					try{

						$this->policy_txn_model->update_status($txn_record, IQB_POLICY_TXN_STATUS_VOUCHERED);

					} catch (Exception $e) {

						$flag_exception = TRUE;
						$message = $e->getMessage();
					}
				}

                // --------------------------------------------------------------------

				/**
				 * Task 5: Generate Policy Number
				 *
				 * NOTE: Policy TXN must be fresh or Renewal
				 */
				if( !$flag_exception && in_array($txn_record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
				{
					try{

						$policy_code = $this->policy_model->generate_policy_number( $policy_record );
						if($policy_code)
						{
							$policy_record->code = $policy_code;
						}

					} catch (Exception $e) {

						$flag_exception = TRUE;
						$message = $e->getMessage();
					}
				}

                // --------------------------------------------------------------------

			/**
             * Complete transactions or Rollback
             */
			if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
			{
		        $this->db->trans_rollback();

		        /**
            	 * Set Voucher Flag Complete to OFF
            	 */
            	$this->ac_voucher_model->disable_voucher($voucher_id);

            	return $this->template->json([
					'title' 	=> 'Something went wrong!',
					'status' 	=> 'error',
					'message' 	=> $message ? $message : 'Could not perform save voucher relation or update policy status'
				]);
			}
			else
			{
			        $this->db->trans_commit();
			}

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


		// --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}

		/**
		 * Reload the Policy Overview Tab, Update Transaction Row (Replace)
		 */
		$txn_record->status = IQB_POLICY_TXN_STATUS_VOUCHERED;
		$html_tab_ovrview 	= $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);
		$html_txn_row 		= $this->load->view('policy_txn/_single_row', ['policy_record' => $policy_record, 'record' => $txn_record], TRUE);
		$ajax_data = [
			'message' 	=> 'Successfully Updated!',
			'status'  	=> 'success',
			'multipleUpdate' => [
				[
					'box' 		=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'html' 		=> $html_tab_ovrview
				],
				[
					'box' 		=> '#_data-row-policy_txn-' . $txn_record->id,
					'method' 	=> 'replaceWith',
					'html' 		=> $html_txn_row
				]
			]
		];
		return $this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate Invoice
	 *
	 * @param integer $id Policy TXN ID
	 * @param integer $voucher_id Voucher ID
	 * @return mixed
	 */
	public function invoice($id, $voucher_id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_txn', 'generate.policy.invoice') )
		{
			$this->dx_auth->deny_access();
		}

		// --------------------------------------------------------------------

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		$id 		= (int)$id;
		$voucher_id = (int)$voucher_id;
		$txn_record = $this->policy_txn_model->get( $id );
		if(!$txn_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Get Voucher Record By Policy Transaction Relation
		 */
		$this->load->model('ac_voucher_model');
		$voucher_record = $this->ac_voucher_model->get_voucher_by_policy_txn_relation($txn_record->id, $voucher_id);
		if(!$voucher_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Check if Invoice already generated for this Voucher
		 */
		$this->load->model('ac_invoice_model');
		if( $this->ac_invoice_model->invoice_exists($voucher_id))
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'Invoice already exists for this Voucher.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($txn_record->policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */
		if($txn_record->status !== IQB_POLICY_TXN_STATUS_VOUCHERED || $voucher_record->flag_invoiced != IQB_FLAG_OFF )
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'You can not perform this action.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Let's Build Policy Invoice
		 */

		/**
         * Voucher Amount Computation
         */
        $gross_premium_amount 		= $txn_record->amt_total_premium;
        $stamp_income_amount 		= $txn_record->amt_stamp_duty;
        $vat_payable_amount 		= $txn_record->amt_vat;

        $total_to_receive_from_insured_party_amount = $gross_premium_amount + $stamp_income_amount + $vat_payable_amount;

		$invoice_data = [
			'customer_id' 		=> $policy_record->customer_id,
            'invoice_date'      => date('Y-m-d'),
            'voucher_id'   		=> $voucher_id,
            'amount' 			=> $total_to_receive_from_insured_party_amount
        ];

		// --------------------------------------------------------------------

        /**
         * Invoice Details
         */
        $invoice_details_data = [
        	[
	        	'description' 	=> "Policy Premium Amount (Policy Code - {$policy_record->code})",
	        	'amount'		=> $gross_premium_amount
	        ],
	        [
	        	'description' 	=> "Stamp Duty",
	        	'amount'		=> $stamp_income_amount
	        ],
	        [
	        	'description' 	=> "VAT",
	        	'amount'		=> $vat_payable_amount
	        ]
        ];


		// --------------------------------------------------------------------

		/**
		 * Save Invoice
		 */
		try {

			/**
			 * Task 1: Save Invoice and Generate Invoice Code
			 */
			$invoice_id = $this->ac_invoice_model->add($invoice_data, $invoice_details_data, $policy_record->id);

		} catch (Exception $e) {

			return $this->template->json([
				'title' 	=> 'Exception Occured!',
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			]);
		}

		$flag_exception = FALSE;
		$message = '';


		/**
		 * --------------------------------------------------------------------
		 * Post Invoice Add Tasks
		 *
		 * NOTE
		 * 		We perform post voucher add tasks which are mainly to update
		 * 		voucher internal relation with policy txn record and  update
		 * 		policy transaction status.
		 *
		 * 		Please note that, if any of transaction fails or exception
		 * 		happens, we rollback and disable voucher. (We can not delete
		 * 		voucher as we need to maintain sequential order for audit trail)
		 * --------------------------------------------------------------------
		 */


		/**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            // $this->db->trans_start();
            $this->db->trans_begin();


                // --------------------------------------------------------------------

				/**
				 * Task 2: Update Transaction Status to "Invoiced", Clean Cache
				 */
				try{

					$this->policy_txn_model->update_status($txn_record, IQB_POLICY_TXN_STATUS_INVOICED);

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

                // --------------------------------------------------------------------

                /**
                 * Task 3: Update Relation Table Flag - "flag_invoiced"
                 */
                if( !$flag_exception )
				{
					$this->load->model('rel_policy_txn__voucher_model');
					$rel_base_where = [
						'policy_txn_id' => $txn_record->id,
						'voucher_id' 	=> $voucher_id
					];
	                $this->rel_policy_txn__voucher_model->update_by($rel_base_where, [
	                	'flag_invoiced' => IQB_FLAG_ON
	            	]);

	            	// Clear Voucher Cache for This Policy
	            	$cache_var = 'ac_voucher_list_by_policy_' . $policy_record->id;
					$this->ac_voucher_model->clear_cache($cache_var);
            	}

                // --------------------------------------------------------------------


				/**
				 * Task 4: Save Invoice PDF (Original)
				 */
				try{

					$invoice_data = [
						'record' 	=> $this->ac_invoice_model->get($invoice_id),
						'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($invoice_id)
					];
					_INVOICE__pdf($invoice_data, 'save');

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

			/**
             * Complete transactions or Rollback
             */
			if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
			{
		        $this->db->trans_rollback();

		        /**
            	 * Set Invoice Flag Complete to OFF
            	 */
            	$this->ac_invoice_model->disable_invoice($invoice_id);

            	return $this->template->json([
					'title' 	=> 'Something went wrong!',
					'status' 	=> 'error',
					'message' 	=> $message ? $message : 'Could not update policy transaction status or voucher relation flag'
				]);
			}
			else
			{
			        $this->db->trans_commit();
			}

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


		// --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


		/**
		 * Reload the Policy Overview Tab, Update Transaction Row (Replace)
		 */
		$txn_record->status = IQB_POLICY_TXN_STATUS_INVOICED;
		$html_tab_ovrview 	= $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);

		$voucher_record->flag_invoiced = IQB_FLAG_ON;
		$html_voucher_row 	= $this->load->view('accounting/vouchers/_single_row', ['record' => $voucher_record], TRUE);

		$ajax_data = [
			'message' 	=> 'Successfully Updated!',
			'status'  	=> 'success',
			'multipleUpdate' => [
				[
					'box' 		=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'html' 		=> $html_tab_ovrview
				],
				[
					'box' 		=> '#_data-row-voucher-' . $voucher_id,
					'method' 	=> 'replaceWith',
					'html' 		=> $html_voucher_row
				]
			]
		];
		return $this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	public function payment($id, $invoice_id)
    {
        /**
         * Check Permissions
         */
        if( !$this->dx_auth->is_authorized('policy_txn', 'make.policy.payment') )
        {
            $this->dx_auth->deny_access();
        }

        /**
         * Get the Policy Fresh/Renewal Txn Record
         */
        $id         = (int)$id;
        $invoice_id = (int)$invoice_id;
        $txn_record = $this->policy_txn_model->get( $id );
        if(!$txn_record)
        {
            $this->template->render_404();
        }

        // --------------------------------------------------------------------

        /**
         * Policy Record
         */
        $policy_record = $this->policy_model->get($txn_record->policy_id);
        if(!$policy_record)
        {
            $this->template->render_404();
        }

        // --------------------------------------------------------------------

        /**
         * Record Status Authorized to Make Payment?
         */
        if($txn_record->status !== IQB_POLICY_TXN_STATUS_INVOICED)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You can not perform this action.'
            ], 404);
        }

        // --------------------------------------------------------------------

        /**
         * Invoice Record? Already Paid?
         */
        $this->load->model('ac_invoice_model');
        $this->load->model('ac_invoice_detail_model');
        $invoice_record = $this->ac_invoice_model->get($invoice_id);
        if(!$invoice_record || $invoice_record->flag_paid == IQB_FLAG_ON)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You have already made payment for this Invoice.'
            ], 404);
        }


        // --------------------------------------------------------------------

        /**
         * Load voucher models
         */
        $this->load->model('ac_voucher_model');
        $this->load->model('rel_policy_txn__voucher_model');

        // --------------------------------------------------------------------

        /**
         * Render Payment Form
         */
        if( !$this->input->post() )
		{
			$invoice_data = [
				'record' 	=> $invoice_record,
				'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($invoice_record->id)
			];
			return $this->_payment_form($invoice_data);
		}
		else
		{
			$v_rules = $this->_payment_rules();
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$payment_data = $this->input->post();
        	}
        	else
        	{
        		return $this->template->json([
	                'title'     => 'Validation Failed!',
	                'status'    => 'error',
	                'message'   => validation_errors()
	            ], 404);
        	}
		}




        // --------------------------------------------------------------------

        /**
         * Fiscal Year Record
         */
        $fy_record = $this->fiscal_year_model->get($policy_record->fiscal_yr_id);

        // --------------------------------------------------------------------

        /**
         * Portfoliio Record
         */
        $this->load->model('portfolio_model');
        $this->load->model('portfolio_setting_model');
        $portfolio_record = $this->portfolio_model->find($policy_record->portfolio_id);

        // --------------------------------------------------------------------


        /**
         * Let's Build Policy Voucher
         */
        $narration = "Receipt against Policy ({$policy_record->code}) Invoice ({$invoice_record->invoice_code})";
        $narration .= $payment_data['narration'] ? PHP_EOL . $payment_data['narration'] : '';

        $voucher_data = [
            'voucher_date'      => date('Y-m-d'),
            'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_RCPT,
            'narration'         => $narration,
            'flag_internal'     => IQB_FLAG_ON
        ];

        // --------------------------------------------------------------------

        /**
         * Debit Rows
         */
        $dr_rows = [

            /**
             * Accounts
             */
             'accounts' =>  [
                // Collection
                IQB_AC_ACCOUNT_ID_COLLECTION
            ],

            /**
             * Party Types
             */
            'party_types' => [
                NULL
            ],

            /**
             * Party IDs
             */
            'parties' => [

                NULL
            ],

            /**
             * Amounts
             */
            'amounts' => [

                $invoice_record->amount
            ]

        ];

        // --------------------------------------------------------------------

        /**
         * Credit Rows
         */
        $cr_rows = [

            /**
             * Accounts
             */
             'accounts' =>  [
                // Insured Party
                IQB_AC_ACCOUNT_ID_INSURED_PARTY,
            ],

            /**
             * Party Types
             */
            'party_types' => [
                // Insured Party -- Customer
                IQB_AC_PARTY_TYPE_CUSTOMER
            ],

            /**
             * Party IDs
             */
            'parties' => [

                // Insured Party -- Customr ID
                $policy_record->customer_id
            ],

            /**
             * Amounts
             */
            'amounts' => [

                $invoice_record->amount
            ]

        ];

        // --------------------------------------------------------------------

        /**
         * Format Data
         */
        $voucher_data['account_id']['dr']   = $dr_rows['accounts'];
        $voucher_data['party_type']['dr']   = $dr_rows['party_types'];
        $voucher_data['party_id']['dr']     = $dr_rows['parties'];
        $voucher_data['amount']['dr']       = $dr_rows['amounts'];

        $voucher_data['account_id']['cr']   = $cr_rows['accounts'];
        $voucher_data['party_type']['cr']   = $cr_rows['party_types'];
        $voucher_data['party_id']['cr']     = $cr_rows['parties'];
        $voucher_data['amount']['cr']       = $cr_rows['amounts'];


        // --------------------------------------------------------------------

        /**
         * Save Voucher and Its Relation with Policy
         */

        try {
        	/**
             * Task 1: Save Voucher and Generate Voucher Code
             */
            $voucher_id = $this->ac_voucher_model->add($voucher_data, $policy_record->id);

        } catch (Exception $e) {

            return $this->template->json([
                'title'     => 'Exception Occured!',
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]);
        }

        $flag_exception = FALSE;
        $message = '';


        /**
         * --------------------------------------------------------------------
         * Post Voucher Add Tasks
         *
         * NOTE
         *      We perform post voucher add tasks which are mainly to insert
         *      voucher internal relation with policy txn record and  update
         *      policy status.
         *
         *      Please note that, if any of transaction fails or exception
         *      happens, we rollback and disable voucher. (We can not delete
         *      voucher as we need to maintain sequential order for audit trail)
         * --------------------------------------------------------------------
         */


        /**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            $this->db->trans_begin();


                // --------------------------------------------------------------------

                /**
                 * Task 2: Add Voucher-Policy Transaction Relation
                 */

                try {

                    $relation_data = [
                        'policy_txn_id' => $txn_record->id,
                        'voucher_id'    => $voucher_id,
                        'flag_invoiced' => IQB_FLAG_NOT_REQUIRED
                    ];
                    $this->rel_policy_txn__voucher_model->add($relation_data);

                } catch (Exception $e) {

                    $flag_exception = TRUE;
                    $message = $e->getMessage();
                }

                // --------------------------------------------------------------------

                /**
                 * Task 4:
                 * 		Update Invoice Paid Flat to "ON"
                 *      Update Policy Status to "Active" (if Fresh or Renewal )
                 *      Update Transaction Status to "Active", Clean Cache, (Commit endorsement if ET or EG)
                 */
                if( !$flag_exception )
                {
                    try{

                    	$this->ac_invoice_model->update_flag($invoice_record->id, 'flag_paid', IQB_FLAG_ON);
                        $this->policy_txn_model->update_status($txn_record, IQB_POLICY_TXN_STATUS_ACTIVE);

                    } catch (Exception $e) {

                        $flag_exception = TRUE;
                        $message = $e->getMessage();
                    }
                }

                // --------------------------------------------------------------------

                /**
                 * Task 5:
                 *      - Save Receipt
                 *      - Generate Receipt PDF (Original)
                 */
                if( !$flag_exception )
                {

                	$receipt_data = [
                		'invoice_id' 		=> $invoice_record->id,
                		'customer_id' 		=> $policy_record->customer_id,
                		'adjustment_amount' => $payment_data['adjustment_amount'] ? $payment_data['adjustment_amount'] : NULL,
                		'amount' 			=> $invoice_record->amount,
                		'received_in'		=> $payment_data['received_in'],
                		'received_in_date'	=> $payment_data['received_in_date'] ? $payment_data['received_in_date'] : NULL,
                		'received_in_ref' 	=> $payment_data['received_in_ref'] ? $payment_data['received_in_ref'] : NULL,
                	];
                	$this->load->model('ac_receipt_model');
                    try{

                        if( $this->ac_receipt_model->add($receipt_data) )
                        {
                        	// Save Receipt PDF
                        	$receipt_data = [
								'record' 			=> $this->ac_receipt_model->find_by(['invoice_id' => $invoice_record->id]),
								'invoice_record' 	=> $invoice_record
							];

							_RECEIPT__pdf($receipt_data, 'save');
                        }

                    } catch (Exception $e) {

                        $flag_exception = TRUE;
                        $message = $e->getMessage();
                    }
                }

                // --------------------------------------------------------------------



            /**
             * Complete transactions or Rollback
             */
            if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();

                /**
                 * Set Voucher Flag Complete to OFF
                 */
                $this->ac_voucher_model->disable_voucher($voucher_id);

                return $this->template->json([
                    'title'     => 'Something went wrong!',
                    'status'    => 'error',
                    'message'   => $message ? $message : 'Could not perform save voucher relation or update policy status'
                ]);
            }
            else
            {
                    $this->db->trans_commit();


                    /**
                     * Post Commit Tasks
                     * -----------------
                     *
                     * 1. Clear Cache
                     * 2. Send SMS
                     */
                    $cache_var = 'ac_invoice_list_by_policy_'.$policy_record->id;
                    $this->ac_invoice_model->clear_cache($cache_var);

                    // Send SMS
                    $this->_sms_activation($txn_record, $policy_record, $invoice_record);
            }

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


        // --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


        /**
         * Reload the Policy Overview Tab, Update Transaction Row (Replace)
         */
        $txn_record->status         = IQB_POLICY_TXN_STATUS_ACTIVE;
        $policy_record->status      = IQB_POLICY_STATUS_ACTIVE;
        $invoice_record->flag_paid  = IQB_FLAG_ON;
        $html_tab_ovrview           = $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);
        $html_invoice_row       	= $this->load->view('accounting/invoices/_single_row', ['record' => $invoice_record], TRUE);
        $ajax_data = [
            'message'   => 'Successfully Updated!',
            'status'    => 'success',
            'hideBootbox' => true,
            'multipleUpdate' => [
                [
                    'box'       => '#tab-policy-overview-inner',
                    'method'    => 'replaceWith',
                    'html'      => $html_tab_ovrview
                ],
                [
                    'box'       => '#_data-row-invoice-' . $invoice_record->id,
                    'method'    => 'replaceWith',
                    'html'      => $html_invoice_row
                ]
            ]
        ];
        return $this->template->json($ajax_data);
    }

    	/**
         * Send Activation SMS
         * ---------------------
         * Case 1: Fresh/Renewal/Transactional - After making payment, it gets activated automatically
         * Case 2: General Endorsement - After activating
         *
         * @param object $txn_record
         * @param object $policy_record
         * @return bool
         */
    	private function _sms_activation($txn_record, $policy_record, $invoice_record = NULL)
    	{
    		$customer_name 		= $policy_record->customer_name;
    		$customer_contact 	= $policy_record->customer_contact ? json_decode($policy_record->customer_contact) : NULL;
    		$mobile 			= $customer_contact->mobile ? $customer_contact->mobile : NULL;

    		if( !$mobile )
    		{
    			return FALSE;
    		}

    		$message = "Dear {$customer_name}," . PHP_EOL;

    		if( in_array($txn_record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
        	{
        		$message .= "Your Policy has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $invoice_record->amount . PHP_EOL .
        					"Expires on : " . $policy_record->end_date . PHP_EOL;
        	}
        	else if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_ET )
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

	    private function _payment_form( $invoice_data )
	    {
	        $form_data = [
	            'form_elements' => $this->_payment_rules(),
	            'record' 		=> NULL,
	            'invoice_data' 	=> $invoice_data
	        ];

	        /**
	         * Render The Form
	         */
	        $json_data = [
	            'form' => $this->load->view('accounting/invoices/_form_payment', $form_data, TRUE)
	        ];
	        $this->template->json($json_data);
	    }

	        private function _payment_rules()
	        {

	        	$received_in_dropdown = ac_payment_receipt_mode_dropdown(FALSE);
	            return [
	                [
	                    'field' => 'narration',
	                    'label' => 'Narration',
	                    'rules' => 'trim|max_length[255]',
	                    '_type' => 'textarea',
	                    'rows'  => '3',
	                ],
	                [
	                    'field' => 'adjustment_amount',
	                    'label' => 'Adjustment Amount',
	                    'rules' => 'trim|prep_decimal|decimal|max_length[20]',
	                    '_type' => 'text',
	                ],
	                [
	                    'field' => 'received_in',
	                    'label' => 'Received In',
	                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[' . implode(',', array_keys($received_in_dropdown)) . ']',
	                    '_type'     => 'dropdown',
	                    '_data'     => IQB_BLANK_SELECT + $received_in_dropdown,
	                    '_required' => true
	                ],
	                [
	                    'field' => 'received_in_date',
	                    'label' => 'Dated (Cheque/Draft)',
	                    'rules' => 'trim|valid_date',
	                    '_type'             => 'date',
	                    '_extra_attributes' => 'data-provide="datepicker-inline"',
	                    '_required' => false
	                ],
	                [
	                    'field' => 'received_in_ref',
	                    'label' => 'Reference (Cheque No./Draft No.)',
	                    'rules' => 'trim|max_length[100]',
	                    '_type' => 'text',
	                ]

	            ];
	        }
}