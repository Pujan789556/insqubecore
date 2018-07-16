<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Agriculture Fish Portfolio Helper Functions
 *
 * This file contains helper functions related to Agriculture's Fish Sub-Portfolio.
 *
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		Agriculture
 * @sub-portfolio 	Fish(Pisciculture)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_AGR_FISH_row_snippet'))
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
	function _OBJ_AGR_FISH_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_agr_fish', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_FISH_select_text'))
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
	function _OBJ_AGR_FISH_select_text( $record )
	{
		$category_dropdown   = _OBJ_AGR_category_dropdown($record->portfolio_id);
		$attributes 		 = $record->attributes ? json_decode($record->attributes) : NULL;
		$bs_agro_category_id = $attributes->bs_agro_category_id ?? NULL;
		$category 		 = $category_dropdown[$bs_agro_category_id] ?? '';

		$snippet = [
			'<strong>' . $category . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_FISH_validation_rules'))
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
	function _OBJ_AGR_FISH_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$category_dropdown  = _OBJ_AGR_category_dropdown($portfolio_id);
		$ownership_dropdown = _OBJ_AGR_FISH_ownership_dropdown(false);
		$yesno_dropdown 	= _FLAG_yes_no_dropdown(false);

		$v_rules = [

		    /**
		     * Poultry Items Details
		     */
		    'items' => [
			    [
			        'field' => 'object[items][breed][]',
			        '_key' => 'breed',
			        'label' => 'जात',
			        'rules' => 'trim|required|integer|max_length[8]',
			        '_type' => 'dropdown',
			        '_class' => 'form-control breed-dropdown',
			        '_data' => IQB_BLANK_SELECT,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][rearing_technology][]',
			        '_key' => 'rearing_technology',
			        'label' => 'पालन प्रविधि',
			        'rules' => 'trim|required|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][area][]',
			        '_key' => 'area',
			        'label' => 'जलासयको क्षेत्रफल',
			        'rules' => 'trim|required|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][stock_no][]',
			        '_key' => 'stock_no',
			        'label' => 'स्टक गरेको माछाको संख्या',
			        'rules' => 'trim|required|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][living_rate][]',
			        '_key' => 'living_rate',
			        'label' => 'बाच्ने दर (%)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][avg_weight][]',
			        '_key' => 'avg_weight',
			        'label' => 'औसत तौल (ग्राम)',
			        'rules' => 'trim|required|integer|max_length[5]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][total_production][]',
			        '_key' => 'total_production',
			        'label' => 'कुल उत्पादन (के. जी.)',
			        'rules' => 'trim|required|integer|max_length[10]',
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
                    'field' => 'object[bs_agro_category_id]',
                    '_key' => 'bs_agro_category_id',
                    'label' => 'माछाको किसिम',
                    'rules' => 'trim|required|integer|in_list['.implode(',', array_keys($category_dropdown)).']',
                    '_type'     => 'dropdown',
                    '_id' 		=> 'bs_agro_category_id',
                    '_data'     => IQB_BLANK_SELECT + $category_dropdown,
                    '_show_label' 	=> true,
                    '_required' 	=> true
                ],
                [
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'माछा पालिएको स्थानको वास्तविक ठेगाना',
			        'rules' => 'trim|required|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],

			    [
			        'field' => 'object[fingerling_source]',
			        '_key' => 'fingerling_source',
			        'label' => 'माछा भुराको स्रोत',
			        'rules' => 'trim|required|max_length[250]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[fish_disease]',
			        '_key' => 'fish_disease',
			        'label' => 'माछामा लागेको रोगको विवरण',
			        'rules' => 'trim|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => false
			    ],
			    [
                    'field' => 'object[flag_insure_fish_pond]',
                    'label' => 'पोखरी/रेसवेको पनि बीमा गर्ने?',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_insure_fish_pond',
                    '_id' 		=> 'flag_insure_fish_pond',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
                ],
                [
			        'field' => 'object[fish_pond_sum_insured]',
			        '_key' => 'fish_pond_sum_insured',
			        'label' => 'पोखरी/रेसवेको मुल्य(रु)',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
				[
                    'field' => 'object[flag_ownership]',
                    '_key' => 'flag_ownership',
                    'label' => 'माछाको स्वामित्व',
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
			        'rules' => 'trim|max_length[300]',
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
			        'rules' => 'trim|max_length[300]',
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
			        'rules' => 'trim|max_length[300]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_private]',
			        '_key' => 'fclt_private',
			        'label' => 'निजी कृषि सेवा केन्द्र',
			        'rules' => 'trim|max_length[300]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_distance]',
			        '_key' => 'fclt_distance',
			        'label' => 'कृषि सेवा केन्द्रबाट माछा पालिएको स्थानसम्मको अन्दाजी दूरी',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[fclt_inspection_report]',
			        '_key' => 'fclt_inspection_report',
			        'label' => 'सरकारी वा निजी कृषि प्राविधकद्वारा बीमित माछाहरूलाई गरिने चेकजाँचको विवरण',
			        'rules' => 'trim|max_length[500]',
			        '_type'     => 'textarea',
			        'rows' 		=> 4,
			        '_required' => false
			    ],
		    ],

		    /**
		     * Nominee
		     */
		    'nominee' => [
		    	[
			        'field' => 'object[nominee_name]',
			        '_key' => 'nominee_name',
			        'label' => 'नाम थर',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[nominee_relation]',
			        '_key' => 'nominee_relation',
			        'label' => 'बिमितसँगको नाता',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[nominee_father]',
			        '_key' => 'nominee_father',
			        'label' => 'पिताको नाम',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[nominee_mother]',
			        '_key' => 'nominee_mother',
			        'label' => 'माताको नाम',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[nominee_contact]',
			        '_key' => 'nominee_contact',
			        'label' => 'संपर्क नं (मोबाईल / आवास)',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ]
		    ],

		    /**
		     * Damage or Loss Details
		     */
		    'damages' => [
			    [
			        'field' => 'object[damages][year][]',
			        '_key' => 'year',
			        'label' => 'वर्ष',
			        'rules' => 'trim|max_length[40]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[damages][reason][]',
			        '_key' => 'reason',
			        'label' => 'मृत्युको कारण',
			        'rules' => 'trim|max_length[300]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[damages][quantity][]',
			        '_key' => 'quantity',
			        'label' => 'नोक्सान भएको माछाको अनुमानित संख्या र तौल',
			        'rules' => 'trim|max_length[300]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
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

if ( ! function_exists('_OBJ_AGR_FISH_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Format Items
	 *
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_AGR_FISH_pre_save_tasks( array $data, $record )
	{
		$items = $data['object']['items'];

		$v_rules = _OBJ_AGR_FISH_validation_rules(IQB_SUB_PORTFOLIO_AGR_FISH_ID);
		$item_rules = $v_rules['items'];

		$items_formatted = [];
		$count = count($items['breed']);

		for($i=0; $i < $count; $i++)
		{
			$single = [];
			foreach($item_rules as $rule)
			{
				$key = $rule['_key'];
				$single[$key] = $items[$key][$i];
			}
			$items_formatted[] = $single;
		}

		$data['object']['items'] = $items_formatted;

		return $data;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_FISH_ownership_dropdown'))
{
	/**
	 * Get Poultry Ownership Dropdown
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_AGR_FISH_ownership_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_AGR_FISH_compute_sum_insured_amount'))
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
	function _OBJ_AGR_FISH_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * Items' Total Sum Insured Amount
		 */
		$amt_sum_insured = 0.00;

		$items = $data['items'];
		$si_items = _OBJ_AGR_FISH_items_only_sum_insured_amount($items);

		/**
		 * Do you want to insure fish pond too?
		 */
		$object_attributes 	= (object)$data;
		$si_pond 	= _OBJ_AGR_FISH_pond_sum_insured_amount($object_attributes);

		$amt_sum_insured = $si_items + $si_pond;

		$si_breakdown = json_encode([
			'si_items' => $si_items,
			'si_pond'  => $si_pond
		]);

		// With SI Breakdown
		return ['amt_sum_insured' => $amt_sum_insured, 'si_breakdown' => $si_breakdown];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_FISH_items_only_sum_insured_amount'))
{
	/**
	 * Get items's only Sum Insured Amount
	 *
	 * @param array  $items 	Item List
	 * @return float
	 */
	function _OBJ_AGR_FISH_items_only_sum_insured_amount( $items )
	{
		$amt_sum_insured 	= 0.00;

		foreach($items as $single)
		{
			$si_per_item = $single['sum_insured'];
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$amt_sum_insured +=  $si_per_item;
		}

		return $amt_sum_insured;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_FISH_pond_sum_insured_amount'))
{
	/**
	 * Get Fish Pond's Sum Insured Amount
	 *
	 * @param array  $object  Object attributes
	 * @return float
	 */
	function _OBJ_AGR_FISH_pond_sum_insured_amount( $object_attributes )
	{
		/**
		 * Do you want to insure fish pond too?
		 */
		$amt_sum_insured 		= 0.00;
		$flag_insure_fish_pond 	= intval($object_attributes->flag_insure_fish_pond ?? 0);
		if($flag_insure_fish_pond)
		{
			$amt_sum_insured = floatval($object_attributes->fish_pond_sum_insured ?? 0.00);
		}

		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_AGR_FISH_premium_validation_rules'))
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
	function _TXN_AGR_FISH_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [
			/**
			 * Premium Validation Rules
			 */
			'premium' => [
                [
	                'field' => 'premium[personal_accident]',
	                'label' => 'Personal Accident Premium Charge of Insured Party (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'personal_accident',
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

if ( ! function_exists('_TXN_AGR_FISH_premium_goodies'))
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
	function _TXN_AGR_FISH_premium_goodies($policy_record, $policy_object)
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
			$message .= '<br/><br/>Portfolio: <strong>FISH</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_AGR_FISH_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_AGR_FISH'))
{
	/**
	 * Update Policy Premium Information - AGRICULTURE - FISH
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_AGR_FISH($policy_record, $endorsement_record)
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
			 * !!! MANUAL PREMIUM COMPUTATION ENDORSEMENT !!!
			 *
			 * Manual Endorsement should be done on
			 * 	- Premium Upgrade
			 * 	- Premium Refund
			 */
			if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
			{
				return _ENDORSEMENT__save_premium_manual($endorsement_record->id, $pfs_record->agent_commission);
			}


			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_AGR_FISH_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data 			= $CI->input->post();
				$post_premium 		= $post_data['premium'];
				$personal_accident 	= $post_premium['personal_accident'];

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
					 * Get Object Attributes
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

					/**
					 * Pond SI, Premium Rates
					 */
					$pond_rate 		= 1; // 1%
					$POND_SI 		= floatval($object_attributes->fish_pond_sum_insured);


					/**
					 * Compute Premium for Per Breed
					 */
					$FISH_PREMIUM = 0.00;
					foreach($object_attributes->items as $single)
					{

						$tariff = _OBJ_AGR_tariff_by_type(IQB_SUB_PORTFOLIO_AGR_FISH_ID, $single->breed);
						$FISH_PREMIUM += ( floatval($tariff->rate) * floatval($single->sum_insured) ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "माछाको बीमा शुल्क ",
						'value' => $FISH_PREMIUM
					];


					// POND PREMIUM
					$POND_PREMIUM = 0.00;
					$flag_insure_fish_pond 	= intval($object_attributes->flag_insure_fish_pond ?? 0);
					if($flag_insure_fish_pond)
					{
						$POND_PREMIUM = ( $POND_SI * $pond_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "पोखरी/रेसवेको बीमा शुल्क",
						'value' => $POND_PREMIUM
					];


					// A = FISH_PREMIUM + POND_PREMIUM
					$A = $FISH_PREMIUM + $POND_PREMIUM;
					$cost_calculation_table[] = [
						'label' => "क. कुल बीमा शुल्क",
						'value' => $A
					];



					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on NET Premium
					 */
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= 0.00;
					$dd_formatted 			= 0.00;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);

					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}

					$cost_calculation_table[] = [
						'label' => "ख. प्रत्यक्ष छूट ({$dd_formatted}%)",
						'value' => $direct_discount
					];


					// C = A - Direct Discount
					$C = $A - $direct_discount;
					$cost_calculation_table[] = [
						'label' => "ग. छुट पछिको शुल्क (क - ख)",
						'value' => $C
					];


					// D = 75% of C
					$D = ($C * 75) / 100.00;
					$cost_calculation_table[] = [
						'label' => "घ. ग को ७५% ले हुन आउने रकम",
						'value' => $D
					];

					// E = C - D
					$E = $C - $D;
					$cost_calculation_table[] = [
						'label' => "ङ. जम्मा (ग - घ)",
						'value' => $E
					];

					// Stamp Duty
					$F = $post_data['amt_stamp_duty'];
					$cost_calculation_table[] = [
						'label' => "च. थप टिकट दस्तुर",
						'value' => $F
					];

					// G = E + F
					$G = $E + $F;
					$cost_calculation_table[] = [
						'label' => "छ. बिमितले तिर्नुपर्ने जम्मा बीमा शुल्क (ङ + च)",
						'value' => $G
					];

					// Personal Accident
					$H = floatval($personal_accident);
					$cost_calculation_table[] = [
						'label' => "ज. बिमितले दुर्घटना बीमा वपत तिर्नुपर्ने बीमा शुल्क",
						'value' => $H
					];

					// Basic Premium
					$I = $G + $H; // Stamp Duty is Saved Saperately
					$cost_calculation_table[] = [
						'label' => "झ. बिमितले तिर्नुपर्ने जम्मा बीमा शुल्क (छ + ज)",
						'value' => $I
					];

					// Basic Premium
					$BASIC_PREMIUM = $E + $H;

					/**
					 * !!! NO VAT  on AGRICULTURE PORTFOLIOS !!!
					 */
					$amount_vat = 0.00;

					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $BASIC_PREMIUM,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_direct_discount' 	=> $direct_discount,
						'amt_pool_premium' 		=> 0.00,
						'gross_amt_sum_insured' => $policy_object->amt_sum_insured,
						'net_amt_sum_insured' 	=> $policy_object->amt_sum_insured,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $amount_vat,
						'txn_date' 				=> date('Y-m-d')
					];



					/**
					 * Premium Computation Table
					 * -------------------------
					 */
					$premium_computation_table 					= json_encode($post_premium);
					$premium_data['premium_computation_table'] 	= $premium_computation_table;


					/**
					 * Cost Calculation Table
					 */
					$premium_data['cost_calculation_table'] = json_encode($cost_calculation_table);
					return $CI->endorsement_model->save($endorsement_record->id, $premium_data);

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



