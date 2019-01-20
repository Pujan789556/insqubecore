<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Property Portfolio Helper Functions
 *
 * This file contains helper functions related to Fire Portfolio
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


// ------------------------------------------------------------------------

if ( ! function_exists('_PROPERTY_risk_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _PROPERTY_risk_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'BWALL' => 'सिमाना पर्खाल',
			'BLDNG' => 'भवन',
			'GOODS'	=> 'मालसामान',
			'MCNRY' => 'मशीनरी',
			'OTH' 	=> 'अन्य'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}