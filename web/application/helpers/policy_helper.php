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

			IQB_POLICY_STATUS_DRAFT 		=> 'Draft',
			IQB_POLICY_STATUS_VERIFIED 		=> 'Verified',
			IQB_POLICY_STATUS_ACTIVE 		=> 'Active',
			IQB_POLICY_STATUS_CANCELED 		=> 'Canceled',
			IQB_POLICY_STATUS_EXPIRED 		=> 'Expired'
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

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_STATUS_VERIFIED, IQB_POLICY_STATUS_ACTIVE]) )
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

if ( ! function_exists('get_policy_flag_dc_dropdown'))
{
	/**
	 * Get Policy's flag_dc dropdown
	 * The flag values indicate whether the policy has any of the following:
	 * 	- Corporate/direct discount
	 * 	- Agent commission
	 * 	- None
	 *
	 * @return	bool
	 */
	function get_policy_flag_dc_dropdown( $flag_blank_select = true )
	{
		$dropdown = [

			IQB_POLICY_FLAG_DC_AGENT_COMMISSION => 'Agent Commission',
			IQB_POLICY_FLAG_DC_DIRECT 			=> 'Direct/Corporate Discount',
			IQB_POLICY_FLAG_DC_NONE 			=> 'None'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_txn_status_dropdown'))
{
	/**
	 * Get Policy Transaction Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_txn_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_STATUS_DRAFT			=> 'Draft',
			IQB_POLICY_TXN_STATUS_VERIFIED		=> 'Verified',
			IQB_POLICY_TXN_STATUS_RI_APPROVED	=> 'RI Approved',
			IQB_POLICY_TXN_STATUS_VOUCHERED		=> 'Vouchered',
			IQB_POLICY_TXN_STATUS_INVOICED		=> 'Invoiced',
			IQB_POLICY_TXN_STATUS_ACTIVE		=> 'Active'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_txn_status_text'))
{
	/**
	 * Get Policy Transaction Status Text
	 *
	 * @return	string
	 */
	function get_policy_txn_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_txn_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_TXN_STATUS_RI_APPROVED, IQB_POLICY_TXN_STATUS_VOUCHERED, IQB_POLICY_TXN_STATUS_INVOICED, IQB_POLICY_TXN_STATUS_ACTIVE]) )
			{
				// Green
				$css_class = 'text-green';
			}
			else
			{
				// Orange
				$css_class = 'text-orange';
			}

			$text = '<strong class="'.$css_class.'">'.$text.'</strong>';
		}
		return $text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_transaction_type_dropdown'))
{
	/**
	 * Get Policy Transaction Type Dropdown
	 *
	 * @return	array
	 */
	function get_policy_transaction_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_TYPE_FRESH 		=> 'Fresh',
			IQB_POLICY_TXN_TYPE_RENEWAL 	=> 'Renewal',

			IQB_POLICY_TXN_TYPE_GENERAL 			=> 'General (Nil)',
			IQB_POLICY_TXN_TYPE_OWNERSHIP_TRANSFER 	=> 'Ownership Transfer',
			IQB_POLICY_TXN_TYPE_PREMIUM_UPGRADE 	=> 'Premium Upgrade',
			IQB_POLICY_TXN_TYPE_PREMIUM_REFUND 		=> 'Premium Refund',
			IQB_POLICY_TXN_TYPE_TERMINATE 			=> 'Terminate'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_transaction_type_endorsement_only_dropdown'))
{
	/**
	 * Get Policy Transaction Type (Endorsement Only) Dropdown
	 *
	 * @return	array
	 */
	function get_policy_transaction_type_endorsement_only_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_TYPE_GENERAL 			=> 'General (Nil)',
			IQB_POLICY_TXN_TYPE_OWNERSHIP_TRANSFER 	=> 'Ownership Transfer',
			IQB_POLICY_TXN_TYPE_PREMIUM_UPGRADE 	=> 'Premium Upgrade',
			IQB_POLICY_TXN_TYPE_PREMIUM_REFUND 		=> 'Premium Refund',
			IQB_POLICY_TXN_TYPE_TERMINATE 			=> 'Terminate'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_transaction_type_deletable'))
{
	/**
	 * Get Policy Transaction Type - Deletable only
	 *
	 * Endorsement Only Transaction Types are deletable from transactions tab.
	 *
	 * @return	array
	 */
	function get_policy_transaction_type_deletable( )
	{
		return  array_keys( get_policy_transaction_type_endorsement_only_dropdown(FALSE) );
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_transaction_type_computation_basis_dropdown'))
{
	/**
	 * Get Policy Transaction Computation Basis Dropdown
	 *
	 * @return	array
	 */
	function get_policy_transaction_type_computation_basis_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_CB_ANNUAL 			=> 'Annual/Complete',
			IQB_POLICY_TXN_CB_SHORT_TERM_RATE 	=> 'Short Term Rate',
			IQB_POLICY_TXN_CB_PRORATA 			=> 'Prorata',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_transaction_type_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function get_policy_transaction_type_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_transaction_type_dropdown();

		$text = $list[$key] ?? '';

		return $text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_installment_status_dropdown'))
{
	/**
	 * Get Policy Transaction Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_installment_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_INSTALLMENT_STATUS_DRAFT			=> 'Due',
			IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED		=> 'Vouchered',
			IQB_POLICY_INSTALLMENT_STATUS_INVOICED		=> 'Invoiced',
			IQB_POLICY_INSTALLMENT_STATUS_PAID			=> 'Paid'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_installment_status_text'))
{
	/**
	 * Get Policy Transaction Status Text
	 *
	 * @return	string
	 */
	function get_policy_installment_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_installment_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED, IQB_POLICY_INSTALLMENT_STATUS_INVOICED, IQB_POLICY_INSTALLMENT_STATUS_PAID]) )
			{
				// Green
				$css_class = 'text-green';
			}
			else
			{
				// Orange
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

		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft
		 *
		 * Editable Permissions Are
		 * 		edit.draft.policy
		 */

		// Editable Permissions ?
		if( $status ===  IQB_POLICY_STATUS_DRAFT )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policies', 'edit.draft.policy') )
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

if ( ! function_exists('is_policy_txn_editable'))
{
	/**
	 * Is Policy Transaction Editable?
	 *
	 * Check if the given policy transaction is editable.
	 *
	 * @param char $status 	Policy Transaction Status
	 * @param char $flag_current 	Is this Current Policy Transaction
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function is_policy_txn_editable($status, $flag_current, $terminate_on_fail = TRUE )
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
		 * 		edit.draft.transaction
		 */

		// Editable Permissions ?
		if( $status === IQB_POLICY_TXN_STATUS_DRAFT )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_TXN_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policy_transactions', 'edit.draft.transaction') )

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

if ( ! function_exists('_POLICY__partial_view__cost_calculation_table'))
{
	/**
	 * Get Cost Calculation Table Parital View
	 *
	 * @param integer $portfolio_id Portfolio ID
	 * @param string $view_for View For [regular|print]
	 * @return	string
	 */
	function _POLICY__partial_view__cost_calculation_table( $portfolio_id, $view_for = 'regular' )
	{
		$partial_view 	= '';
		$view_prefix 	= $view_for === 'print' ? '_print' : '';

		/**
         * AGRICULTURE - ALL SUB-PORTFOLIOs
         * ---------------------------------
         */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR)) )
		{
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_AGR";
		}

		/**
		 * MOTOR PORTFOLIOS- ALL SUB-PORTFOLIOs
         * ------------------------------------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MOTOR";
		}

		/**
         * FIRE - FIRE
         * -------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_FIRE_FIRE";
        }

        /**
         * FIRE - HOUSEHOLDER
         * -------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_FIRE_HHP";
        }

        /**
         * FIRE - LOSS OF PROFIT
         * ----------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_FIRE_LOP";
        }

		/**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_BRG";
        }

		/**
		 * MARINE PORTFOLIOS
		 * -----------------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MARINE";
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_BL";
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_CAR";
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_CPM";
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_EEI";
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_EAR";
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
			$partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_ENG_MB";
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_BB";
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_GPA";
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_PA";
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_PL";
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_CT";
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_CS";
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_CC";
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_EPA";
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_TMI";
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_FG";
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $partial_view = "policy_transactions/snippets/{$view_prefix}_cost_calculation_table_MISC_HI";
        }

        /**
         * Throw Exception
         */
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__partial_view__cost_calculation_table()]: No Cost Calculation Table View defined for supplied portfolio.");
		}

		return $partial_view;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__partial_view__premium_form'))
{
	/**
	 * Get Policy Transaction Premium Form View
	 *
	 * @param id $portfolio_id Portfolio ID
	 * @return	string
	 */
	function _POLICY__partial_view__premium_form( $portfolio_id )
	{
		$form_view = '';

		/**
         * AGRICULTURE - ALL SUB-PORTFOLIOs
         * ---------------------------------
         */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR)) )
		{
			$form_view = 'policy_transactions/forms/_form_premium_AGR';
		}

		/**
		 * MOTOR PORTFOLIOS
		 * ----------------
		 * For all type of motor portfolios, we have same package list
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$form_view = 'policy_transactions/forms/_form_premium_MOTOR';
		}

		/**
         * FIRE - FIRE
         * -------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_FIRE_FIRE';
        }

        /**
         * FIRE - HOUSEHOLDER
         * -------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_FIRE_HHP';
        }

        /**
         * FIRE - LOSS OF PROFIT
         * ----------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_FIRE_LOP';
        }

		/**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_BRG';
        }

		/**
		 * MARINE
		 * ------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$form_view = 'policy_transactions/forms/_form_premium_MARINE';
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_ENG_BL';
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_ENG_CAR';
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_ENG_CPM';
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_ENG_EEI';
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
			$form_view = 'policy_transactions/forms/_form_premium_ENG_EAR';
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_ENG_MB';
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_BB';
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_GPA';
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_PA';
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_PL';
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_CT';
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_CS';
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_CC';
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_EPA';
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_TMI';
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_FG';
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $form_view = 'policy_transactions/forms/_form_premium_MISC_HI';
        }


		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__partial_view__premium_form()]: No premium form defined for supplied portfolio.");
		}

		return $form_view;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__compute_short_term_premium'))
{
	/**
	 * Compute Short Term Policy Premium
	 *
	 * @param object $policy_record Policy Record
	 * @param object $pfs_record Portfolio Settings Record
	 * @param array $cost_table Cost Table computed by Specific cost table function
	 * @return	array
	 */
	function _POLICY__compute_short_term_premium( $policy_record, $pfs_record, $cost_table )
	{
		/**
		 * SHORT TERM POLICY?
		 * ---------------------
		 *
		 * If the policy is short term policy, we have to calculate the short term values
		 *
		 */
		$CI =& get_instance();
		$fy_record = $CI->fiscal_year_model->get_fiscal_year( $policy_record->issued_date );
		$short_term_info = _POLICY__get_short_term_info( $policy_record->portfolio_id, $fy_record, $policy_record->start_date, $policy_record->end_date );

		if(
			$pfs_record->flag_short_term === IQB_FLAG_YES
			&&
			$short_term_info['flag'] === IQB_FLAG_YES
			&&
			$policy_record->flag_short_term === IQB_FLAG_YES )
		{
			$short_term_record = $short_term_info['record'];

			$short_term_rate = $short_term_record->rate ?? 100.00;
			$short_term_rate = (float)$short_term_rate;

			// Compute Total Amount
			$cost_table['amt_total_premium'] = ($cost_table['amt_total_premium'] * $short_term_rate)/100.00;


			// Update Commissionable Amount and Commission
			$amt_commissionable = $cost_table['amt_commissionable'] ?? NULL;
			if($amt_commissionable)
			{
				$cost_table['amt_commissionable'] 	= ($cost_table['amt_commissionable'] * $short_term_rate)/100.00;
				$cost_table['amt_agent_commission'] = ($cost_table['amt_commissionable'] * $pfs_record->agent_commission)/100.00;
			}
		}

		return $cost_table;
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
        	throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__get_short_term_info()]: No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en})");
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

