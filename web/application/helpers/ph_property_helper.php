<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Property Portfolio Helper Functions
 *
 * This file contains helper functions related to Property Portfolio
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


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_compute_sum_insured_amount'))
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
	function _OBJ_PROPERTY_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$CI =& get_instance();

		$amt_sum_insured = 0.00;
		$items 			 = $data['items'];

		$si_list = [];
		foreach($items as $single)
		{
			$list = $single['list'];
			$per_property_si = 0;
			foreach($list as $key=>$attribs)
			{
				$si_per_item = $attribs['item_sum_insured'];
				// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
				$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$per_property_si +=  $si_per_item;
			}
			$amt_sum_insured +=  $per_property_si;
		}

		// NO SI Breakdown for this Portfolio
		return ['amt_sum_insured' => $amt_sum_insured];
	}
}






// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_PROPERTY_row_snippet'))
{
	/**
	 * Row Snippent
	 *
	 * @param object $record Policy Object
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_PROPERTY_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_property', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_select_text'))
{
	/**
	 * Get Policy Object - PROPERTY - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_PROPERTY_select_text( $record )
	{
		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];
		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_validation_rules'))
{
	/**
	 * Get Policy Object - PROPERTY - Validation Rules
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_PROPERTY_validation_rules( $portfolio_id = NULL, $formatted = FALSE )
	{
		$CI =& get_instance();

		$usage_type_dropdwon 	= _OBJ_PROPERTY_usage_type_dropdown(FALSE);
		$risk_class_dropdwon 	= _OBJ_PROPERTY_risk_class_dropdown(FALSE);
		$district_dropdown 		= district_dropdown( 'both', FALSE );

		$risk_category_dropdown = _PROPERTY_risk_category_dropdown(FALSE);
		$item_type_dropdwon 	= _OBJ_PROPERTY_item_type_dropdown(FALSE);

		$risk_codes 			= _OBJ_PROPERTY_risk_codes();

		$v_rules = [

			/**
		     * Property Details
		     */
		    'property_risk' => [
		    	[
			        'field' => 'object[items][risk_category][]',
			        '_key' => 'risk_category',
			        '_class' 	=> 'form-control risk_category',
			        'label' => 'Risk Category',
			        'rules' => 'trim|required|integer|max_length[11]|in_list['. implode(',', array_keys($risk_category_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $risk_category_dropdown,
			        '_show_label' 	=> true,
			        '_required' => true
			    ],

			    // This should be re-filled per item via ajax call on form load.
			    [
			        'field' => 'object[items][risk_code][]',
			        '_key' => 'risk_code',
			        '_class' 	=> 'form-control risk_code',
			        'label' => 'Risk Code',
			        'rules' => 'trim|required|max_length[20]',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT,
			        '_risk_codes' 	=> $risk_codes, // Required in Form
			        '_show_label' 	=> true,
			        '_required' => true
			    ]
		    ],

			/**
		     * Location Details
		     */
		    'property_location' => [

			    /**
			     * Address
			     */
		    	[
			        'field' => 'object[items][location_plot_no][]',
			        '_key' => 'location_plot_no',
			        'label' => 'Land Plot No.',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => false
			    ],
			    [
			        'field' => 'object[items][location_house_no][]',
			        '_key' => 'location_house_no',
			        'label' => 'House No.',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => false
			    ],
			    [
			        'field' => 'object[items][location_tole][]',
			        '_key' => 'location_tole',
			        'label' => 'Tole/Street Address',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => false
			    ],
			    [
			        'field' => 'object[items][location_district][]',
			        '_key' => 'location_district',
			        'label' => 'District',
			        'rules' => 'trim|required|numeric|max_length[2]|in_list['. implode(',', array_keys($district_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $district_dropdown,
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_vdc][]',
			        '_key' => 'location_vdc',
			        'label' => 'VDC / Municipality',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_ward_no][]',
			        '_key' => 'location_ward_no',
			        'label' => 'Ward No.',
			        'rules' => 'trim|required|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => true
			    ],

			    // ------------------------------------------------------
			    [
			        'field' => 'object[items][location_nature][]',
			        '_key' => 'location_nature',
			        'label' => 'Location Nature',
			        'rules' => 'trim|required|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_property_nature][]',
			        '_key' => 'location_property_nature',
			        'label' => 'Property Nature',
			        'rules' => 'trim|required|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_usage_type][]',
			        '_key' => 'location_usage_type',
			        'label' => 'Usage Type',
			        'rules' => 'trim|required|alpha|max_length[20]|in_list['. implode(',', array_keys($usage_type_dropdwon)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $usage_type_dropdwon,
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_storey_no][]',
			        '_key' => 'location_storey_no',
			        'label' => 'No. of Stories',
			        'rules' => 'trim|max_length[10]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => false
			    ],
			    [
			        'field' => 'object[items][location_owner_name][]',
			        '_key' => 'location_owner_name',
			        'label' => 'Owner Name',
			        'rules' => 'trim|required|max_length[300]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][location_risk_class][]',
			        '_key' => 'location_risk_class',
			        'label' => 'Risk Class',
			        'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode(',', array_keys($risk_class_dropdwon)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $risk_class_dropdwon,
			        '_show_label' 	=> true,
			        '_required' => true
			    ]
		    ],
		];


		/**
	     * Property List with Individual Sum Insurance
	     */
	    $items_v_rules = [];
		foreach($item_type_dropdwon as $key=>$label)
		{
			$single_rule = [
		    	[
			        'field' => "object[items][item_type][{$key}][]",
			        '_key' => 'item_type',
			        'label' => 'Item Type',
			        'rules' => 'trim|max_length[20]|in_list['. implode(',', array_keys($item_type_dropdwon)) .']',
			        '_type'     => 'hidden',
			        '_data' 	=> $item_type_dropdwon,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => "object[items][item_usage_type][{$key}][]",
			        '_key' => 'item_usage_type',
			        'label' => 'Usage Type',
			        'rules' => 'trim|alpha|max_length[20]|in_list['. implode(',', array_keys($usage_type_dropdwon)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $usage_type_dropdwon,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => "object[items][item_sum_insured][{$key}][]",
			        '_key' => 'item_sum_insured',
			        'label' => 'Sum Insured',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]|callback__cb_property_valid_sum_insured',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => "object[items][item_remarks][{$key}][]",
			        '_key' => 'item_remarks',
			        'label' => 'Remarks',
			        'rules' => 'trim|max_length[500]',
			        '_type'     	=> 'text',
			        'rows' 			=> 3,
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
		    ];
		    $items_v_rules["{$key}"] = $single_rule;
		}
		$v_rules['property_item_list'] = $items_v_rules;


		// return formatted?
		$fromatted_v_rules = [];
		if($formatted === TRUE)
		{
			$fromatted_v_rules = array_merge($v_rules['property_risk'], $v_rules['property_location']);
			foreach($item_type_dropdwon as $key=>$label)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $items_v_rules[$key]);
			}

			return $fromatted_v_rules;
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_risk_codes'))
{
	/**
	 * Object's Tariff Risks for all Risk Category
	 *
	 * @param bool $risk_category 	Risk Category
	 * @return	array
	 */
	function _OBJ_PROPERTY_risk_codes( $risk_category = NULL )
	{
		$CI =& get_instance();
		$CI->load->model('tariff_property_model');

		$risk_codes = [];
		if($risk_category)
		{
			$risk_codes[$risk_codes] = _PROPERTY_risk_dropdown($risk_category);
		}
		else
		{
			$risk_categories = $CI->tariff_property_model->get_all();
			foreach($risk_categories as $single)
			{
				$risk_codes[$single->id] = _PROPERTY_risk_dropdown($single->id);
			}
		}
		return $risk_codes;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('_PROPERTY_risk_category_dropdown'))
{
	/**
	 * Risk Category Dropdown
	 *
	 * @param string $lang 	both|en|np
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _PROPERTY_risk_category_dropdown($lang = 'both', $flag_blank_select = true )
	{
		$CI =& get_instance();
		$CI->load->model('tariff_property_model');
		$dropdown = $CI->tariff_property_model->risk_category_dropdown($lang);
		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_PROPERTY_risk_dropdown'))
{
	/**
	 * Risk Dropdown
	 *
	 * @param int $risk_category Risk Category (Property Tariff ID)
	 * @param string $lang 	both|en|np
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _PROPERTY_risk_dropdown($risk_category, $lang='both', $flag_blank_select = true )
	{
		$CI =& get_instance();
		$CI->load->model('tariff_property_model');
		$dropdown = $CI->tariff_property_model->risk_dropdown($risk_category, $lang);
		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_usage_type_dropdown'))
{
	/**
	 *  Object's Usage Type dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_PROPERTY_usage_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'BLDNG' 	=> 'भवन (आवासीय वा अन्य)',
			'BIZ' 		=> 'ब्यापार, ब्यवसाय, पसल',
			'IND'		=> 'उद्योग',
			'OUTIND' 	=> 'उद्योग परिसर बाहिर रहेका सम्पति',
			'OUTINDSTR' => 'उद्योग परिसर भन्दा बाहिर गरिएको भण्डारण',
			'OTH' 		=> 'अन्य'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_item_type_dropdown'))
{
	/**
	 *  Object's Property Item Type dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_PROPERTY_item_type_dropdown( $flag_blank_select = true )
	{
		// $dropdown = [
		// 	'१.०' 	=> 'भवन परिसर (कम्पाउन्ड वाल सहित)',
		// 	'२.अ' 		=> 'भवन',
		// 	'२.आ' 		=> 'यन्त्र तथा उपकरण (उधोगको बीमाको हकमा प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको यन्त्र तथा उपरकरणको खरिद तथा जडान मिति सहितको विवरण खुलाउने)',
		// 	'२.इ' 		=> 'कच्चा पदार्थ',
		// 	'२.ई' 		=> 'प्रक्रियाको क्रममा रहेको मौज्दात (वर्क इन प्रोग्रेस)',
		// 	'२.उ' 		=> 'तयारी बस्तु',
		// 	'२.ऊ' 		=> 'अर्ध तयारी बस्तु',
		// 	'२.ऋ' 		=> 'फर्निचर फिक्चर्स तथा फिटिङ्ग्स',
		// 	'२.ए' 		=> 'नगद, सुनचाँदी, गरगहना तथा हिरा जवाहरत',
		// 	'२.ऐ' 		=> 'नक्सा, ढलाईको साँचो, पाण्डुलिपि, चित्रकला, कलात्मक बस्तु तथा दुर्भल सामग्री',
		// 	'२.ओ' 		=> 'अन्य सरसामान (प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको सामानको विवरण खुलाउने)',
		// ];

		$dropdown = [
			'A1' 		=> 'भवन परिसर (कम्पाउन्ड वाल सहित)',
			'B1' 		=> 'भवन',
			'B2' 		=> 'यन्त्र तथा उपकरण',
			'B3' 		=> 'कच्चा पदार्थ',
			'B4' 		=> 'प्रक्रियाको क्रममा रहेको मौज्दात (वर्क इन प्रोग्रेस)',
			'B5' 		=> 'तयारी बस्तु',
			'B6' 		=> 'अर्ध तयारी बस्तु',
			'B7' 		=> 'फर्निचर फिक्चर्स तथा फिटिङ्ग्स',
			'B8' 		=> 'नगद, सुनचाँदी, गरगहना तथा हिरा जवाहरत',
			'B9' 		=> 'नक्सा, ढलाईको साँचो, पाण्डुलिपि, चित्रकला, कलात्मक बस्तु तथा दुर्भल सामग्री',
			'B10' 		=> 'अन्य सरसामान (प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको सामानको विवरण खुलाउने)',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_item_type_index'))
{
	/**
	 *  Object's Property Item Type Indices
	 *
	 * @return	array
	 */
	function _OBJ_PROPERTY_item_type_index( )
	{
		// $dropdown = [
		// 	'१.०' 	=> 'भवन परिसर (कम्पाउन्ड वाल सहित)',
		// 	'२.अ' 		=> 'भवन',
		// 	'२.आ' 		=> 'यन्त्र तथा उपकरण (उधोगको बीमाको हकमा प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको यन्त्र तथा उपरकरणको खरिद तथा जडान मिति सहितको विवरण खुलाउने)',
		// 	'२.इ' 		=> 'कच्चा पदार्थ',
		// 	'२.ई' 		=> 'प्रक्रियाको क्रममा रहेको मौज्दात (वर्क इन प्रोग्रेस)',
		// 	'२.उ' 		=> 'तयारी बस्तु',
		// 	'२.ऊ' 		=> 'अर्ध तयारी बस्तु',
		// 	'२.ऋ' 		=> 'फर्निचर फिक्चर्स तथा फिटिङ्ग्स',
		// 	'२.ए' 		=> 'नगद, सुनचाँदी, गरगहना तथा हिरा जवाहरत',
		// 	'२.ऐ' 		=> 'नक्सा, ढलाईको साँचो, पाण्डुलिपि, चित्रकला, कलात्मक बस्तु तथा दुर्भल सामग्री',
		// 	'२.ओ' 		=> 'अन्य सरसामान (प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको सामानको विवरण खुलाउने)',
		// ];

		return ['१.०', '२.अ', '२.आ', '२.इ', '२.ई', '२.उ', '२.उ', '२.ऊ', '२.ऋ', '२.ए', '२.ऐ', '२.ओ'];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_premium_table_description_list'))
{
	/**
	 *  Object's Premium Table Description List
	 *
	 * @return	array
	 */
	function _OBJ_PROPERTY_premium_table_description_list( )
	{
		// $dropdown = [
		// 	'१.०' 	=> 'भवन परिसर (कम्पाउन्ड वाल सहित)',
		// 	'२.अ' 		=> 'भवन',
		// 	'२.आ' 		=> 'यन्त्र तथा उपकरण (उधोगको बीमाको हकमा प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको यन्त्र तथा उपरकरणको खरिद तथा जडान मिति सहितको विवरण खुलाउने)',
		// 	'२.इ' 		=> 'कच्चा पदार्थ',
		// 	'२.ई' 		=> 'प्रक्रियाको क्रममा रहेको मौज्दात (वर्क इन प्रोग्रेस)',
		// 	'२.उ' 		=> 'तयारी बस्तु',
		// 	'२.ऊ' 		=> 'अर्ध तयारी बस्तु',
		// 	'२.ऋ' 		=> 'फर्निचर फिक्चर्स तथा फिटिङ्ग्स',
		// 	'२.ए' 		=> 'नगद, सुनचाँदी, गरगहना तथा हिरा जवाहरत',
		// 	'२.ऐ' 		=> 'नक्सा, ढलाईको साँचो, पाण्डुलिपि, चित्रकला, कलात्मक बस्तु तथा दुर्भल सामग्री',
		// 	'२.ओ' 		=> 'अन्य सरसामान (प्रत्येक एक लाख रूपैंया भन्दा बढी रकमको सामानको विवरण खुलाउने)',
		// ];

		return [
			'पहिलो स्थानमा रहेको बीमा गरिने सम्पत्तिको बिमांक रकम',
			'दोस्रो स्थानमा रहेको बीमा गरिने सम्पत्तिको बिमांक रकम',
			'तेस्रो स्थानमा रहेको बीमा गरिने सम्पत्तिको बिमांक रकम',
			'चौथो स्थानमा रहेको बीमा गरिने सम्पत्तिको बिमांक रकम',
			'पाचौं स्थानमा रहेको बीमा गरिने सम्पत्तिको बिमांक रकम'
		];
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_risk_class_dropdown'))
{
	/**
	 * Object's Risk Class dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_PROPERTY_risk_class_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'1' => 'पहिलो श्रेणी',
			'2' => 'दोस्रो श्रेणी'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Perform tasks that are required before saving a policy objects.
	 * Return the processed data for further computation or saving in DB
	 *
	 * If items are saved as attachment, we upload the excel file.
	 *
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_PROPERTY_pre_save_tasks( array $data, $record )
	{
		/**
		 * Format Items
		 */
		$data = _OBJ_PROPERTY_format_items($data);

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_PROPERTY_format_items'))
{
	/**
	 * Format Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_PROPERTY_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules 		= _OBJ_PROPERTY_validation_rules(NULL)['property_item_list'];
		$item_type_dropdwon = _OBJ_PROPERTY_item_type_dropdown(FALSE);

		$item_list_fields 	= ['item_type', 'item_usage_type', 'item_sum_insured', 'item_remarks'];
		$items_formatted 	= [];
		$property_count 	= count($items['risk_category']);

		for($i=0; $i < $property_count; $i++)
		{
			$single_item_per_property = [];

			// property_risk
			$risk_rules = _OBJ_PROPERTY_validation_rules(NULL)['property_risk'];
			foreach($risk_rules as $rule)
			{
				$key = $rule['_key'];
				$single_item_per_property[$key] = $items[$key][$i];
			}

			// property_location
			$location_rules = _OBJ_PROPERTY_validation_rules(NULL)['property_location'];
			foreach($location_rules as $rule)
			{
				$key = $rule['_key'];
				$single_item_per_property[$key] = $items[$key][$i];
			}

			// Item List
			$list = [];
			foreach($item_type_dropdwon as $item_type_code => $label)
			{
				$list[$item_type_code]['item_type'] = $item_type_code;
				$list[$item_type_code]['item_usage_type'] = $items['item_usage_type'][$item_type_code][$i];
				$list[$item_type_code]['item_sum_insured'] = $items['item_sum_insured'][$item_type_code][$i];
				$list[$item_type_code]['item_remarks'] = $items['item_remarks'][$item_type_code][$i];
			}
			$single_item_per_property['list'] = $list;

			$items_formatted[] = $single_item_per_property;
		}

		$data['object']['items'] = $items_formatted;

		return $data;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('_PROPERTY_schedule_title'))
{
	/**
	 * Schedule Title
	 *
	 * @param int $portfolio_id Portfolio ID
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	string
	 */
	function _PROPERTY_schedule_title($portfolio_id)
	{
		$portfolio_id = (int)$portfolio_id;

		if($portfolio_id == IQB_SUB_PORTFOLIO_PROPERTY_HOUSE_ID )
		{
			$title = 'घर बीमालेख तालिका';
		}
		else
		{
			$title = 'सम्पत्ति बीमालेख तालिका';
		}

		return $title;
	}
}

// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_PROPERTY_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for FIRE Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_form_processing		For Form Processing
	 * @return array
	 */
	function _TXN_PROPERTY_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [

			/**
			 * Premium Validation Rules
			 */
			'premium' => [
				[
                    'field' => 'premium[flag_exclude_pool_risk]',
                    'label' => 'Exclude Pool Risk',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_exclude_pool_risk',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
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

if ( ! function_exists('_TXN_PROPERTY_premium_goodies'))
{
	/**
	 * Get Policy Endorsement Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 *
	 * @return	array
	 */
	function _TXN_PROPERTY_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_PROPERTY_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_PROPERTY'))
{
	/**
	 * Update Policy Premium Information
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_PROPERTY($policy_record, $endorsement_record)
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
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_PROPERTY_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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


					/**
					 * Get post premium Data
					 */
					$post_premium 				= $post_data['premium'] ?? NULL;
					$flag_exclude_pool_risk 	= intval($post_premium['flag_exclude_pool_risk'] ?? 0);



					$items = $object_attributes->items ?? [];
					$item_count = count($items);


					/**
					 * Let's Compute Premium Per Preoprty
					 */
					$premium_arr 	= [];
					$cc_table_desc 	= _OBJ_PROPERTY_premium_table_description_list();
					$sn 			= ['१', '२', '३', '४', '५'];
					$index 			= 0;
					$cc_table_main 	= [];
					$cc_table_other = [];
					$cc_info 		= [];
					foreach($items as $single_property)
					{
						$risk_category = $single_property->risk_category;

						// Get the Tariff For This Risk Category, This Portfolio
						$tariff 		= _TXN_PROPERTY_get_tariff($single_property->risk_category, $policy_record->portfolio_id);

						$premium_per_property = _TXN_PROPERTY_compute_premium_per_property($single_property, $tariff);

						/**
						 * Cost Calculation Table
						 *
						 * s.n. 	description 	dar sanket 		jokhim sanket 		bimanka rakam 		bima dar 	bima shulka
						 */
						$cc_table_main[] = [
							'sn' 					=> $sn[$index],
							'description' 			=> $cc_table_desc[$index],
							'risk_category_code' 	=> $tariff->risk_category_code,
							'risk_code' 			=> $single_property->risk_code,
							'si_per_property' 		=> _TXN_PROPERTY_compute_si_per_property($single_property),
							'applied_rate_basic' 	=> $premium_per_property['applied_rate_basic'],
							'premium_per_property'  => $premium_per_property['basic_premium']
						];

						// Add to array
						$premium_arr[] = $premium_per_property;
					}


					// Compute Total Basic and Pool Premium
					$NET_BASIC_PREMIUM = 0.00;
					$BASIC_PREMIUM 	= 0.00;
					$POOL_PREMIUM 	= 0.00;
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					foreach($premium_arr as $prm)
					{
						$BASIC_PREMIUM 	= bcadd($BASIC_PREMIUM, $prm['basic_premium'], IQB_AC_DECIMAL_PRECISION);
						$POOL_PREMIUM 	= bcadd($POOL_PREMIUM, $prm['pool_premium'], IQB_AC_DECIMAL_PRECISION);
					}

					// Commisionalble Premium is only Basic premium excluding pool from it.
					$NET_BASIC_PREMIUM = bcsub($BASIC_PREMIUM, $POOL_PREMIUM, IQB_AC_DECIMAL_PRECISION);
					if($flag_exclude_pool_risk)
					{
						$cc_table_other[] = [
							'label' => "सम्पुष्टीद्वारा घटाइएको (बीमितको इच्छा अनुसार) बीमशुल्क",
							'value' => $POOL_PREMIUM
						];

						// Save Excluded premium on cc table
						$cc_info['excluded_pool_premium'] = $POOL_PREMIUM;

						// Now zero the pool premium
						$POOL_PREMIUM 	= 0.00;
					}



					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on Basic Premium
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount 	= ( $NET_BASIC_PREMIUM * $pfs_record->direct_discount ) / 100.00 ;
						$dd_formatted 		= number_format($pfs_record->direct_discount, 2);
						$cc_table_other[] 	= [
							'label' => "अभिकर्ता प्रयोग नगरी प्रत्यक्ष बिक्री गरिएको बीमा बापतको छुट (हुलदंगा तथा आतंकबाद जोखिम समूह बाहेकको बीमाशुल्कको {$dd_formatted}% का दरले)",
							'value' => $direct_discount
						];

						// Deduct direct discount from basic premium
						$NET_BASIC_PREMIUM = bcsub($NET_BASIC_PREMIUM, $direct_discount, IQB_AC_DECIMAL_PRECISION);
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						// Commissionable Premium
						$commissionable_premium = $NET_BASIC_PREMIUM;
						$agent_commission = ( $commissionable_premium * $pfs_record->agent_commission ) / 100.00;
					}

					// Total Premium
					$TOTAL_PREMIUM = $NET_BASIC_PREMIUM + $POOL_PREMIUM;

					$cc_table_other[] = [
						'label' => "जम्मा बीमाशुल्क",
						'value' => $TOTAL_PREMIUM
					];



					/**
					 * Below Defautl Basic/Pool Premium Value? - For FRESH/RENEWAL ONLY
					 */
					if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) )
					{
						$txn_data_defaults = [
							'gross_full_amt_basic_premium' 		=> $NET_BASIC_PREMIUM,
							'gross_full_amt_pool_premium' 		=> $POOL_PREMIUM,
						];
						$defaults = [
							'basic' => floatval($pfs_record->amt_default_basic_premium),
							'pool' 	=> floatval($pfs_record->amt_default_pool_premium),
						];
						$txn_data_defaults = _ENDORSEMENT__tariff_premium_defaults( $txn_data_defaults, $defaults, TRUE);


						if(
							$txn_data_defaults['gross_full_amt_basic_premium'] != $NET_BASIC_PREMIUM
							||
							$txn_data_defaults['gross_full_amt_pool_premium'] != $POOL_PREMIUM )
						{

							$txt_basic_premium = $txn_data_defaults['gross_full_amt_basic_premium'] != $NET_BASIC_PREMIUM
													? 'BASIC PREMIUM (minimum)' : 'BASIC PREMIUM';
							$txt_pool_premium = $txn_data_defaults['gross_full_amt_pool_premium'] != $POOL_PREMIUM
													? 'POOL PREMIUM (minimum)' : 'POOL PREMIUM';

							// @TODO : Overwrite Cost Calculation Table
							// $cost_calculation_table = [
							// 	[
							// 		'label' => $txt_basic_premium,
							// 		'value' => $txn_data_defaults['gross_full_amt_basic_premium']
							// 	],
							// 	[
							// 		'label' => $txt_pool_premium,
							// 		'value' => $txn_data_defaults['gross_full_amt_pool_premium']
							// 	]
							// ];
						}

						// Update basic, pool for further computation
						$NET_BASIC_PREMIUM 		= $txn_data_defaults['gross_full_amt_basic_premium'];
						$POOL_PREMIUM 			= $txn_data_defaults['gross_full_amt_pool_premium'];

						if($flag_exclude_pool_risk)
						{
							$commissionable_premium = $NET_BASIC_PREMIUM;
							$POOL_PREMIUM 			= 0.00;

							// @TODO : DO we still give direct discount and agent commision? If so on which value?
						}
					}

					$cost_calculation_table = [
						'cc_table_main' 	=> $cc_table_main,
						'cc_table_other' 	=> $cc_table_other,
						'cc_info' 		 	=> $cc_info
					];


					// -----------------------------------------------------------------------------


					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'gross_full_amt_basic_premium' 		=> $NET_BASIC_PREMIUM,
						'gross_full_amt_commissionable'		=> $commissionable_premium,
						'gross_full_amt_agent_commission'  	=> $agent_commission,
						'gross_full_amt_direct_discount' 	=> $direct_discount,
						'gross_full_amt_pool_premium' 		=> $POOL_PREMIUM,
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

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_PROPERTY_get_tariff'))
{
	/**
	 * Get tariff for given risk category for given portfolio
	 *
	 * @param int $risk_category Risk Category
	 * @param int $portfolio_id Portfolio ID
	 *
	 * @return	object
	 */
	function _TXN_PROPERTY_get_tariff($risk_category, $portfolio_id)
	{
		$CI =& get_instance();

		$CI->load->model('tariff_property_model');

		$record = $CI->tariff_property_model->get($risk_category);
		$tariffs = json_decode($record->tariff ?? NULL);

		$portfolio_tariff = NULL;
		if($tariffs)
		{
			foreach($tariffs as $trf)
			{
				if($trf->portfolio_id == $portfolio_id)
				{
					$portfolio_tariff = $trf;
					break;
				}
			}
		}

		if($portfolio_tariff)
		{
			// Dar Sanket (Risk Category Code)
			$portfolio_tariff->risk_category_code = $record->code;
		}

		return $portfolio_tariff;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_PROPERTY_compute_premium_per_property'))
{
	/**
	 * Compute Premium Per Property
	 *
	 * @param obj $single_property Risk Category
	 * @param obj $tariff Portfolio ID
	 *
	 * @return	array
	 */
	function _TXN_PROPERTY_compute_premium_per_property($single_property, $tariff)
	{
		// SI Per Property
		$si = _TXN_PROPERTY_compute_si_per_property($single_property);

		$basic_premium = 0.00;
		$pool_premium = 0.00;

		// !!! NOTE : Rate is on Per Thousand of SI
		$SIK = bcdiv($si, 1000.00, IQB_AC_DECIMAL_PRECISION);

		$applied_rate_basic = 0.00; // Required On cost Calculation Table
		$applied_rate_pool = 0.00;

		// Basic Premium
		// Rate on Different SI Range???
		if($tariff->basic_apply_si_range == IQB_FLAG_YES)
		{
			$basic_si_min 		= $tariff->basic_si_min;
			$basic_si_min_rate 	= $tariff->basic_si_min_rate;
			$basic_si_overflow_rate = $tariff->basic_si_overflow_rate;

			if($si <= $basic_si_min )
			{
				$basic_premium 		= bcmul($SIK, $basic_si_min_rate, IQB_AC_DECIMAL_PRECISION);
				$applied_rate_basic = $basic_si_min_rate;
			}
			else
			{
				$basic_premium 		= bcmul($SIK, $basic_si_overflow_rate, IQB_AC_DECIMAL_PRECISION);
				$applied_rate_basic = $basic_si_overflow_rate;
			}
		}
		else
		{
			$basic_default_rate = $tariff->basic_default_rate;
			$basic_premium = bcmul($SIK, $basic_default_rate, IQB_AC_DECIMAL_PRECISION);
			$applied_rate_basic = $basic_default_rate;
		}



		// Pool Premium
		// Rate on Different SI Range???
		if($tariff->pool_apply_si_range == IQB_FLAG_YES)
		{
			$pool_si_min 		= $tariff->pool_si_min;
			$pool_si_min_rate 	= $tariff->pool_si_min_rate;
			$pool_si_overflow_rate = $tariff->pool_si_overflow_rate;

			if($si <= $pool_si_min )
			{
				$pool_premium = bcmul($SIK, $pool_si_min_rate, IQB_AC_DECIMAL_PRECISION);
				$applied_rate_pool = $pool_si_min_rate;
			}
			else
			{
				$pool_premium = bcmul($SIK, $pool_si_overflow_rate, IQB_AC_DECIMAL_PRECISION);
				$applied_rate_pool = $pool_si_overflow_rate;
			}
		}
		else
		{
			$pool_default_rate = $tariff->pool_default_rate;
			$pool_premium = bcmul($SIK, $pool_default_rate, IQB_AC_DECIMAL_PRECISION);
			$applied_rate_pool = $pool_default_rate;
		}

		return [
			'basic_premium' => $basic_premium,
			'pool_premium' 	=> $pool_premium,
			'applied_rate_basic' 	=> $applied_rate_basic,
			'applied_rate_pool' 	=> $applied_rate_pool,
		];
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_PROPERTY_compute_si_per_property'))
{
	/**
	 * Compute SI Per Property
	 *
	 * @param obj $single_property Single Property Object
	 *
	 * @return	decimal
	 */
	function _TXN_PROPERTY_compute_si_per_property($single_property)
	{

		$item_list = $single_property->list ?? [];
        $si_per_property = 0.00;
        foreach($item_list as $single )
        {
        	if($single->item_sum_insured)
        	{
        		$si_per_property = bcadd($si_per_property, $single->item_sum_insured, IQB_AC_DECIMAL_PRECISION);
        	}
        }

        return $si_per_property;
	}
}