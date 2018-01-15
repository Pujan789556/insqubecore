<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube RI Helper Functions
 *
 * This file contains helper functions related to RI
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('RI__ac_basic_dropdown'))
{
	/**
	 * Get RI Account Basic Dropdown
	 *
	 * @return	string
	 */
	function RI__ac_basic_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_SETUP_AC_BASIC_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__treaty_types_dropdown'))
{
	/**
	 * Get RI Treaty Types Dropdown
	 *
	 * @return	string
	 */
	function RI__treaty_types_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_TREATY_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__compute_flag_ri_approval'))
{
	/**
	 * Compute flag_ri_approval value for given SI amount for given portfolio
	 *
	 * @param int $portfolio_id
	 * @param decimal $amt_sum_insured
	 * @return int
	 */
	function RI__compute_flag_ri_approval( $portfolio_id, $amt_sum_insured )
	{
		$CI =& get_instance();

		$CI->load->model('ri_setup_treaty_model');
		$treaty_record = $CI->ri_setup_treaty_model->get_treaty_by_portfolio($portfolio_id);

		if( !$treaty_record )
		{
			// NO Treaty Setup for this portfolio!
			throw new Exception("Exception [Helper: ri_helper][Method: RI__compute_flag_ri_approval()]: No treaty found for supplied portfolio.");
		}

		/**
		 * Get the flag value based on treaty type
		 */
		$flag 		= IQB_FLAG_OFF;
		$treaty_type_id = (int)$treaty_record->treaty_type_id;

		/**
		 * Quota Share Never goes to FAC
		 */
		if($treaty_type_id == IQB_RI_TREATY_TYPE_QT )
		{
			$flag = IQB_FLAG_OFF;
		}

		/**
		 * Surplus OR Quota Share & Surplus
		 */
		else if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_SP, IQB_RI_TREATY_TYPE_QS]) )
		{
			$flag = RI__compute_flag_ri_approval__QS_SP( $treaty_record, $amt_sum_insured );
		}

		/**
		 * @TODO: What about EOL?
		 */
		else
		{

		}
		return $flag;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__compute_flag_ri_approval__QS_SP'))
{
	/**
	 * Compute flag_ri_approval value for given SI amount for given portfolio
	 *
	 * Treaty Types:
	 * 		1. Surplus
	 * 		2. Quota Share & Surplus
	 *
	 * @param int $portfolio_id
	 * @param decimal $amt_sum_insured
	 * @return int
	 */
	function RI__compute_flag_ri_approval__QS_SP( $treaty_record, $amt_sum_insured )
	{
		/**
		 * Surplus or Quota share & Surplus
		 * Let's see if distribution goes to FAC
		 */

		$si_gross 				= $amt_sum_insured;
		$si_comp_cession 		= 0.00;
		$si_treaty_total 		= 0.00;
		$si_treaty_retaintion 	= 0.00;
		$si_treaty_1st_surplus 	= 0.00;
		$si_treaty_2nd_surplus 	= 0.00;
		$si_treaty_3rd_surplus 	= 0.00;
		$si_treaty_fac 			= 0.00;

		// Compulsory Cession
		if( $treaty_record->flag_comp_cession_apply == IQB_FLAG_ON )
		{
			// percent or default max
			$percent_amount 	= ( $si_gross * $treaty_record->comp_cession_percent ) / 100.00;
			$default_max_amount = $treaty_record->comp_cession_max_amt;
			$si_comp_cession 	= $percent_amount < $default_max_amount ? $percent_amount : $default_max_amount;
		}

		// SI Treaty Total (95%)
		$si_treaty_total = $si_gross - $si_comp_cession;


		// Get Max Retention Amount
		$qs_max_ret_amt = $treaty_record->qs_max_ret_amt;
		if( $si_treaty_total > $qs_max_ret_amt )
		{
			$si_treaty_retaintion = $qs_max_ret_amt;
		}
		else
		{
			// All SI consumed within retaintion
			return IQB_FLAG_OFF;
		}

		// Compute 1st surplus
		$remained_si = $si_treaty_total - $si_treaty_retaintion;
		$max_1st_surplus_amount = $treaty_record->qs_lines_1 * $qs_max_ret_amt;
		if( $remained_si > $max_1st_surplus_amount )
		{
			$si_treaty_1st_surplus = $max_1st_surplus_amount;
		}
		else
		{
			// All SI consumed within 1st surplus
			return IQB_FLAG_OFF;
		}

		// Compute 2nd surplus
		$remained_si = $remained_si - $si_treaty_1st_surplus;
		$max_2nd_surplus_amount = $treaty_record->qs_lines_2 * $qs_max_ret_amt;
		if( $remained_si > $max_2nd_surplus_amount )
		{
			$si_treaty_2nd_surplus = $max_2nd_surplus_amount;
		}
		else
		{
			// All SI consumed within 2nd surplus
			return IQB_FLAG_OFF;
		}

		// Compute 3rd surplus
		$remained_si = $remained_si - $si_treaty_2nd_surplus;
		$max_3rd_surplus_amount = $treaty_record->qs_lines_3 * $qs_max_ret_amt;
		if( $remained_si > $max_3rd_surplus_amount )
		{
			$si_treaty_3rd_surplus = $max_3rd_surplus_amount;
		}
		else
		{
			// All SI consumed within 2nd surplus
			return IQB_FLAG_OFF;
		}

		// We do have a FAC SI
		return IQB_FLAG_ON;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__distribute'))
{
	/**
	 * Build/Update RI Distribution
	 */
	function RI__distribute( $policy_transaction_record, $installment_data )
	{
		$CI =& get_instance();

		/**
		 * Get RI Treaty for this Portfolio
		 * @TODO: Cache Result
		 */
		$CI->load->model('ri_setup_treaty_model');
		$treaty_record = $CI->ri_setup_treaty_model->get_treaty_by_portfolio($policy_transaction_record->portfolio_id);

		if(!$treaty_record )
		{
			// NO Treaty Setup for this portfolio!
			throw new Exception("Exception [Helper: ri_helper][Method: RI__distribute()]: No treaty found for supplied portfolio.");
		}

		/**
		 * Treaty Tax and Commission Record
		 * @TODO: Cache Result
		 */
		$treaty_tax_comm_record = $CI->ri_setup_treaty_model->get_treaty_tax_commission($treaty_record->id);


		/**
		 * Get RI Treaty Distribution for this Treaty
		 * @TODO: Cache Result
		 */
		$treaty_distribution_list = $CI->ri_setup_treaty_model->get_treaty_distribution_by_treaty($treaty_record->id);


		/**
		 * Build Treaty Distribution based on Treaty Type
		 */
		$result 		= NULL;
		$treaty_type_id = (int)$treaty_record->treaty_type_id;
		switch($treaty_type_id)
		{
			/**
			 * Surplus
			 */
			case IQB_RI_TREATY_TYPE_SP:
				$result = RI__distribute_surplus($policy_transaction_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
				break;

			/**
			 * Quota Share
			 */
			case IQB_RI_TREATY_TYPE_QT:
				$result = RI__distribute_quota_share($policy_transaction_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
				break;

			/**
			 * Quota Share & Surplus
			 */
			case IQB_RI_TREATY_TYPE_QS:
				$result = RI__distribute_quota_share_surplus($policy_transaction_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
				break;

			default:
				break;
		}


		echo '<pre>';
		print_r($result);
		exit;

		// Quota Share
		// Surplus
		// Quota Share & Surplus

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__distribute_quota_share'))
{
	/**
	 * Build/Update RI Distribution by Type - Quota Share
	 *
	 * @param object $policy_transaction_record
	 * @param object $treaty_record
	 * @param object $treaty_tax_comm_record
	 * @param array $treaty_distribution_list
	 * @return type
	 */
	function RI__distribute_quota_share($policy_transaction_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list )
	{
	}
}


// ------------------------------------------------------------------------



