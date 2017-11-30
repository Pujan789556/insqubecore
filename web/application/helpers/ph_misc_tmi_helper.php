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
 * @sub-portfolio 	Travel Medical Insurance (TMI)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_TMI_row_snippet'))
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
	function _OBJ_MISC_TMI_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_tmi', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_select_text'))
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
	function _OBJ_MISC_TMI_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $attributes->days . ' day(s)</strong>',
			'Plan Type: ' . '<strong>' . _OBJ_MISC_TMI_plan_type_dropdown(FALSE)[$attributes->plan_type] . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_validation_rules'))
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
	function _OBJ_MISC_TMI_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();
		$CI->load->model('tmi_plan_model');

		$plan_dropdown 		= $CI->tmi_plan_model->dropdown_children_tree();
		$plan_type_dropdwon = _OBJ_MISC_TMI_plan_type_dropdown(FALSE);
		$v_rules = [

			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[passport_no]',
			        '_key' => 'passport_no',
			        'label' => 'Passport No.',
			        'rules' => 'trim|required|max_length[40]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[dob]',
			        '_key' => 'dob',
			        'label' => 'Date of Birth',
			        'rules' => 'trim|required|valid_date',
			        '_type'     => 'date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[plan_id]',
			        '_key' => 'plan_id',
			        'label' => 'Plan',
			        'rules' => 'trim|required|integer|max_length[8]',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $plan_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[plan_type]',
			        '_key' => 'plan_type',
			        'label' => 'Plan Type',
			        'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($plan_type_dropdwon)).']',
			        '_type'     => 'radio',
			        '_data' 	=> $plan_type_dropdwon,
			        '_required' => true
			    ]
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

