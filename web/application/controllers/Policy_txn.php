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
		if( !$this->dx_auth->is_authorized('policy_txn', 'edit.transaction') )
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
		            $done = $this->__save_premium_AGR_CROP($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
		         * ---------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
		        {
		            $done = $this->__save_premium_AGR_CATTLE($policy_record, $txn_record);
		        }

		        /**
		         * AGRICULTURE - POULTRY SUB-PORTFOLIO
		         * -----------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
		        {
		            $done = $this->__save_premium_AGR_POULTRY($policy_record, $txn_record);
		        }

				/**
				 * MOTOR PORTFOLIOS
				 * ----------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
				{
					$done = $this->__save_premium_MOTOR( $policy_record, $txn_record );
				}

				/**
				 * FIRE PORTFOLIOS
				 * ---------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
				{
					$done = $this->__save_premium_FIRE( $policy_record, $txn_record );
				}

				/**
				 * MARINE PORTFOLIOS
				 * ---------------
				 */
				else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
				{
					$done = $this->__save_premium_MARINE( $policy_record, $txn_record );
				}

				/**
		         * ENGINEERING - BOILER EXPLOSION
		         * ------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
		        {
		            $done = $this->__save_premium_ENG_BL( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR ALL RISK
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
		        {
		            $done = $this->__save_premium_ENG_CAR( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
		         * ------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
		        {
		            $done = $this->__save_premium_ENG_CPM( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
		        {
		            $done = $this->__save_premium_ENG_EEI( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - ERECTION ALL RISKS
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
		        {
					$done = $this->__save_premium_ENG_EAR( $policy_record, $txn_record );
		        }

		        /**
		         * ENGINEERING - MACHINE BREAKDOWN
		         * ---------------------------------------------
		         */
		        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
		        {
		            $done = $this->__save_premium_ENG_MB( $policy_record, $txn_record );
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
					$ajax_data = [
						'message' 		=> 'Successfully Updated.',
						'status'  		=> 'success',
						'updateSection' => true,
						'hideBootbox' 	=> true
					];

					/**
					 * Get the Policy Fresh/Renewal Txn Record
					 */
					try {

						$txn_record = $this->policy_txn_model->get_fresh_renewal_by_policy( $policy_record->id, $policy_record->ancestor_id ? IQB_POLICY_TXN_TYPE_RENEWAL : IQB_POLICY_TXN_TYPE_FRESH );

					} catch (Exception $e) {

						return $this->template->json([
							'status' => 'error',
							'message' => $e->getMessage()
						], 404);
					}

					/**
					 * Policy Cost Calculation Table
					 */
					$ajax_data['updateSectionData']  = [
						'box' 		=> '#_premium-card',
						'method' 	=> 'replaceWith',
						'html'		=> $this->load->view('policy_txn/_cost_calculation_table', ['txn_record' => $txn_record, 'policy_record' => $policy_record], TRUE)
					];

					return $this->template->json($ajax_data);
				}
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - AGRICULTURE - CROP
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_AGR_CROP($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{
				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);
				$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Tariff Record
				 */
				try {

					$tariff = _OBJ_AGR_CROP_tariff_by_type($object_attributes->crop_type);

				} catch (Exception $e) {

					return $this->template->json([
                        'status'        => 'error',
                        'title' 		=> 'Exception Occured',
                        'message' 	=> $e->getMessage()
                    ], 404);
				}


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_AGR_CROP_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Sum Insured Amount
						 */
						$SI = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount

						/**
						 * Get Tariff Rate
						 */
						$default_rate 	= floatval($tariff->rate);


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "क. बीमा शुल्क ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "ख. प्रत्यक्ष छूट ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "ग. (क - ख)",
							'value' => $C
						];


						// D = 75% of C
						$D = ($C * 75) / 100.00;
						$cost_calculation_table[] = [
							'label' => "घ. ग को ७५% ले हुन आउने छुट",
							'value' => $D
						];

						// NET PREMIUM = C - D
						$NET_PREMIUM = $C - $D;
						$cost_calculation_table[] = [
							'label' => "ङ. जम्मा (ग - घ)",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> 0.00,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * NOT Applicable!!!
						 */
						$premium_computation_table = NULL;
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - AGRICULTURE - CATTLE
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_AGR_CATTLE($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{
				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);
				$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Tariff Record
				 */
				try {

					$tariff = _OBJ_AGR_CATTLE_tariff_by_type($object_attributes->cattle_type);

				} catch (Exception $e) {

					return $this->template->json([
                        'status'        => 'error',
                        'title' 		=> 'Exception Occured',
                        'message' 	=> $e->getMessage()
                    ], 404);
				}


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_AGR_CATTLE_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Sum Insured Amount
						 */
						$SI = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount

						/**
						 * Get Tariff Rate
						 */
						$default_rate 	= floatval($tariff->rate);


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "क. बीमा शुल्क ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "ख. प्रत्यक्ष छूट ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "ग. (क - ख)",
							'value' => $C
						];


						// D = 75% of C
						$D = ($C * 75) / 100.00;
						$cost_calculation_table[] = [
							'label' => "घ. ग को ७५% ले हुन आउने छुट",
							'value' => $D
						];

						// NET PREMIUM = C - D
						$NET_PREMIUM = $C - $D;
						$cost_calculation_table[] = [
							'label' => "ङ. जम्मा (ग - घ)",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> 0.00,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * NOT Applicable!!!
						 */
						$premium_computation_table = NULL;
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - AGRICULTURE - POULTRY
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_AGR_POULTRY($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{
				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);
				$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Tariff Record
				 */
				try {

					$tariff = _OBJ_AGR_POULTRY_tariff_by_type($object_attributes->poultry_type);

				} catch (Exception $e) {

					return $this->template->json([
                        'status'        => 'error',
                        'title' 		=> 'Exception Occured',
                        'message' 	=> $e->getMessage()
                    ], 404);
				}


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_AGR_POULTRY_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Sum Insured Amount
						 */
						$SI = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount

						/**
						 * Get Tariff Rate
						 */
						$default_rate 	= floatval($tariff->rate);


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "क. बीमा शुल्क ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "ख. प्रत्यक्ष छूट ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "ग. (क - ख)",
							'value' => $C
						];


						// D = 75% of C
						$D = ($C * 75) / 100.00;
						$cost_calculation_table[] = [
							'label' => "घ. ग को ७५% ले हुन आउने छुट",
							'value' => $D
						];

						// NET PREMIUM = C - D
						$NET_PREMIUM = $C - $D;
						$cost_calculation_table[] = [
							'label' => "ङ. जम्मा (ग - घ)",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> 0.00,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * NOT Applicable!!!
						 */
						$premium_computation_table = NULL;
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Motor Portfolio : Save a Policy Transaction Record For Given Policy
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  Policy Record
		 * @param object $txn_record 	 Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_MOTOR($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Let's get the Required Records
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);
				$premium_goodies 	= $this->__premium_goodies($policy_record, $policy_object);
				$v_rules 			= $premium_goodies['validation_rules'];
				$tariff_record 		= $premium_goodies['tariff_record'];

				// Format Validation Rules
				$rules = [];
				foreach($v_rules as $section=>$r)
				{
					$rules = array_merge($rules, $r);
				}
	            $this->form_validation->set_rules($rules);
				if($this->form_validation->run() === TRUE )
	        	{

	        		// Portfolio Settings Record For Given Fiscal Year and Portfolio
					$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

					// Premium Data
					$post_data = $this->input->post();

					// Method to compute premium data
					$method = _PO_MOTOR_crf_compute_method($policy_record->portfolio_id);

					/**
					 * Do we have a valid method?
					 */
					if($method)
					{
						try{

							/**
							 * Get the Cost Reference Data
							 */
							$txn_data = call_user_func($method, $policy_record, $policy_object, $tariff_record, $pfs_record, $post_data);


							/**
							 * Compute VAT ON Taxable Amount
							 */
				        	$this->load->helper('account');
					        $taxable_amount 		= $txn_data['amt_total_premium'] + $txn_data['amt_stamp_duty'];
					        $txn_data['amt_vat'] 	= ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);

							$done 	  = $this->policy_txn_model->save($txn_record->id, $txn_data);

							return $done;

							/**
							 * @TODO
							 *
							 * 1. Build RI Distribution Data For This Policy
							 * 2. RI Approval Constraint for this Policy
							 */

						} catch (Exception $e){

							return $this->template->json([
								'status' 	=> 'error',
								'message' 	=> $e->getMessage()
							], 404);
						}
					}
					else
					{
						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> "No CRF computation method found for specified MOTOR portfolio!"
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Fire Portfolio : Save a Policy Transaction Record For Given Policy
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_FIRE($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

				/**
				 * Portfolio Risks
				 */
				$portfolio_risks = $this->portfolio_model->dropdown_risks($policy_record->portfolio_id);

				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_FIRE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 * 	From the Portfolio Risks - We compute two type of premiums
						 * 	a. Pool Premium
						 *  b. Base Premium
						 */


						/**
						 * Portfolio Risks Rows
						 */
						$portfolio_risks = $this->portfolio_model->portfolio_risks($policy_record->portfolio_id);

						/**
						 * Fire Items with Sum Insured
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$items              = $object_attributes->items;
						$item_count         = count($items->category);



						/**
						 * Let's Loop Through each Fire Item
						 */
						$premium_computation_table 	= [];
						$base_premium 				= 0.00;
						$pool_premium 				= 0.00;
						$commissionable_premium 	= 0.00;
						$direct_discount 			= 0.00;
						$agent_commission 			= 0.00;

						for($i=0; $i < $item_count; $i++ )
						{
							$item_sum_insured 	= $items->sum_insured[$i];
							foreach($portfolio_risks as $pr)
							{
								$rate = $post_data['premium']['rate'][$pr->id][$i];

								// Compute only if rate is supplied
								if($rate)
								{
									$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
									$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$base_premium += $premium;
									}
									else
									{
										$pool_premium += $premium;
									}

									// Commissionable Premium?
									if($pr->agent_commission == IQB_FLAG_ON )
									{
										$commissionable_premium += $premium;
									}
								}
							}
						}

						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 *
						 * Note: Direct Discount applies only on Base Premium
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							$direct_discount = ( $base_premium * $pfs_record->direct_discount ) / 100.00 ;
							$base_premium -= $direct_discount;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$agent_commission = ( $commissionable_premium * $pfs_record->agent_commission ) / 100.00;
						}


						/**
						 * Let's Compute the Total Premium
						 */
						$total_premium 	= $base_premium + $pool_premium;
						$taxable_amount = $total_premium + $post_data['amt_stamp_duty'];

						/**
						 * Compute VAT
						 */
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 */
						$txn_data = [
							'amt_total_premium' 	=> $total_premium,
							'amt_pool_premium' 		=> $pool_premium,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_data['premium']);
						$txn_data['premium_computation_table'] = $premium_computation_table;



						/**
						 * Cost Calculation Table - Schedule Data
						 *
						 * 	Property Details
						 * 	------------------------------------
						 * 	| Property | Sum Insured | Premium |
						 * 	------------------------------------
						 *  |		   | 			 |		   |
						 * 	------------------------------------
						 *
						 * 	Risk Details
						 * 	------------------
						 * 	| Risk | Premium |
						 * 	------------------
						 * 	|	   |		 |
						 * 	------------------
						 */
						$property_table = [];
						for($i=0; $i < $item_count; $i++ )
						{
							$item_sum_insured 		= $items->sum_insured[$i];
							$property_category 		= _OBJ_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ];
							$single_property_row 	= [ $property_category, $items->sum_insured[$i] ];

							$per_property_premium 		= 0.00;
							$per_property_base_premium 	= 0.00;
							$per_property_pool_premium 	= 0.00;

							foreach($portfolio_risks as $pr)
							{
								$rate = $post_data['premium']['rate'][$pr->id][$i];

								// Compute only if rate is supplied
								if($rate)
								{
									$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
									$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$per_property_base_premium += $premium;
									}
									else
									{
										$per_property_pool_premium += $premium;
									}
								}
							}

							/**
							 * Direct Discount Applies?
							 */
							if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
							{
								$direct_discount 			= ( $per_property_base_premium * $pfs_record->direct_discount ) / 100.00 ;
								$per_property_base_premium -= $direct_discount;
							}

							$per_property_premium 	= $per_property_base_premium  + $per_property_pool_premium;
							$single_property_row[] 	= $per_property_premium;
							$property_table[] 		= $single_property_row;
						}

						// --------------------------------------------------------------------------------------------

						$risk_table = [];
						foreach($portfolio_risks as $pr)
						{
							$per_risk_premium 		= 0.00;
							$per_risk_base_premium 	= 0.00;
							$per_risk_pool_premium 	= 0.00;

							for($i=0; $i < $item_count; $i++ )
							{
								$item_sum_insured 	= $items->sum_insured[$i];
								$rate 				= $post_data['premium']['rate'][$pr->id][$i];

								// Compute only if rate is supplied
								if($rate)
								{
									$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
									$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$per_risk_base_premium += $premium;
									}
									else
									{
										$per_risk_pool_premium += $premium;
									}
								}
							}

							/**
							 * Direct Discount Applies?
							 */
							if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
							{
								$direct_discount 		= ( $per_risk_base_premium * $pfs_record->direct_discount ) / 100.00 ;
								$per_risk_base_premium 	-= $direct_discount;
							}
							$per_risk_premium 	= $per_risk_base_premium  + $per_risk_pool_premium;


							/**
							 * Include the risk only with premium
							 */
							if( $per_risk_premium )
							{
								$risk_table[] 		= [$pr->name, $per_risk_premium];
							}
						}

						$cost_calculation_table = json_encode([
							'property_table' 	=> $property_table,
							'risk_table'		=> $risk_table
						]);

						$txn_data['cost_calculation_table'] = $cost_calculation_table;

						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - Marine
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_MARINE($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_MARINE_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 */
						$object_attributes   = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				 = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


						// Get the post premium data
						$post_premium 				= $post_data['premium'];
						$default_rate 				= floatval($post_premium['default_rate']);
						$default_discount 			= $post_premium['default_discount'];
						$container_discount 		= floatval($post_premium['container_discount']);
						$additional_rate1 			= floatval($post_premium['additional_rate1']);
						$additional_rate2 			= floatval($post_premium['additional_rate2']);
						$additional_rate3 			= floatval($post_premium['additional_rate3']);
						$large_sum_insured_discount = floatval($post_premium['large_sum_insured_discount']);

						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "A. Specified premium rate({$default_rate}%)",
							'value' => $A
						];


						// B = X% of A
						$B = 0.00;
						$default_discount_label = 'Discount on A (0%)';
						if($default_discount)
						{
							$default_discount_rate 	= _OBJ_MARINE_premium_default_discount_dropdown('rate', FALSE)[$default_discount];
							$default_discount_label = _OBJ_MARINE_premium_default_discount_dropdown('label', FALSE)[$default_discount];
							$B = ( $A * $default_discount_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "B. {$default_discount_label}",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "C. (A - B)",
							'value' => $C
						];

						// D = X% of C
						$D = ( $C * $container_discount ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "D. Container discount ({$container_discount}% of C)",
							'value' => $B
						];

						// E = C - D
						$E = $C - $D;
						$cost_calculation_table[] = [
							'label' => "E. (C - D)",
							'value' => $E
						];


						// F = X% of SI
						$F = ( $SI * $additional_rate1 ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "F. Additional premium for W& SRCC or SRCC ({$additional_rate1}%)",
							'value' => $F
						];

						// G = X% of SI
						$G = ( $SI * $additional_rate2 ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "G. Additional premium for Other i. ({$additional_rate2}%)",
							'value' => $G
						];

						// H = X% of SI
						$H = ( $SI * $additional_rate3 ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "H. Additional premium for Other ii. ({$additional_rate3}%)",
							'value' => $H
						];

						// I = E + F + G + H
						$I = $E + $F + $G + $H;
						$cost_calculation_table[] = [
							'label' => "I. Applicable premium rate (E+F+G+H)",
							'value' => $I
						];

						// Applicable Premium Rate (%)
						$APR = ( $I / $SI ) * 100.00;

						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Business Discount/Direct Discount
							$J = ( $I * $pfs_record->direct_discount ) / 100.00 ;

							$cost_calculation_table[] = [
								'label' => "J. Direct business discount ({$pfs_record->direct_discount}% of I)",
								'value' => $J
							];

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$J = 0.00;
						}


						// K = I - J
						$K = $I - $J;


						// Large Sum Insured Discount
						// L = X% of K
						$L = ( $K * $large_sum_insured_discount ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "L. Large Sum Insured Discount ({$large_sum_insured_discount}%)",
							'value' => $L
						];

						// Net Premium = Applicable Premium
						$NET_PREMIUM = $K - $L;
						$cost_calculation_table[] = [
							'label' => "Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Agent Commission if Applies?
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
						{
							$commissionable_premium = $NET_PREMIUM;
							$agent_commission 		= ( $NET_PREMIUM * $pfs_record->agent_commission ) / 100.00;
						}



						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> 0.00,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - BOILER EXPLOSION
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_BL($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_BL_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 * 	C. Third party liability Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount
						$TPL_AMOUNT 		= _OBJ_ENG_BL_compute_tpl_amount($object_attributes->third_party->limit ?? []);


						/**
						 * Get post premium Data
						 * 	a. Default Rate
						 * 	b. Third party Rate
						 * 	c. Pool Premium Flag
						 * 	d. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$default_rate 				= floatval($post_premium['default_rate']);
						$tp_rate 					= floatval($post_premium['tp_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium Rate ({$default_rate}%)",
							'value' => $A
						];

						// B = TP X TP Rate %
						$B = ( $TPL_AMOUNT * $tp_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "B. Third Party Rate ({$tp_rate}%)",
							'value' => $B
						];

						// C = A + B
						$C = $A + $B;
						$cost_calculation_table[] = [
							'label' => "C. Total Gross Premium",
							'value' => $C
						];


						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$D = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$D = ( $C * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $C;
							$agent_commission 		= ( $C * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $D
						];

						// E = C - D
						$E = $C - $D;
						$cost_calculation_table[] = [
							'label' => "E. (C - D)",
							'value' => $E
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A)
							$pool_rate = floatval($pfs_record->pool_premium);
							$POOL_PREMIUM = ( $A * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "F. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $E + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "G. Net Premium",
							'value' => $NET_PREMIUM
						];




						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - CONTRACTOR ALL RISK
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_CAR($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_CAR_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 * 	C. Third party liability Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount
						$TPL_AMOUNT 		= _OBJ_ENG_CAR_compute_tpl_amount($object_attributes->third_party->limit ?? []);


						/**
						 * Get post premium Data
						 * 	a. Default Premium (All items in total)
						 * 	b. Third party Rate
						 * 	c. Pool Premium Flag
						 * 	d. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$items_premium 				= _OBJ_ENG_CAR_compute_premium_total_by_items($post_premium['items'], $object_attributes->items);
						$tp_rate 					= floatval($post_premium['tp_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = Default Premium for all item
						$A = $items_premium;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium",
							'value' => $A
						];

						// B = TP X TP Rate %
						$B = ( $TPL_AMOUNT * $tp_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "B. Third Party Rate ({$tp_rate}%)",
							'value' => $B
						];

						// C = A + B
						$C = $A + $B;
						$cost_calculation_table[] = [
							'label' => "C. Total Gross Premium",
							'value' => $C
						];


						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$D = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$D = ( $C * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $C;
							$agent_commission 		= ( $C * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $D
						];

						// E = C - D
						$E = $C - $D;
						$cost_calculation_table[] = [
							'label' => "E. (C - D)",
							'value' => $E
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A - 4.3 Debris removal (of insured property))
							$pool_rate = floatval($pfs_record->pool_premium);


							// Debris Premium
							$debris_key 	= array_search('i4.3', $object_attributes->items->sn); // $key = 2;
							$si_debris 		= floatval($object_attributes->items->sum_insured[$debris_key]);
							$debris_rate 	= floatval($post_premium['items']['rate'][$debris_key]);
							$debris_premium = ($si_debris * $debris_rate ) / 100.00;

							$POOL_PREMIUM = ( ($A - $debris_premium) * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "F. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $E + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "G. Net Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - CONTRACTOR PLANT & MACHINARY
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_CPM($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_CPM_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


						/**
						 * Get post premium Data
						 * 	a. Default Rate
						 * 	b. Pool Premium Flag
						 * 	c. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$default_rate 				= floatval($post_premium['default_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium Rate ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "C. (A - B)",
							'value' => $C
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A-B)
							$pool_rate = floatval($pfs_record->pool_premium);
							$POOL_PREMIUM = ( $C * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "D. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $C + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "E. Net Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - CONTRACTOR PLANT & MACHINARY
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_EEI($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_EEI_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


						/**
						 * Get post premium Data
						 * 	a. Default Rate
						 * 	b. Pool Premium Flag
						 * 	c. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$default_rate 				= floatval($post_premium['default_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium Rate ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "C. (A - B)",
							'value' => $C
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A-B)
							$pool_rate = floatval($pfs_record->pool_premium);
							$POOL_PREMIUM = ( $C * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "D. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $C + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "E. Net Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - ERECTION ALL RISK
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_EAR($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_EAR_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 * 	C. Third party liability Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount
						$TPL_AMOUNT 		= _OBJ_ENG_EAR_compute_tpl_amount($object_attributes->third_party->limit ?? []);


						/**
						 * Get post premium Data
						 * 	a. Default Premium (All items in total)
						 * 	b. Third party Rate
						 * 	c. Pool Premium Flag
						 * 	d. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$items_premium 				= _OBJ_ENG_EAR_compute_premium_total_by_items($post_premium['items'], $object_attributes->items);
						$tp_rate 					= floatval($post_premium['tp_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = Default Premium for all item
						$A = $items_premium;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium",
							'value' => $A
						];

						// B = TP X TP Rate %
						$B = ( $TPL_AMOUNT * $tp_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "B. Third Party Rate ({$tp_rate}%)",
							'value' => $B
						];

						// C = A + B
						$C = $A + $B;
						$cost_calculation_table[] = [
							'label' => "C. Total Gross Premium",
							'value' => $C
						];


						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$D = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$D = ( $C * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $C;
							$agent_commission 		= ( $C * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $D
						];

						// E = C - D
						$E = $C - $D;
						$cost_calculation_table[] = [
							'label' => "E. (C - D)",
							'value' => $E
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A - 2. Clearance & Removal of Debris )
							$pool_rate = floatval($pfs_record->pool_premium);

							// Debris Premium
							$debris_key 	= array_search('I2', $object_attributes->items->sn); // $key = 2;
							$si_debris 		= floatval($object_attributes->items->sum_insured[$debris_key]);
							$debris_rate 	= floatval($post_premium['items']['rate'][$debris_key]);
							$debris_premium = ($si_debris * $debris_rate ) / 100.00;

							$POOL_PREMIUM = ( ($A - $debris_premium) * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "F. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $E + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "G. Net Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		}

		// --------------------------------------------------------------------

		/**
		 * Update Policy Premium Information - ENGINEERING - MACHINE BREAKDOWN
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  	Policy Record
		 * @param object $txn_record 	 	Policy Transaction Record
		 * @return json
		 */
		private function __save_premium_ENG_MB($policy_record, $txn_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{

				/**
				 * Policy Object Record
				 */
				$policy_object 		= $this->__get_policy_object($policy_record);

				/**
				 * Portfolio Setting Record
				 */
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


				/**
				 * Validation Rules for Form Processing
				 */
				$validation_rules = _TXN_ENG_MB_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
	            $this->form_validation->set_rules($validation_rules);

	            // echo '<pre>';print_r($validation_rules);exit;

				if($this->form_validation->run() === TRUE )
	        	{

					// Premium Data
					$post_data = $this->input->post();

					/**
					 * Do we have a valid method?
					 */
					try{

						/**
						 * Compute Premium From Post Data
						 * ------------------------------
						 */
						$cost_calculation_table 	= [];
						$premium_computation_table 	= [];


						/**
						 * Extract Information from Object
						 * 	A. Object Atrributes
						 * 	B. Sum Insured Amount
						 */
						$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
						$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


						/**
						 * Get post premium Data
						 * 	a. Default Rate
						 * 	b. Pool Premium Flag
						 * 	c. Other common data
						 */
						$post_premium 				= $post_data['premium'];
						$default_rate 				= floatval($post_premium['default_rate']);
						$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


						// A = SI X Default Rate %
						$A = ( $SI * $default_rate ) / 100.00;
						$cost_calculation_table[] = [
							'label' => "A. Gross Premium Rate ({$default_rate}%)",
							'value' => $A
						];



						/**
						 * Direct Discount or Agent Commission?
						 * ------------------------------------
						 * Agent Commission or Direct Discount
						 * applies on NET Premium
						 */
						$B = 0.00;
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							// Direct Discount
							$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

							// NULLIFY Commissionable premium, Agent Commission
							$commissionable_premium = NULL;
							$agent_commission = NULL;
						}
						else
						{
							$commissionable_premium = $A;
							$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
						}

						$cost_calculation_table[] = [
							'label' => "D. Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $B
						];

						// C = A - B
						$C = $A - $B;
						$cost_calculation_table[] = [
							'label' => "C. (A - B)",
							'value' => $C
						];

						/**
						 * Pool Premium
						 */
						$POOL_PREMIUM = 0.00;
						if($flag_pool_risk)
						{
							// Pool Premium = x% of Default Premium (A-B)
							$pool_rate = floatval($pfs_record->pool_premium);
							$POOL_PREMIUM = ( $C * $pool_rate ) / 100.00;
						}
						$cost_calculation_table[] = [
							'label' => "D. Pool Premium",
							'value' => $POOL_PREMIUM
						];

						$NET_PREMIUM = $C + $POOL_PREMIUM;
						$cost_calculation_table[] = [
							'label' => "E. Net Premium",
							'value' => $NET_PREMIUM
						];


						/**
						 * Compute VAT
						 */
						$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
						$this->load->helper('account');
						$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


						/**
						 * Prepare Transactional Data
						 *
						 * @TODO: What is Pool Premium Amount?
						 */
						$txn_data = [
							'amt_total_premium' 	=> $NET_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
							'amt_commissionable'	=> $commissionable_premium,
							'amt_agent_commission'  => $agent_commission,
							'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
							'amt_vat' 				=> $amount_vat,
							'txn_details' 			=> $post_data['txn_details'],
							'remarks' 				=> $post_data['remarks'],
						];


						/**
						 * Premium Computation Table
						 * -------------------------
						 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
						 */
						$premium_computation_table = json_encode($post_premium);
						$txn_data['premium_computation_table'] = $premium_computation_table;


						/**
						 * Cost Calculation Table
						 */
						$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
						return $this->policy_txn_model->save($txn_record->id, $txn_data);


						/**
						 * @TODO
						 *
						 * 1. Build RI Distribution Data For This Policy
						 * 2. RI Approval Constraint for this Policy
						 */

					} catch (Exception $e){

						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
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
		$policy_object = $this->__get_policy_object($policy_record);

		// Let's get all risks of this portfolio
		$portfolio_risks = $this->portfolio_model->dropdown_risks($policy_record->portfolio_id);


		// Let's get the premium goodies for given portfolio
		$premium_goodies = $this->__premium_goodies($policy_record, $policy_object, $portfolio_risks);

		// Valid Goodies?
		if( empty($premium_goodies) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'No portfolio configuration found for this transaction.'
			], 400);
		}

		// Policy Transaction Form
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
								                'txn_record'        	=> $txn_record,
								                'policy_object' 		=> $policy_object,
								                'tariff_record' 		=> $premium_goodies['tariff_record']
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
	            $goodies = $this->__premium_goodies_AGR_CROP($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
	         * ---------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
	        {
	            $goodies = $this->__premium_goodies_AGR_CATTLE($policy_record, $policy_object);
	        }

	        /**
	         * AGRICULTURE - POULTRY SUB-PORTFOLIO
	         * -----------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
	        {
	            $goodies = $this->__premium_goodies_AGR_POULTRY($policy_record, $policy_object);
	        }

			/**
			 * MOTOR PORTFOLIOS
			 * ----------------
			 */
			else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
			{
				$goodies = $this->__premium_goodies_MOTOR($policy_record, $policy_object);
			}

			/**
			 * FIRE PORTFOLIOS
			 * ---------------
			 */
			else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
			{
				$goodies = $this->__premium_goodies_FIRE($policy_record, $policy_object, $portfolio_risks);
			}

			/**
			 * MARINE PORTFOLIOS
			 * ---------------
			 */
			else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
			{
				$goodies = $this->__premium_goodies_MARINE($policy_record, $policy_object);
			}

			/**
			 * BOILER EXPLOSION (ENGINEERING)
			 * -----------------------------
			 */
			else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
			{
				$goodies = $this->__premium_goodies_ENG_BL($policy_record, $policy_object);
			}

			/**
	         * ENGINEERING - CONTRACTOR ALL RISK
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
	        {
	            $goodies = $this->__premium_goodies_ENG_CAR($policy_record, $policy_object);
	        }

			/**
			 * CONTRACTOR PLANT & MACHINARY (ENGINEERING)
			 * -------------------------------------------
			 */
			else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
			{
				$goodies = $this->__premium_goodies_ENG_CPM($policy_record, $policy_object);
			}

			/**
	         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
	        {
	            $goodies = $this->__premium_goodies_ENG_EEI($policy_record, $policy_object);
	        }

	        /**
	         * ENGINEERING - ERECTION ALL RISKS
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
	        {
				$goodies = $this->__premium_goodies_ENG_EAR($policy_record, $policy_object);
	        }

	        /**
	         * ENGINEERING - MACHINE BREAKDOWN
	         * ---------------------------------------------
	         */
	        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
	        {
	            $goodies = $this->__premium_goodies_ENG_MB($policy_record, $policy_object);
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

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for CROP (Agriculture)
		 *
		 * Get the following goodies for the Crop Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_AGR_CROP($policy_record, $policy_object)
		{
			// Tariff Configuration for this Portfolio
			$this->load->model('tariff_agriculture_model');
			$tariff_record = $this->tariff_agriculture_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Valid Tariff?
			$__flag_valid_tariff = TRUE;
			if( !$tariff_record )
			{
				$message 	= 'Tariff Configuration for this Portfolio is not found.';
				$title 		= 'Tariff Not Found!';
				$__flag_valid_tariff = FALSE;
			}
			else if( $tariff_record->active == IQB_STATUS_INACTIVE )
			{
				$message = 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
				$title = 'Tariff Not Active!';
				$__flag_valid_tariff = FALSE;
			}

			if( !$__flag_valid_tariff )
			{
				$message .= '<br/><br/>Portfolio: <strong>CROP</strong> <br/>' .
							'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
							'<br/>Please contact <strong>IT Department</strong> for further assistance.';

				$this->template->json(['error' => 'not_found', 'message' => $message, 'title' => $title], 404);
				exit(1);
			}


			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_AGR_CROP_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> $tariff_record
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for CATTLE (Agriculture)
		 *
		 * Get the following goodies for the Crop Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_AGR_CATTLE($policy_record, $policy_object)
		{
			// Tariff Configuration for this Portfolio
			$this->load->model('tariff_agriculture_model');
			$tariff_record = $this->tariff_agriculture_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Valid Tariff?
			$__flag_valid_tariff = TRUE;
			if( !$tariff_record )
			{
				$message 	= 'Tariff Configuration for this Portfolio is not found.';
				$title 		= 'Tariff Not Found!';
				$__flag_valid_tariff = FALSE;
			}
			else if( $tariff_record->active == IQB_STATUS_INACTIVE )
			{
				$message = 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
				$title = 'Tariff Not Active!';
				$__flag_valid_tariff = FALSE;
			}

			if( !$__flag_valid_tariff )
			{
				$message .= '<br/><br/>Portfolio: <strong>CATTLE</strong> <br/>' .
							'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
							'<br/>Please contact <strong>IT Department</strong> for further assistance.';

				$this->template->json(['error' => 'not_found', 'message' => $message, 'title' => $title], 404);
				exit(1);
			}


			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_AGR_CATTLE_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> $tariff_record
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for POULTRY (Agriculture)
		 *
		 * Get the following goodies for the Poultry Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_AGR_POULTRY($policy_record, $policy_object)
		{
			// Tariff Configuration for this Portfolio
			$this->load->model('tariff_agriculture_model');
			$tariff_record = $this->tariff_agriculture_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Valid Tariff?
			$__flag_valid_tariff = TRUE;
			if( !$tariff_record )
			{
				$message 	= 'Tariff Configuration for this Portfolio is not found.';
				$title 		= 'Tariff Not Found!';
				$__flag_valid_tariff = FALSE;
			}
			else if( $tariff_record->active == IQB_STATUS_INACTIVE )
			{
				$message = 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
				$title = 'Tariff Not Active!';
				$__flag_valid_tariff = FALSE;
			}

			if( !$__flag_valid_tariff )
			{
				$message .= '<br/><br/>Portfolio: <strong>POULTRY</strong> <br/>' .
							'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
							'<br/>Please contact <strong>IT Department</strong> for further assistance.';

				$this->template->json(['error' => 'not_found', 'message' => $message, 'title' => $title], 404);
				exit(1);
			}


			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_AGR_POULTRY_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> $tariff_record
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for MOTOR
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_MOTOR($policy_record, $policy_object)
		{

			// Object Attributes
			$attributes = json_decode($policy_object->attributes);

			// Tariff Configuration for this Portfolio
			$this->load->model('tariff_motor_model');
			$tariff_record = $this->tariff_motor_model->get_single(
															$policy_record->fiscal_yr_id,
															$attributes->ownership,
															$policy_record->portfolio_id,
															$attributes->cvc_type ?? NULL
														);

			// Valid Tariff?
			$__flag_valid_tariff = TRUE;
			if( !$tariff_record )
			{
				$message 	= 'Tariff Configuration for this Portfolio is not found.';
				$title 		= 'Tariff Not Found!';
				$__flag_valid_tariff = FALSE;
			}
			else if( $tariff_record->active == IQB_STATUS_INACTIVE )
			{
				$message = 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
				$title = 'Tariff Not Active!';
				$__flag_valid_tariff = FALSE;
			}

			if( !$__flag_valid_tariff )
			{
				$message .= '<br/><br/>Portfolio: <strong>MOTOR</strong> <br/>' .
							'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
							'<br/>Please contact <strong>IT Department</strong> for further assistance.';

				$this->template->json(['error' => 'not_found', 'message' => $message, 'title' => $title], 404);
				exit(1);
			}


			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_MOTOR_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> $tariff_record
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for FIRE
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 * @param object $portfolio_risks Portfolio Risks
		 *
		 * @return	array
		 */
		private function __premium_goodies_FIRE($policy_record, $policy_object, $portfolio_risks)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_FIRE_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );

			// echo '<pre>'; print_r($validation_rules);exit;

			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for FIRE
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_MARINE($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_MARINE_premium_validation_rules( $policy_record, $pfs_record, $policy_object );

			// echo '<pre>'; print_r($validation_rules);exit;

			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: BOILDER EXPLOSION
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_BL($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_BL_premium_validation_rules( $policy_record, $pfs_record, $policy_object );

			// echo '<pre>'; print_r($validation_rules);exit;

			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: CONTRACTOR ALL RISK
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_CAR($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_CAR_premium_validation_rules( $policy_record, $pfs_record, $policy_object );

			// echo '<pre>'; print_r($validation_rules);exit;

			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: CONTRACTOR PLANT & MACHINARY
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_CPM($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_CPM_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: CONTRACTOR PLANT & MACHINARY
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_EEI($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_EEI_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: CONTRACTOR ALL RISK
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_EAR($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_EAR_premium_validation_rules( $policy_record, $pfs_record, $policy_object );

			// echo '<pre>'; print_r($validation_rules);exit;

			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

		// --------------------------------------------------------------------

		/**
		 * Get Policy Policy Transaction Goodies for Boiler Explosion
		 *
		 * Portfolio 		: ENGINEERING
		 * Sub-Portfolio	: MACHINE BREAKDOWN
		 *
		 * Get the following goodies for the Motor Portfolio
		 * 		1. Validation Rules
		 * 		2. Tariff Record if Applies
		 *
		 * @param object $policy_record Policy Record
		 * @param object $policy_object Policy Object Record
		 *
		 * @return	array
		 */
		private function __premium_goodies_ENG_MB($policy_record, $policy_object)
		{
			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Let's Get the Validation Rules
			$validation_rules = _TXN_ENG_MB_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


			// Return the goodies
			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> NULL
			];
		}

	//  ------------------- END: PREMIUM GOODIES FUNCTIONS -------------------------



	// --------------------------------------------------------------------
	// PRIVATE CRUD HELPER FUNCTIONS
	// --------------------------------------------------------------------


		// --------------------------------------------------------------------

		/**
		 * Get Policy Object
		 *
		 * @param object $policy_record Policy Record
		 * @return object 	Policy Object
		 */
		private function __get_policy_object($policy_record)
		{
			// Policy Record contains the following columns by prefixing "object_"
			$object_columns = ['id', 'portfolio_id', 'customer_id', 'attributes', 'amt_sum_insured', 'flag_locked'];
			$object = new StdClass();
			foreach($object_columns as $column )
			{
				$object->{$column} = $policy_record->{'object_' . $column};
			}
			return $object;
		}


	// --------------------- END: PRIVATE CRUD HELPER FUNCTIONS --------------------


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