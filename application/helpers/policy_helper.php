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

if ( ! function_exists('get_policy_status_dropdown'))
{
	/**
	 * Get Policy Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [

			/**
			 * Policy Status - Draft
			 *
			 * This is a newly created Debit Note.
			 * One can edit/delete or make a final draft that can be verified.
			 *
			 * Action Allowed
			 * 		1. Edit
			 * 		2. Delete
			 * 		3. Send to Verify
			 * 		4. Generate Schedule (Marked: Draft)
			 *
			 * Upper Status Level: Verifiable
			 */
			IQB_POLICY_STATUS_DRAFT => 'Draft',

			/**
			 * Policy Status - Unverified
			 *
			 * This is a final draft of Debit Note.
			 * Now, only Verifier User can Edit this note if changes are to be made
			 *
			 * Action Allowed
			 * 		1. Edit
			 * 		2. Verify
			 * 		3. Revert to Draft
			 * 		4. Generate Schedule (Marked: Unverified)
			 *
			 * Upper Status Level: Verified
			 * Lower Status Level: Draft
			 */
			IQB_POLICY_STATUS_UNVERIFIED => 'Unverified',

			/**
			 * Policy Status - Verified
			 *
			 * This is a verified of Debit Note.
			 * Now, you can proceed for making payment of this policy OR back to unverified
			 *
			 * Action Allowed
			 * 		1. Make Payment
			 * 		2. Un-verify
			 * 		3. Generate Schedule (Marked: Verified)
			 *
			 * Upper Status Level: Paid
			 * Lower Status Level: Unverified
			 */
			IQB_POLICY_STATUS_VERIFIED => 'Verified',

			/**
			 * Policy Status - Paid
			 *
			 * This is verified-paid Debit Note.
			 * Once you make payment, you can now generate payment receipt.
			 * When this status is made, you have "Policy Number" generated and stored in database.
			 *
			 * Now, you can issue invoice, get final policy schedule.
			 *
			 * Generate Invoice
			 * 		Once you make request generat Invoice, A fresh Invoice is generated along with its original PDF.
			 * 		The first time you download/print, it will have status updated as "Printed". The next time onward,
			 * 		the invoice will have "Duplicate Copy" marked.
			 *
			 * 		Upon this action completion, The Policy will be final and status upgraded to "Active".
			 *
			 * @TODO: Policy Cancelation
			 * 		What happens when you cancel a policy?
			 * 		- in transactional amount & its distribution - RI, Commissions etc, invoices and other related place
			 *
			 * Action Allowed
			 * 		1. Generate Invoice
			 * 		2. Print Receipt
			 * 		3. Cancel Policy
			 * 		4. Generate Schedule (Marked: Paid)
			 *
			 * Upper Status Level: Active | Cancel
			 * Lower Status Level: Verified
			 */
			IQB_POLICY_STATUS_PAID => 'Paid',

			/**
			 * Policy Status - Active
			 *
			 * This is Final Policy.
			 * You can now print Invoice, Print Receipt, print Policy Schedule or Cancel Policy.
			 *
			 * @TODO: Policy Cancelation
			 * 		What do you if you have to cancel policy at this stage?
			 *
			 * Action Allowed
			 * 		1. Print Invoice
			 * 		2. Print Receipt
			 * 		3. Cancel Policy
			 * 		4. Generate Schedule (Final Schedule)
			 *
			 * Upper Status Level: Canceled|Expired
			 * Lower Status Level: Paid
			 */
			IQB_POLICY_STATUS_ACTIVE => 'Active',

			/**
			 * Policy Status - Canceled
			 *
			 * This is a canceled policy.
			 * You have to undo all the previous transactions to get to this status.
			 *
			 * @TODO: To be discussed with the stakeholder and finalized its business process
			 *
			 * Action Allowed
			 * 		3. Generate Schedule (Marked: Canceled)
			 *
			 * Upper Status Level: -
			 * Lower Status Level: Active
			 */
			IQB_POLICY_STATUS_CANCELED => 'Canceled',

			/**
			 * Policy Status - Expired
			 *
			 * This status is automatically upgraded when a policy expires.
			 * It will be done by cron-job.
			 *
			 * You can now renew this policy.
			 *
			 * Tasks carried while upgrading to this status
			 * 		1. Set Status Flag to "Expired"
			 * 		2. Send followup notification to client regarding this expiry
			 * 		3. Create a followup notification to marketing agent who brought
			 * 			this policy.
			 *
			 * Action Allowed
			 * 		3. Renew This Policy
			 *
			 * Upper Status Level: -
			 * Lower Status Level: Active
			 */
			IQB_POLICY_STATUS_EXPIRED => 'Expired'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_policy_status_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function get_policy_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_status_dropdown();

		$text = $list[$key];

		if($formatted)
		{
			if($key === IQB_POLICY_STATUS_ACTIVE )
			{
				// Green
				$css_class = 'text-green';
			}
			else if( $key === IQB_POLICY_STATUS_EXPIRED )
			{
				// Gray
				$css_class = 'text-gray';
			}
			else if( $key === IQB_POLICY_STATUS_CANCELED )
			{
				// Red
				$css_class = 'text-red';
			}
			else
			{
				// Red
				$css_class = 'text-orange';
			}

			$text = '<strong class="'.$css_class.'">'.$text.'</strong>';
		}
		return $text;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_policy_editable'))
{
	/**
	 * Is Policy Editable?
	 *
	 * Check if the given policy is editable.
	 * We need this helper function as it is used multiple controllers & models
	 *
	 * @param char $status 	Policy Status
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function is_policy_editable( $status, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 		= FALSE;
		$__flag_editable_status = FALSE;

		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft | unverified
		 *
		 * Editable Permissions Are
		 * 		edit.draft.policy | edit.unverified.policy
		 */
		$editable_status 		= [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED];

		// Editable Status?
		if( in_array($status, $editable_status) )
		{
			$__flag_editable_status = TRUE;
		}

		// Editable Permissions ?
		if( $__flag_editable_status )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policies', 'edit.draft.policy') )

				||

				( $status === IQB_POLICY_STATUS_UNVERIFIED &&  $CI->dx_auth->is_authorized('policies', 'edit.unverified.policy') )

			)
			{
				$__flag_authorized = TRUE;
			}
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

