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

if ( ! function_exists('_OBJ_row_snippet'))
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
	function _OBJ_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$snippet = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($record->portfolio_id);

			$snippet = _OBJ_MOTOR_row_snippet($record, $_flag__show_widget_row);
		}

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_select_text'))
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
	function _OBJ_select_text( $record )
	{
		$snippet = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($record->portfolio_id);

			$snippet = _OBJ_MOTOR_select_text($record);
		}

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_validation_rules'))
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
	function _OBJ_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$v_rules = [];

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$v_rules = _OBJ_MOTOR_validation_rules( $portfolio_id, $formatted );
		}
		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_attribute_form'))
{
	/**
	 * Get Policy Object  - Attribute Form
	 *
	 * Row Partial Form View for An Object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _OBJ_attribute_form( $portfolio_id )
	{
		$attribute_form = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$attribute_form = 'objects/forms/_form_object_motor';
		}

		return $attribute_form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_policy_package_dropdown'))
{
	/**
	 * Get Policy Packages for Portfolio
	 *
	 * Dropdown list of packages of specified portfolio
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _OBJ_policy_package_dropdown( $portfolio_id, $flag_blank_select = true )
	{
		$portfolio_id 	= (int)$portfolio_id;
		$dropdown 		= [];

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$dropdown = _OBJ_MOTOR_policy_package_dropdown($flag_blank_select);
		}

		/**
		 * FIRE
		 * -----
		 * For all type of FIRE portfolios, we do not need any policy package
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$dropdown = _OBJ_NA_policy_package_dropdown($flag_blank_select);
		}

		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_NA_policy_package_dropdown'))
{
	/**
	 * Get Not-Applicable Policy Packages for Portfolios
	 * which do not require any packages
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_NA_policy_package_dropdown( $flag_blank_select = true)
	{
		$dropdown = [
			IQB_POLICY_PACKAGE_NOT_APPLICABLE  	=> 'Not Applicable'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_sum_insured_amount( $portfolio_id, $data )
	{
		$amt_sum_insured =  0.00;

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$amt_sum_insured = _OBJ_MOTOR_sum_insured_amount($portfolio_id, $data);
		}

		return $amt_sum_insured;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_transactional_attributes'))
{
	/**
	 * Get the list of transactional attributes
	 *
	 * These are the object attributes, whose change will affect on
	 * 	- Sum Insured Amount
	 * 	- Premium
	 *
	 * For tariff-portfolio, we must need this list to generate cost reference table.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return float
	 */
	function _OBJ_transactional_attributes( $portfolio_id )
	{
		$attributes =  [];

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$attributes = _OBJ_MOTOR_transactional_attributes($portfolio_id);
		}

		return $attributes;
	}
}


