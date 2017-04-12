<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Premium Controller
 *
 * We use this controller to work with the policy premium.
 *
 * This controller falls under "Policy" category.
 *
 * @category 	Policy
 */

// --------------------------------------------------------------------

class Premium extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Premium';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('portfolio_setting_model');
		$this->load->model('premium_model');
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
	 * Edit a Policy Premium Record
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($policy_id)
	{
		// $this->template->render_404();
		// Valid Record ?
		$policy_id = (int)$policy_id;
		$policy_record = $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// Premium Record
		$premium_record = $this->premium_model->find_by(['policy_id' => $policy_record->id]);
		if(!$premium_record)
		{
			$this->template->render_404('', 'No Premium Record Found For Supplied Policy.');
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
	 * Get Premium Form View
	 *
	 * @param object $policy_record Policy Record
	 * @return string
	 */
	private function __get_form_view_by_portfolio($policy_record)
	{
		$form_view = 'premium/_form_' . $policy_record->portfolio_code;
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
		$policy_object = new StdClass();
		$policy_object->attributes 		= $policy_record->object_attributes;
		$policy_object->id 				= $policy_record->object_id;
		$policy_object->portfolio_id 	= $policy_record->portfolio_id;
		$policy_object->sub_portfolio_id 	= $policy_record->sub_portfolio_id;

		return $policy_object;
	}

	// --------------------------------------------------------------------

	/**
	 * Save/Update Premium
	 *
	 * @param type $policy_record 	Policy Record
	 * @param type $premium_record 	Policy Premium Record
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
				 * Policy Premium Card
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
		 * Motor Portfolio : Save a Premium Record For Given Policy
		 *
		 * @param object|null $policy_record  Policy Record
		 * @param type $premium_record 	Policy Premium Record
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

					// Save Premium According to Subportfolio
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
			 * Save Motorcycle Premium
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

					$premium_data = _PORTFOLIO_MOTOR_MCY_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Target Premium Record
				// $premium_record = $this->premium_model->find_by(['policy_id' => $policy_record->id]);

				// Find Existing Premium Record
				return $this->premium_model->save($policy_record->id, $premium_data);

			}

			/**
			 * Save Private Vehicle Premium
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

					$premium_data = _PORTFOLIO_MOTOR_PVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Find Existing Premium Record
				return $this->premium_model->save($policy_record->id, $premium_data);

			}

			/**
			 * Save Commercial Vehicle Premium
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

					$premium_data = _PORTFOLIO_MOTOR_CVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data );

				} catch (Exception $e){
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Exception: ' . $e->getMessage()
					], 404);
				}

				// Find Existing Premium Record
				// return $this->premium_model->update($premium_record->id, $premium_data, TRUE);
				return $this->premium_model->save($policy_record->id, $premium_data);
			}

	// --------------------------------------------------------------------


	/**
	 * Render Premium Form
	 *
	 * @param object 	$policy_record 	Policy Record
	 * @param object 	$premium_record Premium Record
	 * @param array 	$json_extra 	Extra Data to Pass as JSON
	 * @return type
	 */
	private function __render_form($policy_record, $premium_record, $json_extra=[])
	{
		/**
		 *  Let's Load The Premium Form For this Record
		 */
		$policy_object = $this->__get_policy_object($policy_record);

		// Let's get the premium goodies for given portfolio
		$premium_goodies = $this->__premium_goodies($policy_record, $policy_object);

		// Valid Goodies?
		if( empty($premium_goodies) )
		{
			$this->template->render_404('', 'No premium configuration found for this portfolio.');
		}

		// Premium Form
		$form_view = $this->__get_form_view_by_portfolio($policy_record);

		// Let's render the form
        $json_data['form'] = $this->load->view($form_view, [
								                'form_elements'         => $premium_goodies['validation_rules'],
								                'policy_record'         => $policy_record,
								                'premium_record'        => $premium_record,
								                'policy_object' 		=> $policy_object,
								                'tariff_record' 		=> $premium_goodies['tariff_record']
								            ], TRUE);

        $json_data = array_merge($json_data, $json_extra);

        // Return HTML
        $this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Policy Premium Goodies
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
		switch ($policy_record->portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$goodies = $this->__premium_goodies_MOTOR($policy_record, $policy_object);
				break;

			default:
				# code...
				break;
		}
		return $goodies;
	}

	// --------------------------------------------------------------------

		/**
		 * Get Policy Premium Goodies for MOTOR
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

			// Get Object Attributes
			$attributes = json_decode($policy_object->attributes);

			// Get the Tariff Configuration Record For this Portfolio
			$this->load->model('tariff_motor_model');
			$tariff_record = $this->tariff_motor_model->get_single(
															$policy_record->fiscal_yr_id,
															$attributes->ownership,
															$policy_record->sub_portfolio_code,
															$attributes->cvc_type ? $attributes->cvc_type : NULL
														);


			$__flag_valid_tariff = TRUE;
			if( !$tariff_record )
			{
				$message 	= 'Tariff Configuration for this Portfolio is not found.';
				$title 		= 'Tariff Not Found!';
				$__flag_valid_tariff = FALSE;
			}
			else if( $tariff_record->active == '0')
			{
				$message = 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
				$title = 'Tariff Not Active!';
				$__flag_valid_tariff = FALSE;
			}

			if( !$__flag_valid_tariff )
			{
				$message .= '<br/><br/>Portfolio: <strong>MOTOR</strong> <br/>' .
							'Sub-Portfolio: <strong>' . $policy_record->sub_portfolio_code . '</strong> <br/>' .
							'<br/>Please contact <strong>IT Department</strong> for further assistance.';

				$this->template->json(['error' => 'not_found', 'message' => $message, 'title' => $title], 404);
				exit(1);
			}

			// Portfolio Setting Record
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			// Common Field: Stamp Duty
			$rule_stamp_duty = [
                'field' => 'stamp_duty_amount',
                'label' => 'Stamp Duty(Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                '_type'     => 'text',
                '_default' 	=> $pfs_record->stamp_duty,
                '_required' => true
            ];

			$validation_rules = [];

			/**
			 * Validation Rule Logic
			 * --------------------------
			 *
			 * If policy package is "Third Party", we don't need any other validation fields
			 * We can simply return the stamp validation rules
			 */
			if($policy_record->policy_package == IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
			{
				$validation_rules = [$rule_stamp_duty];

				return  [
					'validation_rules' 	=> $validation_rules,
					'tariff_record' 	=> $tariff_record
				];
			}

			$__common_validation_rules =  [
				[
                    'field' => 'extra_fields[dr_voluntary_excess]',
                    'label' => 'Voluntary Excess',
                    'rules' => 'trim|prep_decimal|decimal|max_length[5]',
                    '_key' 		=> 'dr_voluntary_excess',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess),
                    '_required' => false
                ],
                [
                    'field' => 'extra_fields[no_claim_discount]',
                    'label' => 'No Claim Discount',
                    'rules' => 'trim|prep_decimal|decimal|max_length[5]',
                    '_key' 		=> 'no_claim_discount',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount),
                    '_required' => false
                ],
                [
                    'field' => 'extra_fields[flag_risk_mob]',
                    'label' => 'Pool Risk Mob (हुलदंगा, हडताल र द्वेशपूर्ण कार्य जोखिम बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_risk_mob',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ],
                [
                    'field' => 'extra_fields[flag_risk_terorrism]',
                    'label' => 'Pool Risk Terorrism (आतंककारी/विध्वंशात्मक कार्य जोखिम बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_risk_terorrism',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ]
			];

			switch ($policy_record->sub_portfolio_code)
			{
				case IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE:
					$validation_rules = array_merge($__common_validation_rules, [$rule_stamp_duty]);
					break;

				case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE:
					$extra_rules = [

						// Commercial Use
						[
		                    'field' => 'extra_fields[flag_commercial_use]',
		                    'label' => 'Commercial Use (निजी प्रयोजनको लागि भाडामा दिएको)',
		                    'rules' => 'trim|integer|in_list[1]',
		                    '_key' 		=> 'flag_commercial_use',
		                    '_type'     => 'checkbox',
		                    '_checkbox_value' 	=> '1',
		                    '_required' => false
		                ],

		                // Pay for Towing
						[
		                    'field' => 'extra_fields[flag_towing]',
		                    'label' => 'Towing (दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा)',
		                    'rules' => 'trim|integer|in_list[1]',
		                    '_key' 		=> 'flag_towing',
		                    '_type'     => 'checkbox',
		                    '_checkbox_value' 	=> '1',
		                    '_required' => false
		                ]
					];
					$validation_rules = array_merge($__common_validation_rules, $extra_rules, [$rule_stamp_duty]);
					break;

				case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE:
					$extra_rules = [

						// Private Use
						[
		                    'field' => 'extra_fields[flag_private_use]',
		                    'label' => 'Private Use',
		                    'rules' => 'trim|integer|in_list[1]',
		                    '_key' 		=> 'flag_private_use',
		                    '_type'     => 'checkbox',
		                    '_checkbox_value' 	=> '1',
		                    '_required' => false,
		                    '_help_text' => '<small>* कार्यालय, पर्यटन र निजी प्रयोजनमा मात्र प्रयोग हुने सवारी साधनको तथा एम्बुलेन्स र शववाहनको ब्यापक बीमा गर्दा शरुु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।<br/>** निजी प्रयोेजनको लागि प्रयोग गर्ने सवारी साधन तथा दमकलको ब्यापक बीमा गर्दा शुरु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।</small>'
		                ],

		                // Pay for Towing
						[
		                    'field' => 'extra_fields[flag_towing]',
		                    'label' => 'Towing (दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा)',
		                    'rules' => 'trim|integer|in_list[1]',
		                    '_key' 		=> 'flag_towing',
		                    '_type'     => 'checkbox',
		                    '_checkbox_value' 	=> '1',
		                    '_required' => false
		                ]
					];
					$validation_rules = array_merge($__common_validation_rules, $extra_rules, [$rule_stamp_duty]);
					break;

				default:
					# code...
					break;
			}

			return  [
				'validation_rules' 	=> $validation_rules,
				'tariff_record' 	=> $tariff_record
			];
		}

	// --------------------------------------------------------------------


}