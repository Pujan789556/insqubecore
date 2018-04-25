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
		$si_basic 			= floatval($si_components['basic'] ?? 0.00);
		$si_cip 			= floatval($si_components['cip'] ?? 0.00);
		$si_cit 			= floatval($si_components['cit'] ?? 0.00);
		$amt_sum_insured 	= $si_basic + $si_cip + $si_cit;

		/**
		 * SI Breakdown
		 * 	- SI Basic
		 * 	- SI CIP
		 * 	- SI CIT
		 */
		$si_breakdown = json_encode([
			'si_basic' 	=> $si_basic,
			'si_cip' 	=> $si_cip,
			'si_cit' 	=> $si_cit,
		]);

		return ['amt_sum_insured' => $amt_sum_insured, 'si_breakdown' => $si_breakdown];
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
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
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
			'basic' => _ENDORSEMENT_premium_basic_v_rules( $policy_record->portfolio_id, $pfs_record ),

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => _POLICY_INSTALLMENT_validation_rules( $policy_record->portfolio_id, $pfs_record )
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
	 * Get Policy Endorsement Goodies
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
		$validation_rules = _TXN_MISC_BB_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );


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
	 * Banker's Blanket Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_MISC_BB($policy_record, $endorsement_record)
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
			 *
			 * In case of endorsements, we will be needing both current policy object and edited object information
			 * to compute premium.
			 */
			$old_object = get_object_from_policy_record($policy_record);
			$new_object = NULL;
			if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
			{
				try {
					$new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
				} catch (Exception $e) {

					return $CI->template->json([
	                    'status'        => 'error',
	                    'title' 		=> 'Exception Occured',
	                    'message' 	=> $e->getMessage()
	                ], 404);
				}
			}

			// Newest object attributes should be used.
			$object_attributes  = json_decode($new_object->attributes ?? $old_object->attributes);

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
			$validation_rules = _TXN_MISC_BB_premium_validation_rules($policy_record, $pfs_record, $old_object, $portfolio_risks, TRUE );
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
					 * Tariff Data
					 */
					$CI->load->model('tariff_misc_bb_model');
					$tariff_record = $CI->tariff_misc_bb_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);
					$tariff   	= json_decode($tariff_record->tariff ?? NULL);



					/**
					 * NET Sum Insured & Its Breakdown
					 *
					 *  A. Basic SI
					 * 	A. CIP SI (Cash in Primeses)
					 * 	B. CIT SI (Cash in Transit)
					 */
					$SI 			= _OBJ_si_net($old_object, $new_object);
					$SI_BREAKDOWN 	= _OBJ_si_breakdown_net($old_object, $new_object);
					$si_basic 	= $SI_BREAKDOWN['si_basic'];
					$si_cip 	= $SI_BREAKDOWN['si_cip'];
					$si_cit 	= $SI_BREAKDOWN['si_cit'];



					/**
					 * Compute Premium From Post Data
					 * ------------------------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Base Premium
					 * 	b. Other Risk Premium
					 *  c. Pool Premium
					 */

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
						'label' => "Basic Premium ({$rate_basic}%)",
						'value' => $premium_basic
					];

					// Additional Premium - Cash in Premises
					$cost_calculation_table[] = [
						'label' => "Additional Premium - Cash in Premises ({$rate_cip}%)",
						'value' => $premium_cip
					];

					// Additional Premium - Cash in Transit
					$cost_calculation_table[] = [
						'label' => "Additional Premium - Cash in Transit ({$rate_cit}%)",
						'value' => $premium_cit
					];


					// D = A + B + C
					$D = $premium_basic + $premium_cip + $premium_cit;


					// Forgery & Dishonesty
					// E = Forgery Rate X Total Staff
					$E =  intval($object_attributes->staff_count) * $forgery_dishonesty_rate;
					$cost_calculation_table[] = [
						'label' => "Forgery & Dishonesty (Rs. {$forgery_dishonesty_rate} per Staff)",
						'value' => $E
					];

					/**
					 * Additional Risks' Premium
					 */
					$additional_risk_premium = 0.00;
					foreach($portfolio_risks as $risk_id=>$risk_name)
					{
						$rate = $post_data['premium']['others']['rate'][$risk_id];

						// Compute only if rate is supplied
						if($rate)
						{
							$per_risk_premium = ( $rate * $SI ) / 100.00;
							$additional_risk_premium += $per_risk_premium;

							if($per_risk_premium)
							{
								$cost_calculation_table[] = [
									'label' => "$risk_name ({$rate}%)",
									'value' => $per_risk_premium
								];
							}
						}
					}

					$F = $additional_risk_premium;

					// G = D + E + F
					$G = $D + $E + $F;




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

						$cost_calculation_table[] = [
							'label' => "Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $G;
						$agent_commission 		= ( $G * $pfs_record->agent_commission ) / 100.00;
					}

					/**
					 * Pool Premium
					 */
					if($flag_pool_risk)
					{
						// Pool Premium = x% of Default Premium (A-B)
						$pool_rate = floatval($pfs_record->pool_premium);
						$POOL_PREMIUM = ( $SI * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "Pool Premium",
						'value' => $POOL_PREMIUM
					];

					$NET_BASIC_PREMIUM = $G;
					$cost_calculation_table[] = [
						'label' => "Total Premium",
						'value' => $NET_BASIC_PREMIUM + $POOL_PREMIUM
					];


					/**
					 * Premium Computation and Cost Calculation Table
					 */
					$premium_computation_table 	= json_encode($post_premium);
					$cost_calculation_table 	= json_encode($cost_calculation_table);

					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_pool_premium' 		=> $POOL_PREMIUM,
					];

					/**
					 * Perform Computation Basis for Endorsement
					 */
					if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
					{
						// Transaction Date must be set as today
						$endorsement_record->txn_date = date('Y-m-d');
						$premium_data = _ENDORSEMENT_apply_computation_basis($policy_record, $endorsement_record, $pfs_record, $premium_data );
					}


					if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
					{
						// We do not do anything here, because, VAT was applied only on Stamp Duty
						// For other portfolio, it must be set as -ve value

						/**
						 * !!! NO POOL PREMIUM !!!
						 *
						 * Pool premium is not refunded to customer.
						 * NULLify Pool Premium
						 */
						$premium_data['amt_pool_premium'] = 0.00;

						/**
						 * !!! VAT RETURN !!!
						 *
						 * We must also refund the VAT for as we refund the premium.
						 *
						 */
					}

					/**
					 * Compute VAT
					 *
					 * NOTE: On premium refund, we should also be refunding VAT
					 */
					$taxable_amount = $premium_data['amt_basic_premium'] + $premium_data['amt_pool_premium'] + $post_data['amt_stamp_duty'];
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Other Data
					 */
					$gross_amt_sum_insured 	= $new_object->amt_sum_insured ?? $old_object->amt_sum_insured;
					$net_amt_sum_insured 	= $SI;
					$txn_data = array_merge($premium_data, [
						'gross_amt_sum_insured' => $gross_amt_sum_insured,
						'net_amt_sum_insured' 	=> $net_amt_sum_insured,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $amount_vat,
						'txn_date' 				=> date('Y-m-d'),

						'premium_computation_table' => $premium_computation_table,	// JSON encoded
						'cost_calculation_table' 	=> $cost_calculation_table		// JSON encoded
					]);


					return $CI->endorsement_model->save($endorsement_record->id, $txn_data);

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



