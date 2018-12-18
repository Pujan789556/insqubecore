<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Portfolio Helper Functions
 *
 * This file contains helper functions related to Cash-in-Safe Portfolio
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_CS_row_snippet'))
{
	/**
	 * Get Policy Object - Row Snippet
	 *
	 * Row Partial View for Object
	 *
	 * @param object $record Policy Object )
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MISC_CS_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_cs', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_CS_select_text'))
{
	/**
	 * Get Policy Object - FIRE - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MISC_CS_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

		$snippet = [
			'<strong>' . $attributes->risk_locaiton . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_CS_validation_rules'))
{
	/**
	 * Get Policy Object - FIRE - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MISC_CS_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'प्रयोग हुने स्थान',
			        'rules' => 'trim|required|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured]',
			        '_key' => 'sum_insured',
			        'label' => 'बिमांक रकम (रु)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[excess]',
			        '_key' => 'excess',
			        'label' => 'अधिक',
			        'rules' => 'trim|required|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_default' 	=> '1. 10 % of claim amount subject to minimum of Rs. 50000 for RSMDST claims.' . PHP_EOL .
			        				'2. 10% of claim amount subject to minimum of Rs. 25000 for Normal claims.',
			        '_required' => false
			    ],
		    ]
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

if ( ! function_exists('_OBJ_MISC_CS_compute_sum_insured_amount'))
{
	/**
	 * Compute Sum Insured Amount of Policy Object - FIRE Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_MISC_CS_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * There is a direct field to supply sum_insured amount
		 */
		$amt_sum_insured = floatval($data['sum_insured']);

		// NO SI Breakdown for this Portfolio
		return ['amt_sum_insured' => $amt_sum_insured];
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_CS_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for FIRE Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_MISC_CS_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{
		$validation_rules = [

			/**
			 * Base Premium
			 */
			'premium' => [
                [
	                'field' => 'premium[risk]',
	                'label' => 'Risk Name',
	                'rules' => 'trim|required|alpha_numeric|max_length[20]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[rate]',
	                'label' => 'Rate (Rs Per Thousand)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
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
			$rules = $validation_rules['basic'];

			/**
			 * Premium Validation Rules
			 */
			$premium_elements 	= $validation_rules['premium'];

			// Loop through each portfolio risks
			foreach($portfolio_risks as $risk_id=>$risk_name)
			{
				foreach ($premium_elements as $elem)
                {
                	$elem['field'] .= "[{$risk_id}]";
                	$rules[] = $elem;
                }
			}
			return $rules;
		}
		return $validation_rules;

	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_CS_premium_goodies'))
{
	/**
	 * Get Policy Endorsement Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 * @param object $portfolio_risks Portfolio Risks
	 *
	 * @return	array
	 */
	function _TXN_MISC_CS_premium_goodies($policy_record, $policy_object, $portfolio_risks)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_CS_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_CS'))
{
	/**
	 * Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_MISC_CS($policy_record, $endorsement_record)
	{
		$CI =& get_instance();

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $CI->input->post() )
		{
			/**
			 * Policy Object Record - Latest
			 */
			$policy_object 	= 	_OBJ__get_latest(
									$policy_record->object_id,
									$endorsement_record->txn_type,
									$endorsement_record->audit_object
								);

			$object_attributes  = json_decode($policy_object->attributes);

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			/**
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);
			if(!$portfolio_risks)
			{
				return $CI->template->json([
					'status' 	=> 'error',
					'title' 	=> "Portfolio Risks Missing (MISC - Cash in Safe)!",
					'message' 	=> 'Please setup portifolio risks from Setup.<br/>Contact Administrator for further support.'
				], 404);
			}

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MISC_CS_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data 		= $CI->input->post();
				$post_premium 	= $post_data['premium'];

				/**
				 * Do we have a valid method?
				 */
				try{

					/**
					 * Portfolio Risks Rows
					 */
					$portfolio_risks = $CI->portfolio_model->portfolio_risks($policy_record->portfolio_id);

					/**
					 * Sum Insured
					 */
					$SI = floatval($policy_object->amt_sum_insured);

					/**
					 * Initialization of Computational Variables
					 */
					$BASE_PREMIUM  	= 0.00; // Gross Premium (Without Pool Premium)
					$POOL_PREMIUM  	= 0.00; // Pool Premium
					$risk_table 	= [];


					/**
					 * -------------------------------------------------------------------------------------
					 * COMPUTE GROSS & POOL PREMIUM
					 * -------------------------------------------------------------------------------------
					 */
					// Compute Base and Pool Premium
					foreach($portfolio_risks as $pr)
					{
						// Rate in Per Thousand
						$rate = floatval($post_premium['rate'][$pr->code]);

						if($rate)
						{
							$premium = $SI * $rate / 1000.00;

							// Assign to Pool or Base based on Risk Type
							if( $pr->type == IQB_RISK_TYPE_BASIC )
							{
								$BASE_PREMIUM += $premium;
							}
							else
							{
								$POOL_PREMIUM += $premium;
							}

							$risk_table[] 		= [$pr->name_np, $rate, $premium];
						}
					}


					// A =  SI X Default Rate
					$A = $BASE_PREMIUM;
					$cost_table[] = [
						'label' => "बिमा शुल्क",
						'value' => $A
					];

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on Basic Premium
					 */
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "प्रत्यक्ष छूट ({$dd_formatted}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}


					// Pool Premium
					$cost_table[] = [
						'label' => "हुलदंगा/आतंकवाद/द्वेष्पूर्ण बिमा शुल्क",
						'value' => $POOL_PREMIUM
					];

					// Net Premium
					$NET_BASIC_PREMIUM = $A - $direct_discount;
					$cost_table[] = [
						'label' => "कुल बिमा शुल्क",
						'value' => $NET_BASIC_PREMIUM + $POOL_PREMIUM
					];

					// --------------------------------------------------------------------------------------------

					$cost_calculation_table = [
						'cost_table' 		=> $cost_table,
						'risk_table'		=> $risk_table
					];


					// -----------------------------------------------------------------------------


					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'gross_amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
						'gross_amt_commissionable'	=> $commissionable_premium,
						'gross_amt_agent_commission'  => $agent_commission,
						'gross_amt_direct_discount' 	=> $direct_discount,
						'gross_amt_pool_premium' 		=> $POOL_PREMIUM,
					];


					// -----------------------------------------------------------------------------

					/**
					 * SAVE PREMIUM
					 * --------------
					 */
					return $CI->endorsement_model->save_premium(
								$endorsement_record,
								$policy_record,
								$premium_data,
								$post_data,
								$cost_calculation_table
							);

				} catch (Exception $e){

					return $CI->template->json([
							'status' => 'error',
							'title' => 'Exception Occured',
							'message' => $e->getMessage()
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