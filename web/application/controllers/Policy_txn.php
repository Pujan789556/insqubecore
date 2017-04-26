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


		// Post? Save it
		// $this->__save($policy_record, $txn_record);


		// Render Form
		$this->__render_premium_form($policy_record, $txn_record, $crf_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Policy Policy Transaction Record (Endorsement Records)
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($policy_id)
	{
		$policy_id = (int)$policy_id;
		$policy_record = $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// Policy Transaction Record
		$premium_record = $this->premium_model->find_by(['policy_id' => $policy_record->id]);
		if(!$premium_record)
		{
			$this->template->render_404('', 'No Policy Transaction Record Found For Supplied Policy.');
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		/**
		 * Check Editable?
		 */
		is_policy_editable($policy_record->status);


		// Post? Save it
		$this->__save($policy_record, $premium_record);




		// Render Form
		$this->__render_form($policy_record, $premium_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Policy Transaction Form View
	 *
	 * @param object $policy_record Policy Record
	 * @return string
	 */
	private function __get_form_view_by_portfolio($policy_record)
	{
		$form_view = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($policy_record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$form_view = 'policies_txn/_form_MOTOR';
		}

		// $form_view = 'premium/_form_' . $policy_record->portfolio_code;
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
	 * Save/Update Policy Transaction
	 *
	 * @param type $policy_record 	Policy Record
	 * @param type $premium_record 	Policy Policy Transaction Record
	 * @return mixed
	 */
	private function __save($policy_record, $premium_record)
	{
		if( $this->input->post() )
		{
			$done = FALSE;
			switch ($policy_record->portfolio_id)
			{
				// Motor
				case IQB_MASTER_PORTFOLIO_MOTOR_ID:
						$done = $this->__save_MOTOR($policy_record, $premium_record);
					break;

				default:
					# code...
					break;
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
				 * Widget or Row?
				 */
				$policy_record = $this->policy_model->get($policy_record->id);

				/**
				 * Policy Policy Transaction Card
				 */
				$premium_record = (object)[
					'policy_id' 			=> $policy_record->id,
					'total_premium_amount' 	=> $policy_record->total_premium_amount,
					'stamp_duty_amount' 	=> $policy_record->stamp_duty_amount,
					'attributes'			=> $policy_record->premium_attributes
				];
				$ajax_data['updateSectionData']  = [
					'box' 		=> '#_premium-card',
					'method' 	=> 'replaceWith',
					'html'		=> $this->load->view('premium/_card_overview', ['premium_record' => $premium_record, 'policy_record' => $policy_record], TRUE)
				];

				return $this->template->json($ajax_data);
			}
		}
	}

	// --------------------------------------------------------------------

		/**
		 * Motor Portfolio : Save a Policy Transaction Record For Given Policy
		 *
		 * @param object|null $policy_record  Policy Record
		 * @param type $premium_record 	Policy Policy Transaction Record
		 * @return json
		 */
		private function __save_MOTOR($policy_record, $premium_record)
		{
			/**
			 * Form Submitted?
			 */
			$return_data = [];

			if( $this->input->post() )
			{
				// Policy Object
				$policy_object = $this->__get_policy_object($policy_record);

				// Let's get the premium goodies for given portfolio
				$premium_goodies = $this->__premium_goodies($policy_record, $policy_object);

				// Validation Rules
				$v_rules = $premium_goodies['validation_rules'];

	            $this->form_validation->set_rules($v_rules);
				if($this->form_validation->run() === TRUE )
	        	{
	        		$data = $this->input->post();

	        		// Get Object Attributes
					$attributes = json_decode($policy_object->attributes);

					// Tariff Record
					$tariff_record = $premium_goodies['tariff_record'];

					// Save Policy Transaction According to Subportfolio
					switch ($policy_record->sub_portfolio_code)
					{
						case IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE:
							return $this->__save_MOTOR_MCY($policy_record, $policy_object, $tariff_record );
							break;

						case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE:
							return $this->__save_MOTOR_PVC($policy_record, $policy_object, $tariff_record );
							break;

						case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE:
							return $this->__save_MOTOR_CVC($policy_record, $policy_object, $tariff_record );
							break;

						default:
							# code...
							break;
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
					return $this->__render_form($policy_record, $premium_record, $json_extra);
	        	}
			}
		}

			/**
			 * Save Motorcycle Policy Transaction
			 *
			 * @param object $policy_record
			 * @param object $object
			 * @param object $tariff_record
			 * @return json
			 */
			private function __save_MOTOR_MCY($policy_record, $policy_object, $tariff_record)
			{
				// Portfolio Settings Record For Given Fiscal Year and Portfolio
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

				$data = $this->input->post();
				try{

					$premium_data = _PO_MOTOR_MCY_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Target Policy Transaction Record
				// $premium_record = $this->premium_model->find_by(['policy_id' => $policy_record->id]);

				// Find Existing Policy Transaction Record
				return $this->premium_model->save($policy_record->id, $premium_data);

			}

			/**
			 * Save Private Vehicle Policy Transaction
			 *
			 * @param object $policy_record
			 * @param object $object
			 * @param object $tariff_record
			 * @return json
			 */
			private function __save_MOTOR_PVC($policy_record, $policy_object, $tariff_record)
			{
				// Portfolio Settings Record For Given Fiscal Year and Portfolio
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

				$data = $this->input->post();

				try{

					$premium_data = _PO_MOTOR_PVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Find Existing Policy Transaction Record
				return $this->premium_model->save($policy_record->id, $premium_data);

			}

			/**
			 * Save Commercial Vehicle Policy Transaction
			 *
			 * @param object $policy_record
			 * @param object $object
			 * @param object $tariff_record
			 * @return json
			 */
			private function __save_MOTOR_CVC($policy_record, $policy_object, $tariff_record)
			{
				// Portfolio Settings Record For Given Fiscal Year and Portfolio
				$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

				$data = $this->input->post();
				try{

					$premium_data = _PO_MOTOR_CVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Find Existing Policy Transaction Record
				// return $this->premium_model->update($premium_record->id, $premium_data, TRUE);
				return $this->premium_model->save($policy_record->id, $premium_data);
			}

	// --------------------------------------------------------------------


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
		$form_view = $this->__get_form_view_by_portfolio($policy_record);



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

	// --------------------------------------------------------------------


}