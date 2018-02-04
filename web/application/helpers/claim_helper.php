<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Claim Helper Functions
 *
 * This file contains helper functions related to Claim
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__status_dropdown'))
{
	/**
	 * Get Claim Status Dropdown
	 *
	 * @return	bool
	 */
	function CLAIM__status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_CLAIM_STATUS_DRAFT 			=> 'Draft',
			IQB_CLAIM_STATUS_VERIFIED 		=> 'Verified',
			IQB_CLAIM_STATUS_APPROVED 		=> 'Approved',
			IQB_CLAIM_STATUS_SETTLED 		=> 'Settled',
			IQB_CLAIM_STATUS_WITHDRAWN 		=> 'Withdrawn',
			IQB_CLAIM_STATUS_CLOSED 		=> 'Closed'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__death_injured_type_dropdown'))
{
	/**
	 * Get Death/Injured Type Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__death_injured_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'D' => 'Dead',
			'I' => 'Injured'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__supporting_docs_dropdown'))
{
	/**
	 * Get supporting documents dropdown
	 *
	 * @return	array
	 */
	function CLAIM__supporting_docs_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'a' => "Surveyors / Doctors / Investigators / Department's Report",
			'b' => 'Muchulka',
			'c' => 'Police Report',
			'd' => 'Post-mortem Report',
			'e' => 'Death Certificate',
			'f' => 'Succession Certificate',
			'g' => 'Claim Form',
			'h' => 'Bills / Invoice / Receipts',
			'i' => 'X-ray / Photos / Reports',
			'j' => 'Invoice',
			'k' => 'B/L, C/N, R/R, AwB',
			'l' => 'Claim Bill',
			'm' => 'Regd. Book / Driving License',
			'n' => 'Route Permit / Janch Pass / Chalan',
			'o' => 'Others'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('CLAIM__is_editable'))
{
	/**
	 * Is Claim Editable?
	 *
	 * Check if the given policy claim is editable.
	 *
	 * @param char $status 	Claim Status
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function CLAIM__is_editable($status, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 		= FALSE;



		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft
		 *
		 * Editable Permissions Are
		 * 		edit.claim
		 */

		// Editable Permissions ?
		if(
			$status === IQB_CLAIM_STATUS_DRAFT
				&&
			( $CI->dx_auth->is_admin() || $CI->dx_auth->is_authorized('claims', 'edit.claim') )
		)
		{
			$__flag_authorized = TRUE;
		}

		// Terminate on Exit?
		if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
		{
			$CI->dx_auth->deny_access();
			exit(1);
		}

		return $__flag_authorized;
	}
}

// ------------------------------------------------------------------------


