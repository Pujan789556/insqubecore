<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Extra Text helper Functions
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola
 * @link		https://twitter.com/ipbastola
 */

// ------------------------------------------------------------------------

if ( ! function_exists('ordinal'))
{
	/**
	 * Format ordinal suffix (st, nd, rd or th) for each number
	 *
	 * @param	integer
	 * @return	bool
	 */
	function ordinal($number)
	{
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
	    if ((($number % 100) >= 11) && (($number%100) <= 13))
	        return $number. 'th';
	    else
	        return $number. $ends[$number % 10];
	}
}
// ------------------------------------------------------------------------

