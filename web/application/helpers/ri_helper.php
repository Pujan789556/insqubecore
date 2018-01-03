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

if ( ! function_exists('ri_ac_basic_dropdown'))
{
	/**
	 * Get RI Account Basic Dropdown
	 *
	 * @return	string
	 */
	function ri_ac_basic_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('ri_treaty_types_dropdown'))
{
	/**
	 * Get RI Treaty Types Dropdown
	 *
	 * @return	string
	 */
	function ri_treaty_types_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('ri_distribution'))
{
	/**
	 * Build/Update RI Distribution
	 */
	function ri_distribution( $portfolio_id, $txn_record )
	{
		$CI =& get_instance();

		/**
		 * Get RI Treaty for this Portfolio
		 * @TODO: Cache Result
		 */
		$CI->load->model('ri_setup_treaty_model');
		$treaty_record = $CI->ri_setup_treaty_model->get_treaty_by_portfolio($portfolio_id);

		if(!$treaty_record )
		{
			// NO Treaty Setup for this portfolio!
			throw new Exception("Exception [Helper: ri_helper][Method: ri_distribution()]: No treaty found for supplied portfolio.");
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
				$result = ri_distribution_surplus($txn_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
				break;

			/**
			 * Quota Share
			 */
			case IQB_RI_TREATY_TYPE_QT:
				$result = ri_distribution_quota_share($txn_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
				break;

			/**
			 * Quota Share & Surplus
			 */
			case IQB_RI_TREATY_TYPE_QS:
				$result = ri_distribution_quota_share_surplus($txn_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list );
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

if ( ! function_exists('ri_distribution_quota_share'))
{
	/**
	 * Build/Update RI Distribution by Type - Quota Share
	 *
	 * @param object $txn_record
	 * @param object $treaty_record
	 * @param object $treaty_tax_comm_record
	 * @param array $treaty_distribution_list
	 * @return type
	 */
	function ri_distribution_quota_share($txn_record, $treaty_record, $treaty_tax_comm_record, $treaty_distribution_list )
	{
		$CI =& get_instance();

		/**
		 * Check if RI-Distribution Needed?
		 *
		 * If SI value is less or equal to defined retention amount or max retention amount,
		 * we do not need to do RI Distribution
		 *
		 * @TODO: WHAT IS THE BUSINESS LOGIC FOR subsequent endorsement/transactions with/without "$txn_record->amt_sum_insured" ?
		 */
		$qs_max_ret_amt = floatval($treaty_record->qs_max_ret_amt);
		$qs_def_ret_amt = floatval($treaty_record->qs_def_ret_amt);

		if(
			($qs_def_ret_amt && $txn_record->amt_sum_insured <= $qs_def_ret_amt )
			OR
			$txn_record->amt_sum_insured <= $qs_max_ret_amt
		)
		{
			return TRUE;
		}


		/**
		 * Build Distribution Data
		 */
		$amt_total_premium 		= floatval($txn_record->amt_total_premium);
		$qs_retention_percent 	= floatval($treaty_record->qs_retention_percent);
		$qs_quota_percent 		= floatval($treaty_record->qs_quota_percent);


		$distribution_data = [
			'amt_retention' 			=> ($amt_total_premium * $qs_retention_percent) / 100.00,
			'amt_distrib_treaty_quota' 	=> ($amt_total_premium * $qs_quota_percent) / 100.00,
		];




		echo '<pre>';
		print_r($txn_record);
		print_r($treaty_record);
		print_r($treaty_distribution_list);
		print_r($treaty_tax_comm_record);
		exit;

		// Quota Share
		// Surplus
		// Quota Share & Surplus

	}
}


// ------------------------------------------------------------------------



