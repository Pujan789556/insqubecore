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

if ( ! function_exists('_PREMIUM_OVERVIEW_CARD_partial_view_by_portfolio'))
{
	/**
	 * Get Premium Overview Card Partial View Name by Portfolio
	 *
	 * @param integer $portfolio_id Portfolio ID
	 * @return	string
	 */
	function _PREMIUM_OVERVIEW_CARD_partial_view_by_portfolio( $portfolio_id )
	{
		$partial_view = '';
		switch ($portfolio_id)
		{
			// Motor
			case IQB_MASTER_PORTFOLIO_MOTOR_ID:
				$partial_view = 'premium/snippets/_card_overview_MOTOR';
				break;

			default:
				# code...
				break;
		}
		return $partial_view;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_short_term_flag'))
{
    /**
     * Get Short Term Policy Flag
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  char
     */
    function _POLICY__get_short_term_flag( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $info = _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date );
        return $info['flag'];
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__is_short_term'))
{
    /**
     * Is Policy Short Term?
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  bool
     */
    function _POLICY__is_short_term( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $info = _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date );
        return $info['flag'] === IQB_FLAG_NO;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_short_term_info'))
{
    /**
     * Get Short Term Policy Info
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  array
     */
    function _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $CI =& get_instance();
        $CI->load->model('portfolio_setting_model');

        $false_return = [
            'flag'      => IQB_FLAG_NO,
            'record'    => NULL
        ];

        /**
         * Current Fiscal Year Record & Portfolio Settings for This Fiscal Year
         */
        $pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($fy_record->id, $portfolio_id);
        if(!$pfs_record)
        {
        	throw new Exception("No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en})");
        }

        // update false return with default duration
        $false_return['default_duration'] = (int)$pfs_record->default_duration;

        if($pfs_record->flag_short_term === IQB_FLAG_NO )
        {
            return $false_return;
        }


        /**
         * Let's find if the policy duration falls under Short Term Duration List
         *
         * Calculate the Number of Days between given two dates
         */
        $start_timestamp    = strtotime($start_date);
        $end_timestamp      = strtotime($end_date);
        $difference         = $end_timestamp - $start_timestamp;
        $days               = floor($difference / (60 * 60 * 24));
        $default_duration 	= (int)$pfs_record->default_duration;

        /**
         * Supplied Duration === Default Duration?
         */
        if($days == $default_duration)
        {
        	return $false_return;
        }


        $short_term_policy_rate = $pfs_record->short_term_policy_rate ? json_decode($pfs_record->short_term_policy_rate) : [];

        // Build The Duration List
        $duration_list = [$default_duration];
        foreach($short_term_policy_rate as $spr)
        {
            $duration_list[] = (int)$spr->duration;
        }
        sort($duration_list);
        $duration_list = array_values(array_unique($duration_list));

        // Index
        $index_days       = array_search($days, $duration_list);
        $index_default    = array_search($default_duration, $duration_list);

        $element_count = count($duration_list);

        // If days is exactly found in duration list, bang...
        $flag_short_term = FALSE;
        $found          = FALSE;
        $found_index    = 0;
        if($index_days !== FALSE)
        {
            // We found the key
            // Last key? then its not short term policy
            $flag_short_term    = $index_days !== $index_default;
            $found_index        = $index_days;
        }
        else
        {
            // Let's loop through to find where it falls
            foreach ($duration_list as $key => $value)
            {
                if( !$found && $days < $value )
                {
                    $found = TRUE;
                    $found_index = $key;
                }
            }
            // Let's check if we have found
            $flag_short_term = $found_index !== $index_default;
        }

        // is Short TERM?
        if( !$flag_short_term )
        {
            return $false_return;
        }

        // Now Let's Get the Short Term Duration Record
        $spr_record = NULL;
        foreach($short_term_policy_rate as $spr)
        {
            $spr_duration = (int)$spr->duration;
            if($spr_duration === $duration_list[$found_index] )
            {
                $spr_record = $spr;
            }
        }

        return [
            'flag'      		=> IQB_FLAG_YES,
            'record'    		=> $spr_record,
            'default_duration' 	=> $default_duration
        ];
    }
}

// ------------------------------------------------------------------------


