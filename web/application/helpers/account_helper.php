<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Account Helper Functions
 *
 * This file contains helper functions related to Accounting
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('ac_party_types_dropdown'))
{
	/**
	 * Get Accounting Party Types Dropdown
	 *
	 * @return	string
	 */
	function ac_party_types_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_AC_PARTY_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}


// ------------------------------------------------------------------------