if ( ! function_exists('_POLICY_TRANSACTION__ri_approval_constraint'))
{
	/**
	 * RI Approval Constraint on Policy Transaction
	 *
	 * Check if the policy transaction record requires RI Approval and is Approved
	 * i.e.
	 * 		if RI Approval required and not approved yet, it returns TRUE
	 * 		FALSE otherwise.
	 *
	 * @param char 	$status 			Policy Transaction Status
	 * @param int 	$flag_ri_approval 	Policy Transaction flag_ri_approval
	 * @return	bool
	 */
	function _POLICY_TRANSACTION__ri_approval_constraint( $status, $flag_ri_approval )
	{
		$constraint = FALSE;

        // First check if it requires RI Approval
        if( (int)$flag_ri_approval === IQB_FLAG_ON )
        {
            // Transaction status must be "RI Approved"
            $constraint = $status !== IQB_POLICY_TXN_STATUS_RI_APPROVED;
        }

        return $constraint;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_INSTALLMENT__voucher_constraint'))
{
	/**
	 * Check voucher constraint for a policy installment
	 *
	 * Logic:
	 *
	 *  Case 1: First Installment
	 *      The first installment is only eligible for voucher if policy transaction record is eligible
	 *      i.e. either ri_approved or no ri_approval constraint with verified status
	 *
	 *  Case 2: Other installmemnts
	 *      The first installment of this transaction record has to be paid.
	 * 		And its status must be draft
	 *
	 * 	NOTE: You can only generate voucher for the given installment, only if voucher constraint is TRUE
	 *
	 *
	 * @param object 	$record 	Policy Installment Record
	 * @return	bool
	 */
	function _POLICY_INSTALLMENT__voucher_constraint( $record )
	{
		$passed = FALSE;

		/**
		 * Case 1: First Installment
		 */
		if( $record->flag_first == IQB_FLAG_ON )
		{
			$ri_approval_constraint = _POLICY_TRANSACTION__ri_approval_constraint($record->policy_transaction_status, $record->policy_transaction_flag_ri_approval);

			$passed = 	($record->policy_transaction_status === IQB_POLICY_TXN_STATUS_RI_APPROVED)
					        ||
				    	(	$record->policy_transaction_status === IQB_POLICY_TXN_STATUS_VERIFIED
				    			&&
		    				$ri_approval_constraint == FALSE
		    			);
		}
		/**
		 * Case 2: Other Installment
		 */
		else
		{
			$CI =& get_instance();
			$CI->load->model('policy_installment_model');

			$first_installment_status = $CI->policy_installment_model->first_installment_status($record->policy_transaction_id);

			$passed = 	(
							$first_installment_status === IQB_POLICY_INSTALLMENT_STATUS_PAID
								&&
							$record->status === IQB_POLICY_INSTALLMENT_STATUS_DRAFT
						);
		}

		return $passed;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__endorsement_pdf'))
{
    /**
     * Print Policy Endorsement/Transaction PDF
     *
     * @param array $data
     * @return  void
     */
    function _POLICY__endorsement_pdf( $data )
    {
    	$CI =& get_instance();

		/**
		 * Extract Policy Record and Policy Transaction Record
		 */
		$records 		= $data['records'];
		$type 			= $data['type'];

		if( $type == 'single' )
		{
			// check if this is not fresh/renewal transaction
			$record = $records[0] ?? NULL;

			if($record && in_array($record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
			{
				throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__endorsement_pdf()]: You can not have endrosement print of FRESH/RENEWAL Transaction/endorsement.");
			}
		}

		$schedule_view 	= 'policy_transactions/print/endorsement';

		$record = $records[0] ?? NULL;

		if( $record )
		{
			$CI->load->library('pdf');
	        $mpdf = $CI->pdf->load();
	        // $mpdf->SetMargins(10, 10, 5);
	        $mpdf->SetMargins(10, 5, 10, 5);
	        $mpdf->margin_header = 5;
	        $mpdf->margin_footer = 5;
	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Endorsement - {$record->code}");
	        $mpdf->SetAuthor($CI->settings->orgn_name_en);

	        /**
	         * Only Active Endorsement Does not have watermark!!!
	         */
	        if( $record->status !== IQB_POLICY_TXN_STATUS_ACTIVE )
	        {
	        	$mpdf->SetWatermarkText( 'ENDORSEMENT - ' . strtoupper(get_policy_txn_status_text($record->status)) );
	        }

	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $CI->load->view( $schedule_view, $data, TRUE);
	        $mpdf->WriteHTML($html);

	        $filename = "endorsement-all-{$record->code}.pdf";
	        // $mpdf->Output($filename,'D');      // make it to DOWNLOAD
	        $mpdf->Output();      // make it to DOWNLOAD
		}
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__endorsement_pdf()]: No endorsement found.");
		}



        // if( $action === 'save' )
        // {
        // 	$save_full_path = rtrim(INSQUBE_MEDIA_PATH, '/') . '/policies/' . $filename;
        // 	$mpdf->Output($save_full_path,'F');
        // }
        // else if($action === 'download')
        // {
		// 		$mpdf->Output($filename,'D');      // make it to DOWNLOAD
        // }
        // else
        // {
        // 	$mpdf->Output();
        // }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__schedule_pdf'))
{
    /**
     * Save or Print Policy Schedule.
     *
     * 1. Save the Original Policy Schedule (PDF)
     * 		Once the policy number is generated, the Original/First Copy of
     * 		policy is saved as pdf. This is required because the policy contents
     * 		change over the period of time via "Endorsement".
     *
     * 2. Print The Policy Schedule (PDF)
     * 		This action is called to generate current policy schedule's pdf.
     *
     * Filename: <policycode>.pdf
     *
     * @param array $data 		['record' => xxx, 'txn_record' => yyy]
     * @param string $action 	[save|print]
     * @return  void
     */
    function _POLICY__schedule_pdf( $data, $action )
    {
    	if( !in_array($action, ['save', 'print', 'download']) )
    	{
    		throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: Invalid Action({$action}).");
    	}

    	$CI =& get_instance();

		/**
		 * Extract Policy Record and Policy Transaction Record
		 */
		$record 		= $data['record'];
		$txn_record 	= $data['txn_record'];
		$schedule_view 	= _POLICY__get_schedule_view($record->portfolio_id);
		if( $schedule_view )
		{
			$CI->load->library('pdf');
	        $mpdf = $CI->pdf->load();
	        // $mpdf->SetMargins(10, 10, 5);
	        $mpdf->SetMargins(10, 5, 10, 5);
	        $mpdf->margin_header = 5;
	        $mpdf->margin_footer = 5;
	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Schedule - {$record->code}");
	        $mpdf->SetAuthor($CI->settings->orgn_name_en);

	        /**
	         * Only Active Policy Does not have watermark!!!
	         */
	        if( $action === 'print' ||  $action === 'download')
	        {
		        if( !in_array($record->status, [IQB_POLICY_STATUS_ACTIVE, IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED]))
		        {
		        	$mpdf->SetWatermarkText( 'DEBIT NOTE - ' . strtoupper(get_policy_status_text($record->status)) );
		        }
		    }

	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $CI->load->view( $schedule_view, $data, TRUE);
	        $mpdf->WriteHTML($html);
	        // $filename = $upload_path . "policy-{$record->code}.pdf";
	        $filename = "policy-{$record->code}.pdf";
	        if( $action === 'save' )
	        {
	        	$save_full_path = rtrim(INSQUBE_MEDIA_PATH, '/') . '/policies/' . $filename;
	        	$mpdf->Output($save_full_path,'F');
	        }
	        else if($action === 'download')
	        {
	        	$mpdf->Output($filename,'D');      // make it to DOWNLOAD
	        }
	        else
	        {
	        	$mpdf->Output();
	        }
		}
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: No schedule view exists for given portfolio({$record->portfolio_name}).");
		}
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_schedule_view'))
{
    /**
     * Get the policy schedule view
     *
     * @param integer $portfolio_id Portfolio ID
     * @return  void
     */
    function _POLICY__get_schedule_view( $portfolio_id )
    {
    	$schedule_view = '';
    	$portfolio_id  = (int)$portfolio_id;

		switch ($portfolio_id)
		{
			// AGRICULTURE - CROP SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_CROP_ID:
				$schedule_view = 'policies/print/schedule_AGR_CROP';
				break;

			// AGRICULTURE - CATTLE SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_CATTLE_ID:
				$schedule_view = 'policies/print/schedule_AGR_CATTLE';
				break;

			// AGRICULTURE - POULTRY SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_POULTRY_ID:
				$schedule_view = 'policies/print/schedule_AGR_POULTRY';
				break;

			// AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_FISH_ID:
				$schedule_view = 'policies/print/schedule_AGR_FISH';
				break;

			// AGRICULTURE - BEE SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_BEE_ID:
				$schedule_view = 'policies/print/schedule_AGR_BEE';
				break;

			// Motor
			case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
			case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
			case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
					$schedule_view = 'policies/print/schedule_MOTOR';
				break;

			// FIRE - FIRE
			case IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID:
					$schedule_view = 'policies/print/schedule_FIRE_FIRE';
				break;

			// FIRE - HOUSEHOLDER
			case IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID:
					$schedule_view = 'policies/print/schedule_FIRE_HHP';
				break;

			// FIRE - LOSS OF PROFIT
			case IQB_SUB_PORTFOLIO_FIRE_LOP_ID:
					$schedule_view = 'policies/print/schedule_FIRE_LOP';
				break;


			// Burglary
			case IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID:
			case IQB_SUB_PORTFOLIO_MISC_BRGHB_ID:
			case IQB_SUB_PORTFOLIO_MISC_BRGCS_ID:
					$schedule_view = 'policies/print/schedule_MISC_BRG';
				break;

			// Marine
			case IQB_SUB_PORTFOLIO_MARINE_AIR_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_MARINE_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_OPEN_MARINE_ID:
			case IQB_SUB_PORTFOLIO_MARINE_ROAD_AIR_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_ROAD_TANSIT_ID:
				$schedule_view = 'policies/print/schedule_MARINE';
				break;

			// ENGINEERING - BOILER EXPLOSION
	        case IQB_SUB_PORTFOLIO_ENG_BL_ID:
	        	$schedule_view = 'policies/print/schedule_ENG_BL';
				break;

			// ENGINEERING - CONTRACTOR ALL RISK
			case IQB_SUB_PORTFOLIO_ENG_CAR_ID:
				$schedule_view = 'policies/print/schedule_ENG_CAR';
				break;

			// ENGINEERING - CONTRACTOR PLANT & MACHINARY
	        case IQB_SUB_PORTFOLIO_ENG_CPM_ID:
	        	$schedule_view = 'policies/print/schedule_ENG_CPM';
				break;

			// ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
			case IQB_SUB_PORTFOLIO_ENG_EEI_ID:
				$schedule_view = 'policies/print/schedule_ENG_EEI';
				break;

			// ENGINEERING - ERECTION ALL RISKS
			case IQB_SUB_PORTFOLIO_ENG_EAR_ID:
				$schedule_view = 'policies/print/schedule_ENG_EAR';
				break;

			// ENGINEERING - MACHINE BREAKDOWN
			case IQB_SUB_PORTFOLIO_ENG_MB_ID:
				$schedule_view = 'policies/print/schedule_ENG_MB';
				break;

			// MISCELLANEOUS - BANKER'S BLANKET(BB)
			case IQB_SUB_PORTFOLIO_MISC_BB_ID:
				$schedule_view = 'policies/print/schedule_MISC_BB';
				break;

			// MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
			case IQB_SUB_PORTFOLIO_MISC_GPA_ID:
				$schedule_view = 'policies/print/schedule_MISC_GPA';
				break;

			// MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
			case IQB_SUB_PORTFOLIO_MISC_PA_ID:
				$schedule_view = 'policies/print/schedule_MISC_PA';
				break;

			// MISCELLANEOUS - PUBLIC LIABILITY(PL)
			case IQB_SUB_PORTFOLIO_MISC_PL_ID:
				$schedule_view = 'policies/print/schedule_MISC_PL';
				break;

			// MISCELLANEOUS - CASH IN TRANSIT
			case IQB_SUB_PORTFOLIO_MISC_CT_ID:
				$schedule_view = 'policies/print/schedule_MISC_CT';
				break;

			// MISCELLANEOUS - CASH IN SAFE
			case IQB_SUB_PORTFOLIO_MISC_CS_ID:
				$schedule_view = 'policies/print/schedule_MISC_CS';
				break;

			// MISCELLANEOUS - CASH IN COUNTER
			case IQB_SUB_PORTFOLIO_MISC_CC_ID:
				$schedule_view = 'policies/print/schedule_MISC_CC';
				break;

			// MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
			case IQB_SUB_PORTFOLIO_MISC_EPA_ID:
				$schedule_view = 'policies/print/schedule_MISC_EPA';
				break;

			// MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
			case IQB_SUB_PORTFOLIO_MISC_TMI_ID:
				$schedule_view = 'policies/print/schedule_MISC_TMI';
				break;

			// MISCELLANEOUS - FIDELITY GUARANTEE (FG)
			case IQB_SUB_PORTFOLIO_MISC_FG_ID:
				$schedule_view = 'policies/print/schedule_MISC_FG';
				break;

			// MISCELLANEOUS - HEALTH INSURANCE (HI)
			case IQB_SUB_PORTFOLIO_MISC_HI_ID:
				$schedule_view = 'policies/print/schedule_MISC_HI';
				break;


			default:
				# code...
				break;
		}

		return $schedule_view;
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('policy_nr_title'))
{
	/**
	 * Get policy number title based on policy status
	 *
	 * If status is draft/verify - it will show label as "Debit No" else "Policy No"
	 *
	 * @param char $status 	Policy Status
	 * @param string $lang 	Language
	 * @return	bool
	 */
	function policy_nr_title( $status, $lang = 'np' )
	{
		if( in_array($status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_VERIFIED]) )
		{
			$label = [
				'np' => 'डेबिट नोट नं.',
				'en' => 'Debit Note No.'
			];
		}
		else
		{
			$label = [
				'np' => 'बीमालेख नं.',
				'en' => 'Policy No.'
			];
		}

		return $label[$lang];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('basic_premium_validation_rules'))
{
	/**
	 * Get common/basic premium validation rules for all portfolios
	 *
	 * @param integer $portfolio_id 	Portfolio ID
	 * @param object $pfs_record		Portfolio Setting Record
	 * @return	array
	 */
	function basic_premium_validation_rules( $portfolio_id, $pfs_record )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $portfolio_id );

		$basic_rules = [
			[
                'field' => 'amt_stamp_duty',
                'label' => 'Stamp Duty(Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                '_type'     => 'text',
                '_default' 	=> $pfs_record->stamp_duty,
                '_required' => true
            ],
			[
                'field' => 'txn_details',
                'label' => 'Details/सम्पुष्टि विवरण',
                'rules' => 'trim|required|htmlspecialchars',
                '_type'     => 'textarea',
                '_id'		=> 'txn-details',
                '_required' => true
            ],
            [
                'field' => 'template_reference',
                'label' => 'Load from endorsement templates',
                'rules' => 'trim|integer|max_length[8]',
                '_key' 		=> 'template_reference',
                '_id'		=> 'template-reference',
                '_type'     => 'dropdown',
                '_data' 	=> IQB_BLANK_SELECT + $template_dropdown,
                '_required' => false
            ],
            [
                'field' => 'remarks',
                'label' => 'Remarks/कैफियत',
                'rules' => 'trim|htmlspecialchars',
                '_type'     => 'textarea',
                '_required' => false
            ]
		];

		return $basic_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('installment_validation_rules'))
{
	/**
	 * Get premium installment validation rules for all portfolios
	 *
	 * @param integer $portfolio_id 	Portfolio ID
	 * @param object $pfs_record		Portfolio Setting Record
	 * @return	array
	 */
	function installment_validation_rules( $portfolio_id, $pfs_record )
	{
		$rules = [];

		if($pfs_record->flag_installment === IQB_FLAG_YES )
		{
			$CI =& get_instance();

			// Let's have the Endorsement Templates
			$CI->load->model('policy_installment_model');

			$rules = $CI->policy_installment_model->validation_rules;
		}

		return $rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_installments_by_txn'))
{
	/**
	 * Get the list of installments by a policy transaction
	 *
	 * @param integer $policy_installment_id 	Policy TXN ID
	 * @return	array
	 */
	function get_installments_by_txn( $policy_installment_id )
	{
		$CI =& get_instance();
		$CI->load->model('policy_installment_model');

		return $CI->policy_installment_model->get_many_by_policy_transaction($policy_installment_id);
	}
}

// ------------------------------------------------------------------------


