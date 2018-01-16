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
	function RI__distribute( $policy_installment_id )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('policy_installment_model');
		$CI->load->model('ri_setup_treaty_model');
		$CI->load->model('ri_transaction_model');

		/**
		 * Get RI Treaty for this Portfolio
		 */
		$policy_installment_record = $CI->policy_installment_model->get( $policy_installment_id );
		if(!$policy_installment_record)
		{
			throw new Exception("Exception [Helper: ri_helper][Method: RI__distribute()]: No installment record found.");
		}

		/**
		 * Get RI Treaty for this Portfolio
		 */
		$treaty_record = $CI->ri_setup_treaty_model->get_treaty_by_portfolio($policy_installment_record->portfolio_id);
		if(!$treaty_record )
		{
			// NO Treaty Setup for this portfolio!
			throw new Exception("Exception [Helper: ri_helper][Method: RI__distribute()]: No treaty found for supplied portfolio.");
		}


		/**
		 * Distribution Logic:
		 *
		 * 		1. FRESH/Renewal Policy Issue - RI Distribution
		 * 		2. Endorsement - RI Distribution - @TODO
		 * 			a. SI increase/decrease
		 * 			b. Premium increase/decrease
		 * 			c. SI & Premium increase/decrease
		 */

		/**
		 * Build Treaty Distribution based on Treaty Type
		 */
		$ri_data 		= [];
		$treaty_type_id = (int)$treaty_record->treaty_type_id;
		switch($treaty_type_id)
		{
			/**
			 * Surplus, Quota Share & Surplus
			 */
			case IQB_RI_TREATY_TYPE_QS:
			case IQB_RI_TREATY_TYPE_SP:
				$ri_data = RI__distribute__QS_SP($policy_installment_record, $treaty_record);
				break;

			/**
			 * Quota Share
			 */
			case IQB_RI_TREATY_TYPE_QT:
				$ri_data = RI__distribute__QT($policy_installment_record, $treaty_record);
				break;

			/**
			 * EOL
			 */
			case IQB_RI_TREATY_TYPE_EOL:
				$ri_data = RI__distribute__EOL($policy_installment_record, $treaty_record);
				break;


			default:
				break;
		}

		if( $ri_data )
		{
			return $CI->ri_transaction_model->add($ri_data);
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__distribute__QS_SP'))
{
	/**
	 * RI Distribute for SP & QS treaty types
	 *
	 * Treaty Types:
	 * 		1. Surplus
	 * 		2. Quota Share & Surplus
	 *
	 * @param object $policy_installment_record
	 * @param object $treaty_record
	 * @return mixed
	 */
	function RI__distribute__QS_SP( $policy_installment_record, $treaty_record )
	{
		$CI =& get_instance();

		/**
		 * SI & Premium Variables
		 */
		$si_gross 				= $policy_installment_record->policy_transaction_amt_sum_insured;
		$si_comp_cession 		= NULL;
		$si_treaty_total 		= NULL;
		$si_treaty_retaintion 	= NULL;
		$si_treaty_quota 		= NULL;
		$si_treaty_1st_surplus 	= NULL;
		$si_treaty_2nd_surplus 	= NULL;
		$si_treaty_3rd_surplus 	= NULL;
		$si_treaty_fac 			= NULL;

		$premium_gross 				= $policy_installment_record->amt_total_premium;
		$premium_pool 				= floatval($policy_installment_record->amt_pool_premium);
		$premium_net 				= $premium_gross - $premium_pool;
		$premium_comp_cession 		= NULL;
		$premium_treaty_total 		= NULL;
		$premium_treaty_retaintion 	= NULL;
		$premium_treaty_quota 		= NULL;
		$premium_treaty_1st_surplus = NULL;
		$premium_treaty_2nd_surplus = NULL;
		$premium_treaty_3rd_surplus = NULL;
		$premium_treaty_fac 		= NULL;


		/**
		 * Fresh/Renewal
		 * 	- SI - transaction's SI
		 * 	- Premium - installment premium
		 *
		 * @TODO: Endorsement
		 * 	- Track Changed SI
		 * 	- Track Changed Premium
		 */


		// Compulsory Cession
		if( $treaty_record->flag_comp_cession_apply == IQB_FLAG_ON )
		{
			// SI = Percent or default max
			$percent_amount 	= ( $si_gross * $treaty_record->comp_cession_percent ) / 100.00;
			$default_max_amount = $treaty_record->comp_cession_max_amt;
			$si_comp_cession 	= $percent_amount < $default_max_amount ? $percent_amount : $default_max_amount;

			// Premium
			$premium_comp_cession = ( $si_comp_cession / $si_gross ) * $premium_net;

		}

		// SI Treaty Total (95%), Premium Treaty Total
		$si_treaty_total 		= $si_gross - floatval($si_comp_cession);
		$premium_treaty_total 	= $premium_net - floatval($premium_comp_cession);


		// Get Retention Amount ( This is 1 line)
		$qs_retention_amount = $treaty_record->flag_qs_def_ret_apply == IQB_FLAG_ON
									? $treaty_record->qs_def_ret_amt
									: $treaty_record->qs_max_ret_amt;

		if( $si_treaty_total > $qs_retention_amount )
		{
			$si_treaty_retaintion = $qs_retention_amount;
		}
		else
		{
			$si_treaty_retaintion = $si_treaty_total;
		}

		/**
		 * If Quota Share & Surplus, we have to divide it into
		 * 	- quota
		 * 	- retention
		 */
		$si_qs = $si_treaty_retaintion;
		if((int)$treaty_record->treaty_type_id === IQB_RI_TREATY_TYPE_QS )
		{
			// Sum Insured
			$si_treaty_retaintion 	= ( $qs * $treaty_record->qs_retention_percent ) / 100.00;
			$si_treaty_quota		= ( $qs * $treaty_record->qs_quota_percent ) / 100.00;

			// Premium
			$premium_treaty_retaintion 	= ( $si_treaty_retaintion / $si_treaty_total ) * $premium_treaty_total;
			$premium_treaty_quota 		= ( $si_treaty_quota / $si_treaty_total ) * $premium_treaty_total;
		}
		else
		{
			// Sum Inusred
			$si_treaty_retaintion = $si_qs;

			// Premium
			$premium_treaty_retaintion 	= ( $si_treaty_retaintion / $si_treaty_total ) * $premium_treaty_total;
		}

		// Compute 1st surplus
		$remained_si = $si_treaty_total - $si_qs;
		if( $remained_si > 0 )
		{
			$max_1st_surplus_amount = $treaty_record->qs_lines_1 * $qs_retention_amount;
			if( $remained_si > $max_1st_surplus_amount )
			{
				$si_treaty_1st_surplus 	= $max_1st_surplus_amount;
			}
			else
			{
				$si_treaty_1st_surplus 	= $remained_si;
			}

			// Premium
			$premium_treaty_1st_surplus 	= ( $si_treaty_1st_surplus / $si_treaty_total ) * $premium_treaty_total;

			// Update Remained SI
			$remained_si = $remained_si - $si_treaty_1st_surplus;
		}

		// Compute 2nd surplus
		if( $remained_si > 0 )
		{
			$max_2nd_surplus_amount = $treaty_record->qs_lines_2 * $qs_retention_amount;
			if( $remained_si > $max_2nd_surplus_amount )
			{
				$si_treaty_2nd_surplus = $max_2nd_surplus_amount;
			}
			else
			{
				$si_treaty_2nd_surplus 	= $remained_si;
			}

			// Premium
			$premium_treaty_2nd_surplus 	= ( $si_treaty_2nd_surplus / $si_treaty_total ) * $premium_treaty_total;

			// Update Remained SI
			$remained_si = $remained_si - $si_treaty_2nd_surplus;
		}

		// Compute 3rd surplus
		if( $remained_si > 0 )
		{
			$max_3rd_surplus_amount = $treaty_record->qs_lines_3 * $qs_retention_amount;
			if( $remained_si > $max_3rd_surplus_amount )
			{
				$si_treaty_3rd_surplus = $max_3rd_surplus_amount;
			}
			else
			{
				$si_treaty_3rd_surplus 	= $remained_si;
			}

			// Premium
			$premium_treaty_3rd_surplus 	= ( $si_treaty_3rd_surplus / $si_treaty_total ) * $premium_treaty_total;

			// Update Remained SI
			$remained_si = $remained_si - $si_treaty_3rd_surplus;
		}

		// Compute FAC
		if( $remained_si > 0 )
		{
			$si_treaty_fac = $remained_si;

			// Premium
			$premium_treaty_fac 	= ( $si_treaty_fac / $si_treaty_total ) * $premium_treaty_total;
		}


		$ri_data = [

			/**
			 * Foreign Relation Data (meta data)
			 */
			'policy_id' 			=> $policy_installment_record->policy_id,
			'policy_transaction_id' => $policy_installment_record->policy_transaction_id,
			'policy_installment_id' => $policy_installment_record->id,
			'treaty_id' 			=> $treaty_record->id,
			'fiscal_yr_id' 			=> $CI->current_fiscal_year->id,
			'fy_quarter' 			=> $CI->current_fy_quarter->quarter,

			/**
			 * Distribution Data
			 */
			'si_gross' 				=> $si_gross,
			'si_comp_cession' 		=> $si_comp_cession,
			'si_treaty_total' 		=> $si_treaty_total,
			'si_treaty_retaintion' 	=> $si_treaty_retaintion,
			'si_treaty_quota' 		=> $si_treaty_quota,
			'si_treaty_1st_surplus' => $si_treaty_1st_surplus,
			'si_treaty_2nd_surplus' => $si_treaty_2nd_surplus,
			'si_treaty_3rd_surplus' => $si_treaty_3rd_surplus,
			'si_treaty_fac' 		=> $si_treaty_fac,

			/**
			 * Distribution Data - Premium
			 */
			'premium_gross' 		=> $premium_gross,
			'premium_pool' 			=> $premium_pool,
			'premium_net' 			=> $premium_net,
			'premium_comp_cession' 	=> $premium_comp_cession,
			'premium_treaty_total'  => $premium_treaty_total,
			'premium_treaty_retaintion' 	=> $premium_treaty_retaintion,
			'premium_treaty_quota' 			=> $premium_treaty_quota,
			'premium_treaty_1st_surplus' 	=> $premium_treaty_1st_surplus,
			'premium_treaty_2nd_surplus' 	=> $premium_treaty_2nd_surplus,
			'premium_treaty_3rd_surplus' 	=> $premium_treaty_3rd_surplus,
			'premium_treaty_fac' 			=> $premium_treaty_fac
		];

		return $ri_data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__distribute__QT'))
{
	/**
	 * RI Distribute for QT treaty types
	 *
	 * Treaty Types:
	 * 		1. Quota Share
	 *
	 * @param object $policy_installment_record
	 * @param object $treaty_record
	 * @return mixed
	 */
	function RI__distribute__QT( $policy_installment_record, $treaty_record )
	{
		$CI =& get_instance();

		/**
		 * SI & Premium Variables
		 */
		$si_gross 				= $policy_installment_record->policy_transaction_amt_sum_insured;
		$si_comp_cession 		= NULL;
		$si_treaty_total 		= NULL;
		$si_treaty_retaintion 	= NULL;
		$si_treaty_quota 		= NULL;


		$premium_gross 				= $policy_installment_record->amt_total_premium;
		$premium_pool 				= floatval($policy_installment_record->amt_pool_premium);
		$premium_net 				= $premium_gross - $premium_pool;
		$premium_comp_cession 		= NULL;
		$premium_treaty_total 		= NULL;
		$premium_treaty_retaintion 	= NULL;
		$premium_treaty_quota 		= NULL;



		/**
		 * Fresh/Renewal
		 * 	- SI - transaction's SI
		 * 	- Premium - installment premium
		 *
		 * @TODO: Endorsement
		 * 	- Track Changed SI
		 * 	- Track Changed Premium
		 */


		// Compulsory Cession
		if( $treaty_record->flag_comp_cession_apply == IQB_FLAG_ON )
		{
			// SI = Percent or default max
			$percent_amount 	= ( $si_gross * $treaty_record->comp_cession_percent ) / 100.00;
			$default_max_amount = $treaty_record->comp_cession_max_amt;
			$si_comp_cession 	= $percent_amount < $default_max_amount ? $percent_amount : $default_max_amount;

			// Premium
			$premium_comp_cession = ( $si_comp_cession / $si_gross ) * $premium_net;

		}

		// SI Treaty Total (95%), Premium Treaty Total
		$si_treaty_total 		= $si_gross - floatval($si_comp_cession);
		$premium_treaty_total 	= $premium_net - floatval($premium_comp_cession);


		/**
		 * Quota Share Distribution
		 */
		$si_treaty_retaintion 	= ( $si_treaty_total * $treaty_record->qs_retention_percent ) / 100.00;
		$si_treaty_quota		= ( $si_treaty_total * $treaty_record->qs_quota_percent ) / 100.00;

		// Premium
		$premium_treaty_retaintion 	= ( $si_treaty_retaintion / $si_treaty_total ) * $premium_treaty_total;
		$premium_treaty_quota 		= ( $si_treaty_quota / $si_treaty_total ) * $premium_treaty_total;



		$ri_data = [

			/**
			 * Foreign Relation Data (meta data)
			 */
			'policy_id' 			=> $policy_installment_record->policy_id,
			'policy_transaction_id' => $policy_installment_record->policy_transaction_id,
			'policy_installment_id' => $policy_installment_record->id,
			'treaty_id' 			=> $treaty_record->id,
			'fiscal_yr_id' 			=> $CI->current_fiscal_year->id,
			'fy_quarter' 			=> $CI->current_fy_quarter->quarter,

			/**
			 * Distribution Data - Sum Insured
			 */
			'si_gross' 				=> $si_gross,
			'si_comp_cession' 		=> $si_comp_cession,
			'si_treaty_total' 		=> $si_treaty_total,
			'si_treaty_retaintion' 	=> $si_treaty_retaintion,
			'si_treaty_quota' 		=> $si_treaty_quota,

			/**
			 * Distribution Data - Premium
			 */
			'premium_gross' 		=> $premium_gross,
			'premium_pool' 			=> $premium_pool,
			'premium_net' 			=> $premium_net,
			'premium_comp_cession' 	=> $premium_comp_cession,
			'premium_treaty_total'  => $premium_treaty_total,
			'premium_treaty_retaintion' 	=> $premium_treaty_retaintion,
			'premium_treaty_quota' 			=> $premium_treaty_quota,
		];

		return $ri_data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__distribute__EOL'))
{
	/**
	 * RI Distribute for EOL treaty types
	 *
	 * Treaty Types:
	 * 		1. Excess of Loss
	 *
	 * @param object $policy_installment_record
	 * @param object $treaty_record
	 * @return mixed
	 */
	function RI__distribute__EOL( $policy_installment_record, $treaty_record )
	{
		$CI =& get_instance();

		/**
		 * !!!NOTE: We are only saving compulsory cession si and premium,
		 * 			the rest of the amount goes as retention amount
		 */

		/**
		 * SI & Premium Variables
		 */
		$si_gross 				= $policy_installment_record->policy_transaction_amt_sum_insured;
		$si_comp_cession 		= NULL;
		$si_treaty_retaintion 	= NULL;


		$premium_gross 				= $policy_installment_record->amt_total_premium;
		$premium_pool 				= floatval($policy_installment_record->amt_pool_premium);
		$premium_net 				= $premium_gross - $premium_pool;
		$premium_comp_cession 		= NULL;
		$premium_treaty_retaintion 	= NULL;

		/**
		 *
		 * @TODO: Endorsement
		 * 	- Track Changed SI
		 * 	- Track Changed Premium
		 */


		// Compulsory Cession
		if( $treaty_record->flag_comp_cession_apply == IQB_FLAG_ON )
		{
			// SI = Percent or default max
			$percent_amount 	= ( $si_gross * $treaty_record->comp_cession_percent ) / 100.00;
			$default_max_amount = $treaty_record->comp_cession_max_amt;
			$si_comp_cession 	= $percent_amount < $default_max_amount ? $percent_amount : $default_max_amount;

			// Premium
			$premium_comp_cession = ( $si_comp_cession / $si_gross ) * $premium_net;

		}

		// SI Treaty Total (95%), Premium Treaty Total (Both are retaintaion in this case)
		$si_treaty_retaintion 		= $si_gross - floatval($si_comp_cession);
		$premium_treaty_retaintion 	= $premium_net - floatval($premium_comp_cession);


		$ri_data = [

			/**
			 * Foreign Relation Data (meta data)
			 */
			'policy_id' 			=> $policy_installment_record->policy_id,
			'policy_transaction_id' => $policy_installment_record->policy_transaction_id,
			'policy_installment_id' => $policy_installment_record->id,
			'treaty_id' 			=> $treaty_record->id,
			'fiscal_yr_id' 			=> $CI->current_fiscal_year->id,
			'fy_quarter' 			=> $CI->current_fy_quarter->quarter,

			/**
			 * Distribution Data - Sum Insured
			 */
			'si_gross' 				=> $si_gross,
			'si_comp_cession' 		=> $si_comp_cession,
			'si_treaty_retaintion' 	=> $si_treaty_retaintion,

			/**
			 * Distribution Data - Premium
			 */
			'premium_gross' 				=> $premium_gross,
			'premium_pool' 					=> $premium_pool,
			'premium_net' 					=> $premium_net,
			'premium_comp_cession' 			=> $premium_comp_cession,
			'premium_treaty_retaintion' 	=> $premium_treaty_retaintion,
		];

		return $ri_data;
	}
}

// ------------------------------------------------------------------------



