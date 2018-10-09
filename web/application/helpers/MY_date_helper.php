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

if ( ! function_exists('duration_formatted'))
{
	/**
	 * Get Date Difference Formatted
	 *
	 * 	Return X Years Y Months Z Days
	 *
	 * @param	date
	 * @param 	date
	 * @param 	str 	Language
	 * @return	string
	 */
	function duration_formatted($from, $to, $lang = 'en')
	{
		$d1 = new DateTime($from);
		$d2 = new DateTime($to);

		$diff = $d1->diff($d2);

		if( !$diff )
		{
			return FALSE;
		}

		$formatted = [];

		if($diff->y)
		{
			$formatted[] = $diff->y;
			if($lang == 'en')
			{
				$formatted[] = $diff->y > 1 ? 'years' : 'year';
			}
			else
			{
				$formatted[] = 'वर्ष';
			}

		}

		if($diff->m)
		{
			$formatted[] = $diff->m;
			if($lang == 'en')
			{
				$formatted[] = $diff->m > 1 ? 'months' : 'month';
			}
			else
			{
				$formatted[] = 'महिना';
			}

		}

		if($diff->d)
		{
			$formatted[] = $diff->d;
			if($lang == 'en')
			{
				$formatted[] = $diff->d > 1 ? 'days' : 'day';
			}
			else
			{
				$formatted[] = 'दिन';
			}
		}

		return implode(' ', $formatted);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('belong_to_current_fy_quarter'))
{
	/**
	 * Is a Record belong to Current Fiscal Year's Current Quarter?
	 *
	 * @param	int $fiscal_yr_id
	 * @param	int $quarter
	 * @return	bool
	 */
	function belong_to_current_fy_quarter($fiscal_yr_id, $quarter)
	{
		$CI =& get_instance();

		return ($CI->current_fy_quarter->fiscal_yr_id == $fiscal_yr_id) && ($CI->current_fy_quarter->quarter == $quarter);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_current_fy_quarter'))
{
	/**
	 * Is supplied date falls under current fy quarter?
	 *
	 * @param	date $date
	 * @return	bool
	 */
	function is_current_fy_quarter($date)
	{
		$CI =& get_instance();

		return ( strtotime($CI->current_fy_quarter->starts_at) <= strtotime($date) ) && ( strtotime($CI->current_fy_quarter->ends_at) >= strtotime($date) );
	}
}

// ------------------------------------------------------------------------

