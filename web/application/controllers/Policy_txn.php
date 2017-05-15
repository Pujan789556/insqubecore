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
	 * Render the settings
	 *
	 * @return type
	 */
	function index()
	{
		$this->template->render_404();
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
							 * Build RI Distribution Data For This Policy
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
	        $txn_data['amt_vat'] 	= $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DUTY_AND_TAX_ID_VAT, $taxable_amount);

	        return $txn_data;
		}

	// --------------------- END: PRIVATE CRUD HELPER FUNCTIONS --------------------



}