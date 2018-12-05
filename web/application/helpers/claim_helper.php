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
			'DTH' => 'Death',
			'INJ' => 'Injured',
			'PRD' => 'Partially Disabled',
			'FLD' => 'Fully Disabled',
			'ILN' => 'Illness'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_type_dropdown'))
{
	/**
	 * Get Surveyor Type Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__surveyor_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'P' => 'Preliminary',
			'F' => 'Final',
			'R' => 'Re-inspection'
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
		 * 		edit.claim.draft
		 */

		// Editable Permissions ?
		if(
			$status === IQB_CLAIM_STATUS_DRAFT
				&&
			( $CI->dx_auth->is_admin() || $CI->dx_auth->is_authorized('claims', 'edit.claim.draft') )
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

if ( ! function_exists('CLAIM__approval_constraint'))
{
	/**
	 * Is Claim Eligible to approve?
	 *
	 * Check if the given policy claim is valid for approval.
	 * The following criteria must be met:
	 *
	 * 		1. settlement_claim_amount must be set
     *  	2. claim_scheme_id must be set
     *  	3. assessment_brief must be set
	 *
	 * @param object $record 	Claim Record
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function CLAIM__approval_constraint($record, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Authorized Flag ?
		$__flag_authorized 		= TRUE;


		/**
		 * Check Claim Criteria
		 */
		if(
			$record->settlement_claim_amount === NULL
				||
			$record->claim_scheme_id === NULL
				||
			$record->assessment_brief === NULL
		)
		{
			$__flag_authorized = FALSE;

			$message = 'You must first set "Claim Settlement Amount", "Claim Scheme",  "Settlement Brief" & "Beema Samiti Report Headings" in order to approve a claim.';
		}

		/**
		 * Beema Samiti Reports
		 */
		if( $__flag_authorized  )
		{
			$CI->load->model('rel_claim_bsrs_heading_model');
			$rel_exists = $CI->rel_claim_bsrs_heading_model->rel_exists($record->id);

			if(!$rel_exists)
			{
				$__flag_authorized 	= FALSE;
				$message = 'You must first set "Beema Samiti Report Headings" in order to approve a claim.';
			}
		}




		// Terminate on Exit?
		if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
		{
			$CI =& get_instance();

			$CI->dx_auth->deny_access('deny', $message);
			exit(1);
		}

		return $__flag_authorized;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__settlement_category_dropdown'))
{
	/**
	 * Get Claim Settlement Category Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__settlement_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'OD' => 'Own Damage',
			'TP' => 'Third Party',
			'NA' => 'Not Applicable'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__settlement_subcategory_dropdown'))
{
	/**
	 * Get Claim Settlement Category Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__settlement_subcategory_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'DTH' => 'Death',
			'INJ' => 'Injured',
			'PRD' => 'Partially Disabled',
			'FLD' => 'Fully Disabled',
			'ILN' => 'Illness',
			'PRP' => 'Property Damage'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__flag_surveyor_voucher_dropdown'))
{
	/**
	 * Get Claim flag_surveyor_voucher Dropdown
	 *
	 * @return	bool
	 */
	function CLAIM__flag_surveyor_voucher_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_CLAIM_FLAG_SRV_VOUCHER_NOT_REQUIRED 	=> 'Not Required',
			IQB_CLAIM_FLAG_SRV_VOUCHER_REQUIRED 		=> 'Required',
			IQB_CLAIM_FLAG_SRV_VOUCHER_VOUCHERED 		=> 'Vouchered'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


