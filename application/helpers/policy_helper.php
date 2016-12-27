<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Policy Helper Functions
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

if ( ! function_exists('get_policy_duration_list'))
{
	/**
	 * Get Policy Duration List
	 *
	 * Returns the array of duration list from policy configuration
	 *
	 * @return	bool
	 */
	function get_policy_duration_list( )
	{
		$CI =& get_instance();
		return $CI->config->item('PC_policy_duration_list');
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_policy_status_dropdown'))
{
	/**
	 * Get Policy Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['D' => 'Draft', 'A' => 'Active', 'E' => 'Expired'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_policy_status_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	mixed
	 */
	function get_policy_status_text( $key )
	{
		$list = get_policy_status_dropdown();
		return $list[$key] ?? NULL;
	}
}

// ------------------------------------------------------------------------

