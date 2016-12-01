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

