<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Agriculture Cattle Portfolio Helper Functions
 *
 * This file contains helper functions related to Agriculture's Cattle Sub-Portfolio.
 *
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		Agriculture
 * @sub-portfolio 	Cattle
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_AGR_CATTLE_row_snippet'))
{
	/**
	 * Get Policy Object - Row Snippet
	 *
	 * Row Partial View for  Object
	 *
	 * @param object $record Policy Object
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_AGR_CATTLE_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_agr_cattle', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_select_text'))
{
	/**
	 * Get Policy Object -  - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_AGR_CATTLE_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$cattle_type 	= $attributes->cattle_type;

		$snippet = [
			'<strong>' . $cattle_type . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_validation_rules'))
{
	/**
	 * Get Policy Object -  - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_AGR_CATTLE_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$keep_type_dropdown = _OBJ_AGR_CATTLE_keep_type_dropdown(FALSE);
		$type_dropdown 		= _OBJ_AGR_CATTLE_type_dropdown(FALSE);
		$ownership_dropdown = _OBJ_AGR_CATTLE_ownership_dropdown(false);
		$yesno_dropdown 	= _FLAG_yes_no_dropdwon(false);

		$v_rules = [

		    /**
		     * Cattle Items Details
		     */
		    'items' => [
			    [
			        'field' => 'object[items][name][]',
			        '_key' => 'name',
			        'label' => 'नाम',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][breed][]',
			        '_key' => 'breed',
			        'label' => 'जात',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][age][]',
			        '_key' => 'age',
			        'label' => 'उमेर',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][height][]',
			        '_key' => 'height',
			        'label' => 'उचाइ',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][color][]',
			        '_key' => 'color',
			        'label' => 'रंग',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
                    'field' => 'object[items][keep_type][]',
                    '_key' => 'keep_type',
                    'label' => 'पालिएको तरिका',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys($keep_type_dropdown)).']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $keep_type_dropdown,
                    '_show_label' 	=> false,
                    '_required' => true
                ],
                [
			        'field' => 'object[items][sign][]',
			        '_key' => 'sign',
			        'label' => 'संकेत पट्टा',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][health_stat][]',
			        '_key' => 'health_stat',
			        'label' => 'हालको स्वास्थ्य स्थिति',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][sum_insured][]',
			        '_key' => 'sum_insured',
			        'label' => 'बीमांक रकम(रु)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
		    ],

		    /**
			 * Basic Data
			 */
			'basic' =>[
				[
                    'field' => 'object[cattle_type]',
                    '_key' => 'cattle_type',
                    'label' => 'पशुधनको किसिम',
                    'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($type_dropdown)).']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $type_dropdown,
                    '_required' => true
                ],
                [
                    'field' => 'object[cattle_usage]',
                    '_key' => 'cattle_usage',
                    'label' => 'पशुधनको प्रयोजन(दूध/मासु/प्रजनन/पशुश्रम/ऊन)',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type'     => 'text',
                    '_required' => true
                ],
				[
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'पशुधन पालिएको गोठको वास्तविक ठेगाना',
			        'rules' => 'trim|required|htmlspecialchars|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
				[
			        'field' => 'object[farmhouse_structure]',
			        '_key' => 'farmhouse_structure',
			        'label' => 'पशुधन राखिने गोठको बनवटको विवरण',
			        'rules' => 'trim|required|htmlspecialchars|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[cattle_disease]',
			        '_key' => 'cattle_disease',
			        'label' => 'पशुधनमा लागेको रोगको विवरण',
			        'rules' => 'trim|htmlspecialchars|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => false
			    ],
				[
                    'field' => 'object[flag_ownership]',
                    '_key' => 'flag_ownership',
                    'label' => 'पशुधनको स्वामित्व',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys($ownership_dropdown)).']',
                    '_type'     => 'radio',
                    '_data'     => $ownership_dropdown,
                    '_show_label' 	=> true,
                    '_required' => true
                ],
				[
			        'field' => 'object[partner_details]',
			        '_key' => 'partner_details',
			        'label' => 'साझेदारको विवरण (नाम र ठेगाना)',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
                    'field' => 'object[flag_investment]',
                    '_key' => 'flag_investment',
                    'label' => 'बैंक वा वित्त कम्पनी वा सहकारीले लगानी गरेको छ?',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys($yesno_dropdown)).']',
                    '_type'     => 'radio',
                    '_data'     => $yesno_dropdown,
                    '_show_label' 	=> true,
                    '_required' => true
                ],
				[
			        'field' => 'object[invester_details]',
			        '_key' => 'invester_details',
			        'label' => 'लगानीकर्ताको विवरण (नाम र ठेगाना)',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[investment_amount]',
			        '_key' => 'investment_amount',
			        'label' => 'लिएको ऋणको रकम(रू.)',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => false
			    ]
		    ],

		    /**
		     * Facilities
		     */
		    'facilities' => [
		    	[
			        'field' => 'object[fclt_govt]',
			        '_key' => 'fclt_govt',
			        'label' => 'सरकारी कृषि सेवा केन्द्र',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_private]',
			        '_key' => 'fclt_private',
			        'label' => 'निजी कृषि सेवा केन्द्र',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_distance]',
			        '_key' => 'fclt_distance',
			        'label' => 'कृषि सेवा केन्द्रबाट पशुधन राखिने गोठसम्मको अन्दाजी दूरी',
			        'rules' => 'trim|htmlspecialchars|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_inspection_report]',
			        '_key' => 'fclt_inspection_report',
			        'label' => 'सरकारी वा निजी कृषि प्राविधकद्वारा बीमित पशुहरूलाई गरिने चेकजाँचको विवरण',
			        'rules' => 'trim|htmlspecialchars|max_length[500]',
			        '_type'     => 'textarea',
			        'rows' 		=> 4,
			        '_required' => false
			    ],
		    ],

		    /**
		     * Damage or Loss Details
		     */
		    'damages' => [
			    [
			        'field' => 'object[damages][year][]',
			        '_key' => 'year',
			        'label' => 'वर्ष',
			        'rules' => 'trim|htmlspecialchars|max_length[40]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[damages][reason][]',
			        '_key' => 'reason',
			        'label' => 'मृत्युको कारण',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[damages][quantity][]',
			        '_key' => 'quantity',
			        'label' => 'मृत्यु भएको परिमाण',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
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

if ( ! function_exists('_OBJ_AGR_CATTLE_type_dropdown'))
{
	/**
	 * Get Cattle Type Dropdown
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_AGR_CATTLE_type_dropdown( $flag_blank_select = true )
	{
		$CI =& get_instance();

		$CI->load->model('tariff_agriculture_model');

		$dropdown = $CI->tariff_agriculture_model->type_dropdown($CI->current_fiscal_year->id, IQB_SUB_PORTFOLIO_AGR_CATTLE_ID);

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_keep_type_dropdown'))
{
	/**
	 * Get Cattle Ownership Dropdown
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_AGR_CATTLE_keep_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['B' => 'बँधुवा', 'C' => 'चरन'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_ownership_dropdown'))
{
	/**
	 * Get Cattle Ownership Dropdown
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_AGR_CATTLE_ownership_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['S' => 'एकल स्वामित्व', 'J' => 'साझेदारी'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object -  Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @return float
	 */
	function _OBJ_AGR_CATTLE_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * Sum up all the item's sum insured amount to get the total Sum Insured
		 * Amount
		 */
		$items_sum_insured 	= $data['items']['sum_insured'] ?? [];
		$amt_sum_insured 	= 0.00;

		foreach($items_sum_insured as $si_per_item)
		{
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$amt_sum_insured +=  $si_per_item;
		}

		// NO SI Breakdown for this Portfolio
		return ['amt_sum_insured' => $amt_sum_insured];
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_AGR_CATTLE_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for  Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_AGR_CATTLE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [
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

if ( ! function_exists('_TXN_AGR_CATTLE_premium_goodies'))
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
	 *
	 * @return	array
	 */
	function _TXN_AGR_CATTLE_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Tariff Configuration for this Portfolio
		$CI->load->model('tariff_agriculture_model');
		$tariff_record = $CI->tariff_agriculture_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

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
			$message .= '<br/><br/>Portfolio: <strong>CATTLE</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_AGR_CATTLE_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_CATTLE_tariff_by_type'))
{
	/**
	 * Get Tariff for supplied cattle type
	 *
	 * @param alpha $cattle_code 	Cattle Type Code
	 * @return	Object
	 */
	function _OBJ_AGR_CATTLE_tariff_by_type( $cattle_code )
	{
		$CI =& get_instance();

		$CI->load->model('tariff_agriculture_model');
		$tariff_record = $CI->tariff_agriculture_model->get_by_fy_portfolio($CI->current_fiscal_year->id, IQB_SUB_PORTFOLIO_AGR_CATTLE_ID);
		$tariff     	= json_decode($tariff_record->tariff ?? '[]');
		$valid_tariff 	= NULL;

		foreach($tariff as $single_tariff)
		{
			if(strtoupper($single_tariff->code) == strtoupper($cattle_code))
			{
				$valid_tariff = $single_tariff;
				break;
			}
		}

		if( !$valid_tariff)
		{
			throw new Exception("Exception [Helper: ph_agr_cattle_helper][Method: _OBJ_AGR_CATTLE_tariff_by_type()]: No Tariff found for supplied cattle ({$cattle_code})");
		}

		return $valid_tariff;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_AGR_CATTLE'))
{
	/**
	 * Update Policy Premium Information - AGRICULTURE - CATTLE
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_AGR_CATTLE($policy_record, $endorsement_record)
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
			 * Tariff Record
			 */
			try {

				$tariff = _OBJ_AGR_CATTLE_tariff_by_type($object_attributes->cattle_type);

			} catch (Exception $e) {

				return $CI->template->json([
                    'status'        => 'error',
                    'title' 		=> 'Exception Occured',
                    'message' 	=> $e->getMessage()
                ], 404);
			}


			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_AGR_CATTLE_premium_validation_rules($policy_record, $pfs_record, $old_object, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data = $CI->input->post();

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
					 * Get the NET Sum Insured Amount
					 */
					$SI = _OBJ_si_net($old_object, $new_object);

					/**
					 * Extract Information from Object
					 * 	A. Sum Insured Amount
					 */
					// $SI = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount

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
					$B 						= 0.00;
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
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
					$BASIC_PREMIUM = $C - $D;
					$cost_calculation_table[] = [
						'label' => "ङ. जम्मा (ग - घ)",
						'value' => $BASIC_PREMIUM
					];


					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $BASIC_PREMIUM,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_pool_premium' 		=> 0.00,
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

					/**
					 * Compute VAT
					 *
					 * NOTE: On premium refund, we should also be refunding VAT
					 */
					$taxable_amount = $post_data['amt_stamp_duty']; // Vat applies only for Ticket
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);

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
						 * NOTE:
						 * In this portfolio, we have to pay vat for stamp duty if any.
						 * So there is no vat return in this case.
						 */
					}

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
						'txn_date' 				=> date('Y-m-d')
					]);




					// /**
					//  * Compute VAT
					//  */
					// $taxable_amount = $post_data['amt_stamp_duty']; // Vat applies only for Ticket
					// $CI->load->helper('account');
					// $amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					// /**
					//  * Prepare Transactional Data
					//  */
					// $txn_data = [
					// 	'amt_total_premium' 	=> $BASIC_PREMIUM,
					// 	'amt_pool_premium' 		=> 0.00,
					// 	'amt_commissionable'	=> $commissionable_premium,
					// 	'amt_agent_commission'  => $agent_commission,
					// 	'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
					// 	'amt_vat' 				=> $amount_vat,
					// 	'txn_details' 			=> $post_data['txn_details'],
					// 	'remarks' 				=> $post_data['remarks'],
					// ];


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



