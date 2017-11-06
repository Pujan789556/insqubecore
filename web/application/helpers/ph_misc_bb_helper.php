<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube MISCELLANEOUS Portfolio Helper Functions
 *
 * This file contains helper functions related to MISCELLANEOUS Portfolio
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		MISCELLANEOUS
 * @sub-portfolio 	BANKER'S BLANKET(BB)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_BB_row_snippet'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Row Snippet
	 *
	 * Row Partial View for MISCELLANEOUS Object
	 *
	 * @param object $record Policy Object (MISCELLANEOUS)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MISC_BB_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_bb', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BB_select_text'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MISC_BB_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BB_validation_rules'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MISC_BB_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$v_rules = [

			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[staff_count]',
			        '_key' => 'staff_count',
			        'label' => 'No. of Staffs',
			        'rules' => 'trim|required|integer|max_length[8]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
				[
			        'field' => 'object[cash_in_atm]',
			        '_key' => 'cash_in_atm',
			        'label' => 'Cash in ATM (Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[excess_deductibles]',
			        '_key' => 'excess_deductibles',
			        'label' => 'Excess/Deductibles',
			        'rules' => 'trim|htmlspecialchars|max_length[500]',
			        '_type' => 'textarea',
			        'rows' 	=> 4,
			        '_required' 	=> false
			    ],
		    ],

		    /**
			 * Sum Insured Components
			 */
			'sum_insured' =>[
				[
			        'field' => 'object[sum_insured][basic]',
			        '_key' => 'basic',
			        'label' => 'Basic Sum Insured (Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[sum_insured][cip]',
			        '_key' => 'cip',
			        'label' => 'Cash in Premises (Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[sum_insured][cit]',
			        '_key' => 'cit',
			        'label' => 'Cash in Transit(Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],

		    ],
		];


		// return formatted?
		$fromatted_v_rules = [];
		if($formatted === TRUE)
		{
			foreach ($v_rules as $key=>$section)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $section);
			}
			return $fromatted_v_rules;
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BB_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - MISCELLANEOUS Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @param string 	$mode 	What to Compute [all|except_duty|duty_only]
	 * @param bool 		$forex_convert 	Convert sum insured into NPR
	 * @return float
	 */
	function _OBJ_MISC_BB_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$si_components 		= $data['sum_insured'];
		$amt_sum_insured 	= floatval($si_components['basic'] ?? 0.00) + floatval($si_components['cip'] ?? 0.00) + floatval($si_components['cit'] ?? 0.00);

		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_BB_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for MISCELLANEOUS Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_MISC_BB_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );


		$validation_rules = [

			/**
			 * Premium Validation Rules - Basic
			 */
			'premium' => [

	            [
                    'field' => 'premium[flag_pool_risk]',
                    'label' => 'Pool Risk',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_pool_risk',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
                ],
                [
	                'field' => 'premium[forgery_dishonesty]',
	                'label' => 'Forgery & Dishonesty (Per Staff in Rs)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_default' 	=> '10',
	                '_key' 		=> 'forgery_dishonesty',
	                '_required' => true
	            ],
			],

			/**
			 * Premium Validation Rules - Additional Risk List (Like Fire)
			 */
			'premium_others' => [
                [
	                'field' => 'premium[others][risk]',
	                'label' => 'Rate',
	                'rules' => 'trim|required|integer|max_length[8]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[others][rate]',
	                'label' => 'Rate',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
	                '_required' => true
	            ]
			],

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => [
				[
	                'field' => 'amt_stamp_duty',
	                'label' => 'Stamp Duty(Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_default' 	=> $pfs_record->stamp_duty,
	                '_required' => true
	            ],
				[
	                'field' => 'txn_details',
	                'label' => 'Details/सम्पुष्टि विवरण',
	                'rules' => 'trim|required|htmlspecialchars',
	                '_type'     => 'textarea',
	                '_id'		=> 'txn-details',
	                '_required' => true
	            ],
	            [
                    'field' => 'template_reference',
                    'label' => 'Load from endorsement templates',
                    'rules' => 'trim|integer|max_length[8]',
                    '_key' 		=> 'template_reference',
                    '_id'		=> 'template-reference',
                    '_type'     => 'dropdown',
                    '_data' 	=> IQB_BLANK_SELECT + $template_dropdown,
                    '_required' => false
                ],
	            [
	                'field' => 'remarks',
	                'label' => 'Remarks/कैफियत',
	                'rules' => 'trim|htmlspecialchars',
	                '_type'     => 'textarea',
	                '_required' => false
	            ]
			]
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$formatted_rules = array_merge( $validation_rules['basic'], $validation_rules['premium']);

			/**
			 * Premium Validation Rules
			 */
			$other_premium_elements 	= $validation_rules['premium_others'];
			$object_attributes 	= $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;


			// Loop through each portfolio risks
			foreach($portfolio_risks as $risk_id=>$risk_name)
			{
				foreach ($other_premium_elements as $elem)
                {
                	$elem['field'] .= "[{$risk_id}]";
                	$formatted_rules[] = $elem;
                }
			}
			return $formatted_rules;
		}

		return $validation_rules;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_BB_premium_goodies'))
{
	/**
	 * Get Policy Policy Transaction Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 * 		2. Tariff Record if Applies
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 * @param array $portfolio_risks Portfolio Risks
	 *
	 * @return	array
	 */
	function _TXN_MISC_BB_premium_goodies($policy_record, $policy_object, $portfolio_risks)
	{
		$CI =& get_instance();

		// Tariff Configuration for this Portfolio
		$CI->load->model('tariff_misc_bb_model');
		$tariff_record = $CI->tariff_misc_bb_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

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
			$message 	= 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
			$title 		= 'Tariff Not Active!';
			$__flag_valid_tariff = FALSE;
		}

		if( !$__flag_valid_tariff )
		{
			$message .= '<br/><br/>Portfolio: <strong>Banker\'s Blanket</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_BB_premium_validation_rules( $policy_record, $pfs_record, $tariff_record, $portfolio_risks );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_BB'))
{
	/**
	 * Banker's Blanket Portfolio : Save a Policy Transaction Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_MISC_BB($policy_record, $txn_record)
	{
		$CI =& get_instance();

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $CI->input->post() )
		{

			/**
			 * Policy Object Record
			 */
			$policy_object 		= get_object_from_policy_record($policy_record);

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			/**
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MISC_BB_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data = $CI->input->post();

				try{

					/**
					 * Post Premium Data
					 */
					$post_premium  = $post_data['premium'];


					/**
					 * Sum Insured Amount
					 */
					$object_attributes  	= $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$object_amt_sum_insured = $policy_object->amt_sum_insured;


					/**
					 * Tariff Data
					 */
					$CI->load->model('tariff_misc_bb_model');
					$tariff_record = $CI->tariff_misc_bb_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);
					$tariff   	= json_decode($tariff_record->tariff ?? NULL);

					/**
					 * Compute Premium From Post Data
					 * ------------------------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Base Premium
					 * 	b. Other Risk Premium
					 *  c. Pool Premium
					 */

					// Basic, Cash in Primeses, Cash in Transit - SI
					$si_basic 	= floatval($object_attributes->sum_insured->basic ?? 0.00);
					$si_cip 	= floatval($object_attributes->sum_insured->cip ?? 0.00);
					$si_cit 	= floatval($object_attributes->sum_insured->cit ?? 0.00);

					// Tariff Rate for those SI
					$rate_basic = floatval($tariff->basic ?? 0.00);
					$rate_cip 	= floatval($tariff->cip ?? 0.00);
					$rate_cit 	= floatval($tariff->cit ?? 0.00);

					// Forgery & Dishonesty
					$forgery_dishonesty_rate 	= $post_premium['forgery_dishonesty'];
					$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


					$premium_basic 	= ( $si_basic * $rate_basic ) / 100.00;
					$premium_cip 	= ( $si_cip * $rate_cip ) / 100.00;
					$premium_cit 	= ( $si_cit * $rate_cit ) / 100.00;

					// Basic Premium
					$cost_calculation_table[] = [
						'label' => "A. Basic Premium Rate ({$rate_basic}%)",
						'value' => $premium_basic
					];

					// Additional Premium - Cash in Premises
					$cost_calculation_table[] = [
						'label' => "B. Additional Premium - Cash in Premises Rate ({$rate_cip}%)",
						'value' => $premium_cip
					];

					// Additional Premium - Cash in Transit
					$cost_calculation_table[] = [
						'label' => "C. Additional Premium - Cash in Transit Rate ({$rate_cit}%)",
						'value' => $premium_cit
					];


					// D = A + B + C
					$D = $premium_basic + $premium_cip + $premium_cit;
					$cost_calculation_table[] = [
						'label' => "D. (A+B+C)",
						'value' => $D
					];

					// Forgery & Dishonesty
					// E = Forgery Rate X Total Staff
					$E =  intval($object_attributes->staff_count) * $forgery_dishonesty_rate;
					$cost_calculation_table[] = [
						'label' => "E. Forgery & Dishonesty (Rs. {$forgery_dishonesty_rate} per Staff)",
						'value' => $E
					];

					/**
					 * Additional Risks' Premium
					 */
					$cost_calculation_table[] = [
						'label' => "<strong>Additional Risks</strong>",
						'value' => ''
					];
					$additional_risk_premium = 0.00;
					foreach($portfolio_risks as $risk_id=>$risk_name)
					{
						$rate = $post_data['premium']['others']['rate'][$risk_id];

						// Compute only if rate is supplied
						if($rate)
						{
							$per_risk_premium = ( $rate * $object_amt_sum_insured ) / 100.00;
							$additional_risk_premium += $per_risk_premium;

							$cost_calculation_table[] = [
								'label' => "$risk_name ({$rate}%)",
								'value' => $per_risk_premium
							];
						}
					}

					$F = $additional_risk_premium;
					$cost_calculation_table[] = [
						'label' => "F. Total Additional Risk Premium",
						'value' => $F
					];

					// G = E + F
					$G = $E + $F;
					$cost_calculation_table[] = [
						'label' => "G. (E + F)",
						'value' => $G
					];



					/**
					 * Direct Discount or Agent Commission?, Pool Premium
					 * --------------------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$POOL_PREMIUM 			= 0.00;
					$direct_discount 		= 0.00;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$direct_discount = ( $G * $pfs_record->direct_discount ) / 100.00 ;
						$G -= $direct_discount;
					}
					else
					{
						$commissionable_premium = $G;
						$agent_commission 		= ( $G * $pfs_record->agent_commission ) / 100.00;
					}

					$cost_calculation_table[] = [
						'label' => "H. Direct discount ({$pfs_record->direct_discount}%)",
						'value' => $direct_discount
					];

					/**
					 * Pool Premium
					 */
					if($flag_pool_risk)
					{
						// Pool Premium = x% of Default Premium (A-B)
						$pool_rate = floatval($pfs_record->pool_premium);
						$POOL_PREMIUM = ( $object_amt_sum_insured * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "I. Pool Premium",
						'value' => $POOL_PREMIUM
					];

					$NET_PREMIUM = $G + $POOL_PREMIUM;
					$cost_calculation_table[] = [
						'label' => "E. Net Premium",
						'value' => $NET_PREMIUM
					];

					/**
					 * Compute VAT
					 */
					$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Transactional Data
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
					return $CI->policy_txn_model->save($txn_record->id, $txn_data);


					/**
					 * @TODO
					 *
					 * 1. Build RI Distribution Data For This Policy
					 * 2. RI Approval Constraint for this Policy
					 */

				} catch (Exception $e){

					return $CI->template->json([
						'status' 	=> 'error',
						'message' 	=> $e->getMessage()
					], 404);
				}
        	}
        	else
        	{
        		return $CI->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Validation Error!',
					'message' 	=> validation_errors()
				]);
        	}
		}
	}
}


// ------------------------------------------------------------------------
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------



