<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Extra Date helper Functions
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola
 * @link		https://twitter.com/ipbastola
 */

// ------------------------------------------------------------------------

if ( ! function_exists('valid_date'))
{
	/**
	 * Valid Date?
	 *
	 * The supplied paramater should have yyyy-mm-dd format, otherwise it will always return false
	 *
	 * @param	string
	 * @return	bool
	 */
	function valid_date($str)
	{
		$date_values = explode('-',$str);
		if((sizeof($date_values)!=3) || !checkdate( (int) $date_values[1], (int) $date_values[2], (int) $date_values[0]))
		{
			return FALSE;
		}
		return TRUE;
	}
}

// ------------------------------------------------------------------------

