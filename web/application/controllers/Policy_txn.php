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
		$this->load->helper('motor');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'policies/';
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
			'add_url' 					=> 'policy_txn/add/' . $policy_id,
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
				'rowId' 	=> '#tab-policy-transactions',
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
	// CRUD OPERATIONS
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

		// Let's get the Cost Reference Record for this Policy
		$crf_record = $this->policy_crf_model->get($txn_record->id);

		// No CRF Record? You can't move further. You must have one
		if( !$crf_record )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'No cost reference information found for this Policy.<br/>Please create one first and proceed.'
			], 400);
		}

		/**
		 * Policy Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		// Post? Save Premium
		$this->__save_premium($policy_record, $txn_record, $crf_record);


		// Render Form
		$this->__render_premium_form($policy_record, $txn_record, $crf_record);
	}





	// --------------------------------------------------------------------
	//  SAVE PREMIUM FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Save/Update Policy Premium
		 *
		 * !!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record 	Policy Record
		 * @param object $txn_record 		Policy Transaction Record
		 * @param object $crf_record 		Policy Cost Reference Record
		 * @return mixed
		 */
		private function __save_premium($policy_record, $txn_record, $crf_record)
		{
			if( $this->input->post() )
			{
				$done = FALSE;

				/**
				 * MOTOR
				 * -----
				 * For all type of motor portfolios, we have same package list
				 */
				if( in_array($policy_record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
				{
					$done = $this->__save_premium_MOTOR( $policy_record, $txn_record, $crf_record );
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
		 * Motor Portfolio : Save a Policy Transaction Record For Given Policy
		 *
		 *	!!! Important: Fresh/Renewal Only
		 *
		 * @param object $policy_record  Policy Record
		 * @param object $txn_record 	 Policy Transaction Record
		 * @param object $crf_record 		Policy Cost Reference Record
		 * @return json
		 */
		private function __save_premium_MOTOR($policy_record, $txn_record, $crf_record)
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

	            $this->form_validation->set_rules($v_rules);
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
							$crf_data = call_user_func($method, $policy_record, $policy_object, $tariff_record, $pfs_record, $post_data);

							/**
							 * Save CRF Data
							 * -----------------
							 *
							 * Task 1: Build CRF Data
							 * 		Copy the computed data to Cost Reference Table with "transfer_type" = "Take Whole Amount"
							 *
							 * Task 2: Build Txn Data
							 *		Transfer CRF data to TXN Table based on transfer type
							 *
							 * Task 3: Update CRF and TXN data
							 *
							 * Task 4: Update RI-Distribution for this Policy
							 */
							$crf_data['transfer_type'] 		= IQB_POLICY_CRF_TRANSFER_TYPE_FULL;
							$crf_data['computation_type'] 	= IQB_POLICY_CRF_COMPUTE_AUTO;

							$txn_data = $this->_prepare_txn_data($policy_record, $txn_record, $crf_data, $post_data);
							return $this->policy_txn_model->save($txn_record->id, $crf_data, $txn_data);

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
	        		// Reload Form With Validation Error
	        		$json_extra = [
						'status' 		=> 'error',
						'message' 		=> 'Validation Error.',
						'reloadForm' 	=> true
					];
					return $this->__render_premium_form($policy_record, $txn_record, $crf_record, $json_extra);
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
	 * @param object 	$crf_record Policy Cost Reference Record
	 * @param array 	$json_extra 	Extra Data to Pass as JSON
	 * @return type
	 */
	private function __render_premium_form($policy_record, $txn_record, $crf_record, $json_extra=[])
	{
		/**
		 *  Let's Load The Policy Transaction Form For this Record
		 */
		$policy_object = $this->__get_policy_object($policy_record);

		// Let's get the premium goodies for given portfolio
		$premium_goodies = $this->__premium_goodies($policy_record, $policy_object);

		// Valid Goodies?
		if( empty($premium_goodies) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'No portfolio configuration found for this transaction.'
			], 400);
		}

		// Policy Transaction Form
		$form_view = $this->__premium_form_view_by_portfolio($policy_record->portfolio_id);



		// Let's render the form
        $json_data['form'] = $this->load->view($form_view, [
								                'form_elements'         => $premium_goodies['validation_rules'],
								                'policy_record'         => $policy_record,
								                'txn_record'        	=> $txn_record,
								                'crf_record'        	=> $crf_record,
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
		 *
		 * @return	array
		 */
		private function __premium_goodies($policy_record, $policy_object)
		{
			$goodies = [];

			/**
			 * MOTOR
			 * -----
			 * For all type of motor portfolios, we have same package list
			 */
			if( in_array($policy_record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
			{
				$goodies = $this->__premium_goodies_MOTOR($policy_record, $policy_object);
			}


			return $goodies;
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

	//  ------------------- END: PREMIUM GOODIES FUNCTIONS -------------------------



	// --------------------------------------------------------------------
	// PRIVATE CRUD HELPER FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Get Policy Transaction Premium Form View
		 *
		 * @param id $portfolio_id Portfolio ID
		 * @return string
		 */
		private function __premium_form_view_by_portfolio($portfolio_id)
		{
			$form_view = '';

			/**
			 * MOTOR
			 * -----
			 * For all type of motor portfolios, we have same package list
			 */
			if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
			{
				$form_view = 'policy_txn/forms/_form_premium_MOTOR';
			}

			return $form_view;
		}

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

		// --------------------------------------------------------------------

		/**
		 * Prepare TXN Data
		 *
		 * Prepare transactional data based on the supplied crf data and other post data
		 *
		 * @param object $policy_record
		 * @param object $txn_record
		 * @param array $crf_data
		 * @param array $post_data
		 * @return array
		 */
		private function _prepare_txn_data($policy_record, $txn_record, $crf_data, $post_data)
		{
			$transfer_type = $crf_data['transfer_type'];
	        if( !$transfer_type )
	        {
	            throw new Exception("Exception [Controller: Policy_txn][Method: _prepare_txn_data()]: Invalid Transfer Type!");
	        }

	        /**
	         * Compute TXN data based on "transfer_type"
	         */
	        $txn_data = [];
	        switch($transfer_type)
	        {
	            case IQB_POLICY_CRF_TRANSFER_TYPE_FULL:
	            	$txn_data = $this->_crf_transfer_full($crf_data);
	                break;

	            case IQB_POLICY_CRF_TRANSFER_TYPE_PRORATA_ON_DIFF:
	                break;

	            case IQB_POLICY_CRF_TRANSFER_TYPE_SHORT_TERM_RATE_ON_FULL:
	                break;

	            case IQB_POLICY_CRF_TRANSFER_TYPE_DIRECT_DIFF:
	                break;
	        }

	        /**
	         * TXN Specific Post Data
	         */
	        $txn_data['txn_details'] 	= $post_data['txn_details'];
	        $txn_data['remarks'] 		= $post_data['remarks'];
	        return $txn_data;
		}

		private function _crf_transfer_full($crf_data)
		{
			$txn_data = [];

			/**
			 * Task 1: Simply Copy all data from CRF to TXN
			 */
			foreach(Policy_crf_model::$fields_to_txn_transfer as $field)
        	{
        		$txn_data[$field] = $crf_data[$field] ?? NULL;
        	}

        	/**
			 * Task 1: Compute VAT ON Taxable Amount
			 */
        	$this->load->model('ac_duties_and_tax_model');
	        $taxable_amount 		= $txn_data['amt_total_premium'] + $txn_data['amt_stamp_duty'];
	        $txn_data['amt_vat'] 	= $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);

	        return $txn_data;
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
	public function status($policy_id, $to_status_code, $ref)
	{
		// Current Policy Transaction Record and Has valid Type?
		$txn_record = $this->policy_txn_model->get_current_txn_by_policy($policy_id);
		if( !$txn_record  )
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

			if( $this->policy_txn_model->update_status($txn_record->policy_id, $to_status_code) )
			{
				/**
				 * Updated Transaction Record
				 */
				$txn_record = $this->policy_txn_model->get($txn_record->id);

				/**
				 * What to reload/render after success?
				 * -----------------------------------
				 * 	1. RI Approval Triggered from Policy Overview Tab
				 */
				if( $ref = 'policy_tab_overview' )
				{
					$policy_record = $this->policy_model->get($txn_record->policy_id);

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
					return $this->template->json([
						'status' 	=> 'error',
						'title' 	=> '@TODO',
						'message' 	=> 'Please update other reference return'
					], 400);
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

				case IQB_POLICY_TXN_STATUS_APPROVED:
					$permission_name = 'status.to.approved';
					break;

				case IQB_POLICY_TXN_STATUS_VOUCHERED:
					$permission_name = 'status.to.vouchered';
					break;

				case IQB_POLICY_TXN_STATUS_INVOICED:
					$permission_name = 'status.to.invoiced';
					break;

				case IQB_POLICY_STATUS_ACTIVE:
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
				 * 	Draft/Unverified/Verified/Approved are automatically triggered from
				 * 	Policy Status Update Method
				 */
				if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_FRESH  || $txn_record->txn_type == IQB_POLICY_TXN_TYPE_RENEWAL )
				{
					$__flag_passed = !in_array($to_updown_status, [
						IQB_POLICY_TXN_STATUS_DRAFT,
						IQB_POLICY_TXN_STATUS_UNVERIFIED,
						IQB_POLICY_TXN_STATUS_VERIFIED,
						IQB_POLICY_TXN_STATUS_APPROVED,
						IQB_POLICY_TXN_STATUS_ACTIVE
					]);
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

			return $__flag_pass;

		}

	// --------------------- END: STATUS UPGRADE/DOWNGRADE --------------------

	// --------------------------------------------------------------------
	//  POLICY Voucher & Invoice
	// --------------------------------------------------------------------

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
		if($txn_record->status !== IQB_POLICY_TXN_STATUS_APPROVED)
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'You can not perform this action.'
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
        $agent_commission_amount 					= $txn_record->amt_agent_commission;

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
         */
        if( $policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION && $policy_record->agent_id )
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
		$this->load->model('ac_voucher_model');
		$this->load->model('rel_policy_txn__voucher_model');

		try {

			/**
			 * Task 1: Save Voucher and Generate Voucher Code
			 */
			$voucher_id = $this->ac_voucher_model->add($voucher_data);

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

						$this->policy_txn_model->update_status($policy_record->id, IQB_POLICY_TXN_STATUS_VOUCHERED);

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



}