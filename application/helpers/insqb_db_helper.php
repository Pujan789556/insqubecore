<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Database Transaction Helper Functions
 *
 * 	This will contain helper functions related to database query.
 * 	We will be having the following four database fields on almost all
 *  transactional tables:
 * 		created_at, updated_at, created_by, deleted_by
 * 
 * 	For these fields, we want to create some simple functions to automate
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */


// ------------------------------------------------------------------------

if ( ! function_exists('timestamp_create'))
{
	/**
	 * Timestamp field on Record Insert/Create
	 * 
	 * Add created_at & created_by fields on 
	 * 
	 * @param	array	$data
	 * @return	bool
	 */
	function timestamp_create( &$data )
	{
		$CI =& get_instance();			
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['created_by'] = $CI->dx_auth->get_user_id();
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('timestamp_update'))
{
	/**
	 * Timestamp field on Record Update
	 * 
	 * Add updated_at & updated_by fields on 
	 * 
	 * @param	array	$data
	 * @return	bool
	 */
	function timestamp_update( &$data )
	{
		$CI =& get_instance();			
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['updated_by'] = $CI->dx_auth->get_user_id();
	}
}

// ------------------------------------------------------------------------

