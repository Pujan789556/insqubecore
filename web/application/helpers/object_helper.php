<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Policy Object Helper Functions
 *
 * This file contains helper functions related to policy
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// GENERAL OBJECT HELPERS
// ------------------------------------------------------------------------

if ( ! function_exists('_PO_row_snippet'))
{
	/**
	 * Get Policy Object  - Row Snippet
	 *
	 * Row Partial View for An Object
	 *
	 * @param object $record Policy Object
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _PO_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$snippet = '';
		switch ($record->portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$snippet = _PO_MOTOR_row_snippet($record, $_flag__show_widget_row);
				break;

			default:
				# code...
				break;
		}
		return $snippet;
	}
}
// ------------------------------------------------------------------------


if ( ! function_exists('_PO_select_text'))
{
	/**
	 * Get Policy Object  - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 *
	 * @param object $record 	Object Record
	 * @return	string
	 */
	function _PO_select_text( $record )
	{
		$snippet = '';
		switch ($record->portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$snippet = _PO_MOTOR_select_text($record);
				break;

			default:
				# code...
				break;
		}
		return $snippet;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('_PO_validation_rules'))
{
	/**
	 * Get Policy Object  - Validation Rules
	 *
	 * Row Partial View for An Object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	array
	 */
	function _PO_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$v_rules = [];
		switch ($portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$v_rules = _PO_MOTOR_validation_rules( $formatted );
				break;

			default:
				# code...
				break;
		}
		return $v_rules;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('_PO_attribute_form'))
{
	/**
	 * Get Policy Object  - Attribute Form
	 *
	 * Row Partial Form View for An Object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _PO_attribute_form( $portfolio_id )
	{
		$attribute_form = '';
		switch ($portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$attribute_form = 'objects/forms/_form_object_motor';
				break;

			default:
				# code...
				break;
		}
		return $attribute_form;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('_PO_policy_package_dropdown'))
{
	/**
	 * Get Policy Packages for Portfolio
	 *
	 * Dropdown list of packages of specified portfolio
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _PO_policy_package_dropdown( $portfolio_id, $flag_blank_select = true )
	{
		$dropdown = [];
		switch ($portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$dropdown = _PO_MOTOR_policy_package_dropdown($flag_blank_select);
				break;

			default:
				# code...
				break;
		}
		return $dropdown;
	}
}
// ------------------------------------------------------------------------



// ------------------------------------------------------------------------
// MOTOR OBJECT HELPERS
// ------------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_validation_rules'))
{
	/**
	 * Get Policy Object - Motor - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _PO_MOTOR_validation_rules( $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post = $CI->input->post();
		$object = $post['object'] ?? NULL;

		$sub_portfolio_id 	= $post['sub_portfolio_id'] ?? '';
		$cvc_type_rules 	= 'trim|alpha|strtoupper';
		$staff_count_rule 	= 'trim|integer|max_length[4]';
		if($sub_portfolio_id == IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID )
		{
			$cvc_type_rules 	= 'trim|required|alpha|strtoupper';
			$staff_count_rule 	= 'trim|required|integer|max_length[4]';
		}

		// CVC TYPES in_list validation
		$cvc_type_list = array_keys( _PO_MOTOR_CVC_type_dropdown(FALSE) );
		$cvc_type_in_list = implode(',', $cvc_type_list);
		$cvc_type_rules .= '|in_list['.$cvc_type_in_list.']';

		// To Be Intimated Set?
		$flag_to_be_intimated = $object['flag_to_be_intimated'] ?? NULL;
		if($flag_to_be_intimated)
		{
			$reg_no_rules  = 'trim|max_length[30]|strtoupper|in_list[TO BE INTIMATED]';
			$reg_date_rule = 'trim|valid_date';
		}
		else
		{
			$reg_no_rules  = 'trim|max_length[30]|strtoupper|callback__cb_motor_duplicate_reg_no';
			$reg_date_rule = 'trim|required|valid_date';
		}

		/**
		 * Object Sections
		 * -----------------
		 * 	a. Vehicle Section
		 * 	b. Trailer (Private/Commercial Vehicle)
		 */

		$v_rules = [

			// Vehicle Section
			'vehicle' =>[
				[
			        'field' => 'object[ownership]',
			        '_key' => 'ownership',
			        'label' => 'Ownership',
			        'rules' => 'trim|required|alpha|in_list[G,N]',
			        '_type'     => 'dropdown',
			        '_data' 	=> _PO_MOTOR_ownership_dropdown( ),
			        '_default'  => 'N',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[cvc_type]',
			        '_key' => 'cvc_type',
			        'label' => 'Commercial Vehicle Type',
			        'rules' => $cvc_type_rules,
			        '_id' 		=> '_motor-vehicle-cvc-type',
			        '_type'     => 'dropdown',
			        '_data' 	=> _PO_MOTOR_CVC_type_dropdown(),
			        '_required' => true
			    ],

			    [
			        'field' => 'object[flag_mcy_df]',
			        '_key' => 'flag_mcy_df',
			        'label' => 'Disabled friendly Vehicle',
			        'rules' => 'trim|integer|in_list[1]',
			        '_id' 		=> '_motor-vehicle-df',
			        '_type'     => 'checkbox',
			        '_checkbox_value' 	=> '1',
			        '_required' => false
			    ],

			    [
			        'field' => 'object[engine_no]',
			        '_key' => 'engine_no',
			        'label' => 'Engine Number',
			        'rules' => 'trim|required|alpha_numeric_spaces|max_length[30]|strtoupper|callback__cb_motor_duplicate_engine_no',
			        '_id' 		=> '_motor-engine-no',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[chasis_no]',
			        '_key' => 'chasis_no',
			        'label' => 'Chasis Number',
			        'rules' => 'trim|required|alpha_numeric_spaces|max_length[30]|strtoupper|callback__cb_motor_duplicate_chasis_no',
			        '_id' 		=> '_motor-chasis-no',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[ec_unit]',
			        '_key' => 'ec_unit',
			        'label' => 'Engine Capacity Unit',
			        'rules' => 'trim|required|alpha|in_list[CC,HP,KW]',
			        '_id' 		=> '_motor-vehicle-ec-unit',
			        '_type'     => 'dropdown',
			        '_data' 	=> _PO_MOTOR_ec_unit_dropdown(),
			        '_required' => true
			    ],
			    [
			        'field' => 'object[engine_capacity]',
			        '_key' => 'engine_capacity',
			        'label' => 'Engine Capacity',
			        'rules' => 'trim|required|integer|max_length[5]',
			        '_id' 		=> '_motor-engine-capacity',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[year_mfd]',
			        '_key' => 'year_mfd',
			        'label' => 'Year Manufactured',
			        'rules' => 'trim|required|integer|exact_length[4]',
			        '_id' 		=> '_motor-mfd-year',
			        '_type'     => 'text',
			        '_required' => true
			    ],

			    // For New Vehicle, It can Have "To Be Intimated"
			    [
			        'field' => 'object[flag_to_be_intimated]',
			        '_key' => 'flag_to_be_intimated',
			        'label' => 'To Be Intimated',
			        'rules' => 'trim|integer|in_list[1]',
			        '_id' 		=> '_motor-vehicle-to-be-intimated',
			        '_type'     => 'checkbox',
			        '_checkbox_value' 	=> '1',
			        '_required' => false,
			        '_help_text' => 'Click here if the vehicle is not registered yet.'
			    ],

			    [
			        'field' => 'object[reg_no]',
			        '_key' => 'reg_no',
			        'label' => 'Registration Number',
			        'rules' => $reg_no_rules,
			        '_id' 		=> '_motor-registration-no',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[reg_date]',
			        '_key' => 'reg_date',
			        'label' => 'Registration Date',
			        'rules' => $reg_date_rule,
			        '_id' 		=> '_motor-registration-date',
			        '_type'     => 'date',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[puchase_date]',
			        '_key' => 'puchase_date',
			        'label' => 'Purchase Date',
			        'rules' => 'trim|required|valid_date',
			        '_id' 		=> '_motor-purchase-date',
			        '_type'     => 'date',
			        '_required' => true
			    ],
			    [
			    	'field' => 'object[vechile_status]',
			        '_key' => 'vechile_status',
			        'label' => 'Status on Purchase',
			        'rules' => 'trim|required|alpha|in_list[N,O]',
			        '_id' 		=> '_motor-purchase-status',
			        '_type'     => 'dropdown',
			        '_data' 	=> _PO_MOTOR_vehicle_status_dropdown(),
			        '_required' => true
			    ],
			    [
			        'field' => 'object[manufacturer]',
			        '_key' => 'manufacturer',
			        'label' => 'Manufacturer',
			        'rules' => 'trim|required|alpha_numeric_spaces|max_length[80]',
			        '_id' 		=> '_motor-manufacturer',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[make]',
			        '_key' => 'make',
			        'label' => 'Make',
			        'rules' => 'trim|required|max_length[60]',
			        '_id' 		=> '_motor-make',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[model]',
			        '_key' => 'model',
			        'label' => 'Model',
			        'rules' => 'trim|required|alpha_numeric_spaces|max_length[60]',
			        '_id' 		=> '_motor-model',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[price_vehicle]',
			        '_key' => 'price_vehicle',
			        'label' => 'Vehicle Price',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_id' 		=> '_motor-vehicle-price',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[price_accessories]',
			        '_key' => 'price_accessories',
			        'label' => 'Acessories Price',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
			        '_id' 		=> '_motor-accessories-price',
			        '_type'     => 'text',
			        '_default' 	=> 0.00,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[carrying_unit]',
			        '_key' => 'carrying_unit',
			        'label' => 'Carrying Capacity Unit',
			        'rules' => 'trim|required|alpha|in_list[S,T]',
			        '_id' 		=> '_motor-vehicle-carrying-unit',
			        '_type'     => 'dropdown',
			        '_data' 	=> _PO_MOTOR_carrying_unit_dropdown(),
			        '_required' => true
			    ],
			    [
			        'field' => 'object[carrying_capacity]',
			        '_key' => 'carrying_capacity',
			        'label' => 'Carrying Capacity',
			        'rules' => 'trim|required|integer|max_length[5]',
			        '_id' 		=> '_motor-carrying-capacity',
			        '_type'     => 'text',
			        '_required' => true
			    ]
		    ],

		    // Staff Count (Commercial Vehicle Only)
		    'staff' => [
		    	[
			        'field' => 'object[staff_count]',
			        '_key' => 'staff_count',
			        'label' => 'Staff Count',
			        'rules' => $staff_count_rule,
			        '_id' 		=> '_motor-staff-count',
			        '_type'     => 'text',
			        '_default' 	=> 0,
			        '_required' => false
			    ],
		    ],

		    // Trailer Section
		    'trailer' => [
		    	[
			        'field' => 'object[trailer_price]',
			        '_key' => 'trailer_price',
			        'label' => 'Trailer Price',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_id' 		=> '_motor-trailer-price',
			        '_type'     => 'text',
			        '_default' 	=> 0.00,
			        '_required' => false
			    ],
		    ],
		];

		// return formatted?
		$fromatted_v_rules = [];
		if($formatted === TRUE)
		{
			foreach ($v_rules as $key => $rules)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $rules);
			}
			return $fromatted_v_rules;
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_ownership_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Ownership Dropdown
	 *
	 * Motor ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_ownership_dropdown( $flag_blank_select = true )
	{
		$dropdown = [IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_GOVT => 'Government', IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT => 'Non-government'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_vehicle_status_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Ownership Dropdown
	 *
	 * Motor ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_vehicle_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['N' => 'New', 'O' => 'Old'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_sub_portfolio_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Sub Portfolio List
	 *
	 * Motor Sub-portfolio Dorpdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_sub_portfolio_dropdown( $flag_blank_select = true)
	{
		$CI =& get_instance();
		$CI->load->model('portfolio_model');

		$dropdown = $CI->portfolio_model->dropdown_children(IQB_MASTER_PORTFOLIO_MOTOR_ID, 'code');
		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_CVC_type_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Commercial Vehicle Dropdown
	 *
	 * Motor - Commercial Vehicle Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @param bool $sub_type Return Sub Type instead of primary type
	 * @return	bool
	 */
	function _PO_MOTOR_CVC_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL 	=> 'Goods Carrier - Truck', 	// GOODS CARRIER GENERAL
			IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER  	=> 'Goods Carrier - Tanker',	// GOODS CARRIER TANKER
			IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER  		=> 'Passenger Carrier', 		// PASSENGER CARRIER
			IQB_MOTOR_CVC_TYPE_TAXI 					=> 'Taxi',
			IQB_MOTOR_CVC_TYPE_TEMPO					=> 'Tempo (e-rikshaw, safa tempo, tricycle)',
			IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY 			=> 'Agriculture & Forestry Vehicle',
			IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER	=> 'Tractor & Power Triller',
			IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT	=> 'Construction Equipment Vehicle'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_ec_unit_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Engine Capacity Dropdown
	 *
	 * Motor Engine Capacity dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_ec_unit_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['CC' => 'Cubic Centimeter (CC)', 'HP' => 'Horse Power (HP)', 'KW' => 'Kilo Watt (KW)'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_ec_unit_tariff_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Engine Capacity Dropdown for Tariff Settings
	 *
	 * Motor Engine Capacity for Tariff Setting dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_ec_unit_tariff_dropdown( $flag_blank_select = true )
	{
		$dropdown = _PO_MOTOR_ec_unit_dropdown(FALSE) + ['T' => 'Metric Ton'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_carrying_unit_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Carrying Capacity Dropdown
	 *
	 * Motor Carrying Capacity dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_carrying_unit_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['S' => 'Seat', 'T' => 'Metric Ton'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_row_snippet'))
{
	/**
	 * Get Policy Object - Motor - Row Snippet
	 *
	 * Row Partial View for Motor Object
	 *
	 * @param object $record Policy Object (Motor)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _PO_MOTOR_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_motor', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_select_text'))
{
	/**
	 * Get Policy Object - Motor - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _PO_MOTOR_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$select_text = '';
		if($attributes)
		{
			$select_text = implode(', ', [$attributes->make, $attributes->model, $attributes->reg_no, $attributes->engine_no, $attributes->chasis_no]);
		}
		return $select_text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_PO_MOTOR_policy_package_dropdown'))
{
	/**
	 * Get Policy Packages - Motor
	 *
	 * Motor Policy Packages
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _PO_MOTOR_policy_package_dropdown( $flag_blank_select = true)
	{
		$dropdown = [
			IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE  	=> 'Comprehensive',
			IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY 		=> 'Third Party',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}
// ------------------------------------------------------------------------




