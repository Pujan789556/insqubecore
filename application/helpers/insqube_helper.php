<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube General Helper Functions
 *
 * 	This will have general helper functions required
 * 
 * 
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */


// ------------------------------------------------------------------------

if ( ! function_exists('set_menu_active'))
{
	/**
	 * Set navigation menu active
	 * 
	 * @param string $nav_src supplied nav menu
	 * @param string $nav_dstn nav menu to compare against
	 * @param string $css_class CSS Class to return if active
	 * @return bool
	 */
	function set_menu_active( $nav_src, $nav_dstn, $css_class = 'active' )
	{
		return $nav_src === $nav_dstn ? $css_class : '';
	}
}

// ------------------------------------------------------------------------
