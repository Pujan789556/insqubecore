<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube RI Helper Functions
 *
 * This file contains helper functions related to RI
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('ri_ac_basic_dropdown'))
{
	/**
	 * Get RI Account Basic Dropdown
	 *
	 * @return	string
	 */
	function ri_ac_basic_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_SETUP_AC_BASIC_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('ri_treaty_types_dropdown'))
{
	/**
	 * Get RI Treaty Types Dropdown
	 *
	 * @return	string
	 */
	function ri_treaty_types_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_TREATY_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('ri_qs_surplus_line_reference_dropdown'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function ri_qs_surplus_line_reference_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			1 => 'Defined/Max Retention',
			2 => 'Quota Retention(%)'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}


// ------------------------------------------------------------------------



