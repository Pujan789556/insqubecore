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

if ( ! function_exists('date_difference'))
{
	/**
	 * Get Date Difference
	 *
	 * 	Calculate the difference between two dates and returns
	 * 	Years or Months or Days
	 *
	 * @param	date
	 * @param 	date
	 * @param 	str
	 * @return	integer
	 */
	function date_difference($from, $to, $what)
	{
		$d1 = new DateTime($from);
		$d2 = new DateTime($to);

		$diff = $d1->diff($d2);

		if( !$diff )
		{
			return FALSE;
		}

		$interval 	= 0;
		$years 		= $diff->y;
		$months 	= $diff->m;
		$days 		= $diff->days;

		if($what == 'y')
		{
			$interval = $years;
		}
		else if($what == 'm')
		{
			$interval = ($years * 12) + $months;
		}
		else
		{
			$interval = $days;
		}
		return $interval;
	}
}

// ------------------------------------------------------------------------