if ( ! function_exists('_OBJ_MISC_TMI_plan_type_dropdown'))
{
	/**
	 * Get Plan Type Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_TMI_plan_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'M' => 'Medical Only',
			'P' => 'Package Policy'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_plan_dropdown'))
{
	/**
	 * Get Plan Dropdown (All Sub-plans)
	 *
	 * This method is used to render data on object popup
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_TMI_plan_dropdown( $flag_blank_select = true )
	{
		$CI =& get_instance();
		$CI->load->model('tmi_plan_model');

		$dropdown = $CI->tmi_plan_model->dropdown_children();

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object
	 *
	 * !!!NOTE: TMI DOES NOT HAVE SUM INSURANCE FIELD
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @return float
	 */
	function _OBJ_MISC_TMI_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$amt_sum_insured = 0.00;

		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_TMI_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for MISCELLANEOUS Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_MISC_TMI_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );


		$validation_rules = [
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
			$rules_formatted = [];
			foreach ($validation_rules as $section=> $section_rules)
			{
				$rules_formatted = array_merge($rules_formatted, $section_rules);
			}
			return $rules_formatted;
		}
		return $validation_rules;

	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_TMI_premium_goodies'))
{
	/**
	 * Get Policy Policy Transaction Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 *
	 * @return	array
	 */
	function _TXN_MISC_TMI_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		$attributes = json_decode($policy_object->attributes);

		// Tariff Configuration for this Portfolio
		$CI->load->model('tmi_plan_model');
		$tariff_record = $CI->tmi_plan_model->find( (int)$attributes->plan_id );

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
			$message .= '<br/><br/>Portfolio: <strong>Travel Medical Insurance</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_TMI_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_tariff_rate'))
{
	/**
	 * Get Tariff Rate for supplied travel days, and insurer age
	 *
	 * @param object $tariff_record
	 * @param integer $tmi_days
	 * @param integer $age
	 * @param bool $convert_forex
	 * @return float
	 */
	function _OBJ_MISC_TMI_tariff_rate( $tariff_record, $plan_type, $tmi_days, $age, $convert_forex = true )
	{
		$CI =& get_instance();

		// Get Tariffs
		$tariffs = json_decode ( $plan_type == 'M' ? $tariff_record->tariff_medical : $tariff_record->tariff_package);
		$rate 	= 0.00;

		foreach($tariffs as $single)
		{
			if(  $tmi_days >= $single->day_min && $tmi_days <= $single->day_max )
			{
				$rate_column = '';

				// Get the Age Band
				if( $age >= 5 && $age <= 40 )
				{
					$rate_column = 'age_5_40_rate';
				}
				else if( $age >= 41 && $age <= 60 )
				{
					$rate_column = 'age_41_60_rate';
				}
				else if( $age >= 61 && $age <= 70 )
				{
					$rate_column = 'age_61_70_rate';
				}

				if($rate_column)
				{
					$rate = floatval($single->{$rate_column});
				}
				break;
			}
		}

		/**
		 * Forex Conversion?
		 */
		if( $convert_forex )
		{
			$CI->load->helper('forex');
			$rate = forex_conversion(date('Y-m-d'), 'USD', $rate);
		}

		return $rate;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_TMI_tariff_benefits'))
{
	/**
	 * Get Schedule of Benefit from Tariff for Given Policy Object
	 *
	 * @param integer $plan_id
	 * @return mixed
	 */
	function _OBJ_MISC_TMI_tariff_benefits( $plan_id )
	{
		$CI =& get_instance();

		/**
		 * Tariff Data
		 */
		$CI->load->model('tmi_plan_model');
		$tariff_record 	= $CI->tmi_plan_model->find( (int)$plan_id );


		$benefits = NULL;

		if( $tariff_record )
		{
			$benefits = $tariff_record->benefits;
		}

		return $benefits;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_TMI'))
{
	/**
	 * Expedition Personnel Accident Portfolio : Save a Policy Transaction Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_MISC_TMI($policy_record, $txn_record)
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
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MISC_TMI_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
            $CI->form_validation->set_rules($validation_rules);


			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data = $CI->input->post();

				try{

					// Object Attributes
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

					/**
					 * Tariff Data
					 */
					$CI->load->model('tmi_plan_model');
					$tariff_record 	= $CI->tmi_plan_model->find( (int)$object_attributes->plan_id );
					$tariff_json 	= $object_attributes->plan_type == 'M' ? $tariff_record->tariff_medical : $tariff_record->tariff_package;
					$tariff   		= json_decode($tariff_json ?? NULL);

					if( !$tariff )
					{
						return $CI->template->json([
							'status' 	=> 'error',
							'message' 	=> 'No Tariff Information Found for selected TMI Plan.'
						], 404);
					}


					/**
					 * Compute Age in Years and Rate (in NRS which is Premium)
					 */
					$policy_duration = date_difference($policy_record->start_date, $policy_record->end_date, 'd');

					$age 	= date_difference($object_attributes->dob, date('Y-m-d'), 'y');
					$RATE 	= _OBJ_MISC_TMI_tariff_rate( $tariff_record, $object_attributes->plan_type, $policy_duration, $age );



					/**
					 * Direct Discount or Agent Commission?, Pool Premium
					 * --------------------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					$PREMIUM_TOTAL 			= $RATE;
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$POOL_PREMIUM 			= 0.00;
					$direct_discount 		= 0.00;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$direct_discount = ( $PREMIUM_TOTAL * $pfs_record->direct_discount ) / 100.00 ;
						$PREMIUM_TOTAL -= $direct_discount;
					}
					else
					{
						$commissionable_premium = $PREMIUM_TOTAL;
						$agent_commission 		= ( $PREMIUM_TOTAL * $pfs_record->agent_commission ) / 100.00;
					}



					/**
					 * Compute VAT
					 */
					$taxable_amount = $PREMIUM_TOTAL + $post_data['amt_stamp_duty'];
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Transactional Data
					 */
					$txn_data = [
						'amt_total_premium' 	=> $PREMIUM_TOTAL,
						'amt_pool_premium' 		=> $POOL_PREMIUM,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $amount_vat,
						'txn_details' 			=> $post_data['txn_details'],
						'remarks' 				=> $post_data['remarks']
					];


					/**
					 * Premium Computation Table
					 * -------------------------
					 * 	!!! No additional premium computation information.
					 */
					$txn_data['premium_computation_table'] = NULL;


					/**
					 * Cost Calculation Table
					 * !!! No cost calculation table
					 */
					$txn_data['cost_calculation_table'] = NULL;
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


