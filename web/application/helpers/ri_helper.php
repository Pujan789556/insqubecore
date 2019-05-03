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

if ( ! function_exists('RI__pool_treaty_types_dropdown'))
{
	/**
	 * Get RI Pool Treaty Types Dropdown
	 *
	 * @return	string
	 */
	function RI__pool_treaty_types_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_TREATY_TYPES_POOL;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__transaction_ri_txn_for_dropdown'))
{
	/**
	 * Get RI Transaction Premium Type Dropdown
	 *
	 * @return	string
	 */
	function RI__transaction_ri_txn_for_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_RI_TXN_FOR_TYPES;

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

		$CI->load->model('ri_setup_treaty_portfolio_model');
		$treaty_record = $CI->ri_setup_treaty_portfolio_model->get_portfolio_treaty($portfolio_id, $CI->current_fiscal_year->id, IQB_RI_TREATY_CATEGORY_NORMAL);

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
	 * RI Distribution on a Fresh/Renwal Policy's 1st Installment
	 *
	 * @param int $policy_installment_id
	 * @return mixed
	 */
	function RI__distribute( $policy_installment_id )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('policy_installment_model');
		$CI->load->model('ri_transaction_model');

		/**
		 * Get Installment Record
		 */
		$policy_installment_record = $CI->policy_installment_model->get( $policy_installment_id );
		if(!$policy_installment_record)
		{
			throw new Exception("Exception [Helper: ri_helper][Method: RI__distribute()]: No installment record found.");
		}


		/**
		 * Based on Type - Distribute Fresh or Endorsement
		 */
		if(
			_ENDORSEMENT_is_first($policy_installment_record->txn_type)
				&&
            $policy_installment_record->flag_first == IQB_FLAG_ON )
		{
			return RI__save_distribution_data( $policy_installment_record );
		}

		/**
		 * Is Endorsement RI-Distributable (Non-Fresh and RI-Distributable)
		 *
		 * NOTE: This check is required to exclude non-first installment of Fresh Endorsement
		 */
		else if( !_ENDORSEMENT_is_first($policy_installment_record->txn_type) && _ENDORSEMENT_is_ri_distributable($policy_installment_record->txn_type) )
		{
			return RI__endorsement_save_distribution_data( $policy_installment_record );
		}

		return FALSE;

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__save_distribution_data'))
{
	/**
	 * Save RI Distribution Data on Installment
	 *
	 * @param obj $policy_installment_record
	 * @return mixed
	 */
	function RI__save_distribution_data( $policy_installment_record )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('ri_transaction_model');


		/**
		 * Build Treaty Distribution based on Treaty Type
		 */
		$ri_data_basic 	= [];
	 	$ri_data_pool 	= [];

	 	// Basic Premium Distribution
 		if( floatval($policy_installment_record->net_amt_basic_premium) != 0.00 )
	 	{
	 		$ri_data_basic = RI__build_distribution_data( $policy_installment_record, IQB_RI_TREATY_CATEGORY_NORMAL );
	 	}

	 	// Pool Premium Distribution
	 	if( floatval($policy_installment_record->net_amt_pool_premium) != 0.00 )
	 	{
	 		$ri_data_pool = RI__build_distribution_data( $policy_installment_record, IQB_RI_TREATY_CATEGORY_POOL );
	 	}


	 	/**
	 	 * Save in Database
	 	 */
		return RI__save( $policy_installment_record, $ri_data_basic, $ri_data_pool );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__build_distribution_data'))
{
	/**
	 * RI Distribution Data
	 *
	 * @param obj $policy_installment_record
	 * @param int $category [Normal Risks | Pool Risks]
	 * @return array
	 */
	function RI__build_distribution_data( $policy_installment_record, $category )
	{
		/**
		 * Portfolio Treaty for "Normal Risks" or "Pool Risk" which defined by $category variable
		 */
		$treaty_record = RI__get_portfolio_treaty( $policy_installment_record->portfolio_id, $policy_installment_record->fiscal_yr_id, $category );

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

		return [
			'data' 		=> $ri_data,
			'treaty_id' => $treaty_record->id
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__endorsement_build_distribution_data'))
{
	/**
	 * RI Distribution Data
	 *
	 * @param obj $installment_record
	 * @param int $ri_txn_for [Normal Risks | Pool Risks]
	 * @return array
	 */
	function RI__endorsement_build_distribution_data( $installment_record, $ri_txn_for )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('ri_transaction_model');

		// ---------------------------------------------------------------------

		// Clone Policy Installment Record as it is modified inside the function.
		$policy_installment_record = clone $installment_record;

		/**
		 * Validate the Changes made in endorsement.
		 *
		 * Either SI or Premium Must be changed in order to perform RI-Distribution
		 */
		if(
			(floatval($policy_installment_record->endorsement_amt_sum_insured) == 0.00)
			&&
			(
				($ri_txn_for == IQB_RI_TXN_FOR_BASIC && floatval($policy_installment_record->net_amt_basic_premium) == 0.00)
					||
				($ri_txn_for == IQB_RI_TXN_FOR_POOL && floatval($policy_installment_record->net_amt_pool_premium) == 0.00)
			)
		 )
		{

			return FALSE;
		}


		// ---------------------------------------------------------------------

		/**
		 * Task 1 : Reflected RI Transaction (Current RI Transaction State)
		 *
		 * 	Compute the sum of all ri transactions of this policy
		 */
		$ri_transaction_latest 	= $CI->ri_transaction_model->latest_build_by_policy($policy_installment_record->policy_id, $ri_txn_for);


		// Last reference values
		$si_gross 		= $ri_transaction_latest->si_gross ?? 0;
		$premium_net 	= $ri_transaction_latest->premium_net ?? 0;


		// ---------------------------------------------------------------------

		/**
		 * Task 2: Update Sum Insured ( If SI Changed )
		 *
		 * !!!IMPORTANT: It applies only on installments of non-fresh/non-renewal transactions
		 */
		$policy_installment_record->endorsement_amt_sum_insured = $si_gross + floatval($policy_installment_record->endorsement_amt_sum_insured);


		// ---------------------------------------------------------------------


		/**
		 * Task 3: Update Premium ( If changed )
		 *
		 * Charge the premium from policy issue date to today.
		 *
		 * NOTE: On Treaty account basic = Clean cut basis
		 *
		 * !!!IMPORTANT: It applies only on installments of non-fresh/non-renewal transactions
		 */

		/**
		 * Portfolio Treaty for "Normal Risks" or "Pool Risk" which defined by $category variable
		 */
		$category 		= $ri_txn_for == IQB_RI_TXN_FOR_BASIC ? IQB_RI_TREATY_CATEGORY_NORMAL : IQB_RI_TREATY_CATEGORY_POOL;
		$treaty_record 	= RI__get_portfolio_treaty( $policy_installment_record->portfolio_id, $policy_installment_record->fiscal_yr_id, $category );

		if ( $treaty_record->ac_basic == IQB_RI_SETUP_AC_BASIC_TYPE_AY )
		{
			$today 				= date('Y-m-d');
			$policy_start_date 	= $policy_installment_record->policy_start_date;
			$policy_end_date 	= $policy_installment_record->policy_end_date;

			$duration_consumed 	= _POLICY_duration($policy_start_date, $today, 'd');
			$policy_duration 	= _POLICY_duration($policy_start_date, $policy_end_date, 'd');

			$charged_premium_net 	= ($ri_transaction_latest->premium_net / $policy_duration) * $duration_consumed;
			$premium_net 			-= $charged_premium_net;
		}

		/**
		 * Update Premium
		 */
		if($ri_txn_for == IQB_RI_TXN_FOR_BASIC && floatval($policy_installment_record->net_amt_basic_premium) != 0.00)
		{
			$policy_installment_record->net_amt_basic_premium 	+= $premium_net;
		}
		else if($ri_txn_for == IQB_RI_TXN_FOR_POOL && floatval($policy_installment_record->net_amt_pool_premium) != 0.00)
		{
			$policy_installment_record->net_amt_pool_premium 	+= $premium_net;
		}


		// ---------------------------------------------------------------------


		/**
		 * Task 4: Build RI Distribution Data
		 */
		$ri_data =  RI__build_distribution_data( $policy_installment_record, $category );


		// ---------------------------------------------------------------------


		/**
		 * Task 5: Get the difference between ri_transaction_latest & $ri_data
		 */
		if( $ri_data['data'] )
		{
			$ri_data['data'] = RI__compute_ri_transaction_net_effect($ri_data['data'], (array)$ri_transaction_latest );
		}

		return $ri_data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__endorsement_save_distribution_data'))
{
	/**
	 * Save RI Distribution Data on Installment - Endorsement
	 *
	 * @param obj $policy_installment_record
	 * @return mixed
	 */
	function RI__endorsement_save_distribution_data( $policy_installment_record )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('ri_transaction_model');


		/**
		 * Build Treaty Distribution Data for "Normal Risks" & "Pool Risks"
		 */
	 	$ri_data_basic 	= RI__endorsement_build_distribution_data( $policy_installment_record, IQB_RI_TXN_FOR_BASIC );
	 	$ri_data_pool 	= RI__endorsement_build_distribution_data( $policy_installment_record, IQB_RI_TXN_FOR_POOL );

	 	/**
	 	 * Save in Database
	 	 */
		return RI__save( $policy_installment_record, $ri_data_basic, $ri_data_pool );

	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('RI__save'))
{
	/**
	 * Save RI Distribution Data on Database
	 *
	 * @param object $policy_installment_record Policy Installment Record
	 * @param array $ri_data_basic ['data'=> [...], 'treaty_id' => xxx]
	 * @param array $ri_data_pool ['data'=> [...], 'treaty_id' => xxx]
	 * @return array
	 */
	function RI__save( $policy_installment_record, $ri_data_basic, $ri_data_pool )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('ri_transaction_model');


	 	/**
	 	 * Save in Database
	 	 */
		$ri_transaction_id_basic = NULL;
		$ri_transaction_id_pool = NULL;
		if( isset($ri_data_basic['data']) && !empty($ri_data_basic['data']) )
		{
			/**
			 * Foreign Relation Data (meta data)
			 */
			$relation_data = [
				'policy_id' 			=> $policy_installment_record->policy_id,
				'endorsement_id' 		=> $policy_installment_record->endorsement_id,
				'policy_installment_id' => $policy_installment_record->id,
				'treaty_id' 			=> $ri_data_basic['treaty_id'],
				'fiscal_yr_id' 			=> $policy_installment_record->fiscal_yr_id,
				'fy_quarter' 			=> $policy_installment_record->fy_quarter,
				'ri_txn_for' 			=> IQB_RI_TXN_FOR_BASIC
			];

			$ri_data 					= array_merge($ri_data_basic['data'], $relation_data);
			$ri_transaction_id_basic 	= $CI->ri_transaction_model->add($ri_data);
		}

		if( isset($ri_data_pool['data']) && !empty($ri_data_pool['data']) )
		{
			/**
			 * Foreign Relation Data (meta data)
			 */
			$relation_data = [
				'policy_id' 			=> $policy_installment_record->policy_id,
				'endorsement_id' 		=> $policy_installment_record->endorsement_id,
				'policy_installment_id' => $policy_installment_record->id,
				'treaty_id' 			=> $ri_data_pool['treaty_id'],
				'fiscal_yr_id' 			=> $policy_installment_record->fiscal_yr_id,
				'fy_quarter' 			=> $policy_installment_record->fy_quarter,
				'ri_txn_for' 			=> IQB_RI_TXN_FOR_POOL
			];

			$ri_data 					= array_merge($ri_data_pool['data'], $relation_data);
			$ri_transaction_id_pool 	= $CI->ri_transaction_model->add($ri_data);
		}

		return [
			'ri_transaction_id_basic' 	=> $ri_transaction_id_basic,
			'ri_transaction_id_pool' 	=> $ri_transaction_id_pool,
		];

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__get_portfolio_treaty'))
{
	/**
	 * Get the Treaty Record for Given Portfolio for given fiscal year.
	 *
	 * @return	string
	 */
	/**
	 * Get the Treaty Record for Given Portfolio for given fiscal year.
	 * @param int $portfolio_id
	 * @param int $fiscal_yr_id
	 * @param int $category Treaty Category
	 * @param bool $validate Validate Treaty Record?
	 * @return object
	 */
	function RI__get_portfolio_treaty( $portfolio_id, $fiscal_yr_id, $category, $validate = TRUE )
	{
		$CI =& get_instance();

		/**
		 * Load models
		 */
		$CI->load->model('ri_setup_treaty_portfolio_model');


		/**
		 * Portfolio Treaty for "Normal Risks" or "Pool Risk" which defined by $category variable
		 */
		$treaty_record = $CI->ri_setup_treaty_portfolio_model->get_portfolio_treaty($portfolio_id, $fiscal_yr_id, $category);
		if(!$treaty_record )
		{
			// NO Treaty Setup for this portfolio!
			throw new Exception("Exception [Helper: ri_helper][Method: RI__get_portfolio_treaty()]: No treaty found for supplied portfolio.");
		}

		/**
		 * Validate Treaty record?
		 */
		if($validate)
		{
			if( !RI__valid_treaty_record( $treaty_record ) )
			{
				$treaty_type = RI__treaty_types_dropdown(false)[$treaty_record->treaty_type_id];
				throw new Exception("Exception [Helper: ri_helper][Method: RI__get_portfolio_treaty()][{$treaty_record->treaty_name} - {$treaty_type}]: Treaty for this portfolio is not setup properly. Please contact Administrator for this.");
			}
		}

		return $treaty_record;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('RI__compute_ri_transaction_net_effect'))
{
	/**
	 * Compute the net effect on ri transaction with reference to lastest RI transaction build by policy
	 *
	 * @return	string
	 */
	function RI__compute_ri_transaction_net_effect( $data1, $data2 )
	{
		foreach( $data1 as $key=>$value1 )
		{
			$value2 = $data2[$key] ? floatval($data2[$key]) : NULL;
			if($value2)
			{
				$data1[$key] = floatval($data1[$key]) - $value2;
			}
		}
		return $data1;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__valid_treaty_record'))
{
	/**
	 * Valid Treaty Record?
	 *
	 * Test if a treaty record is filled with required fields as per treaty type
	 *
	 * @param object $treaty_record
	 * @return bool
	 */
	function RI__valid_treaty_record( $treaty_record )
	{
		$valid = TRUE;

		$comp_fields = ['ac_basic', 'flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amt', 'treaty_max_capacity_amt'];
		$treaty_comp_fields = [];

		$treaty_type_id = (int)$treaty_record->treaty_type_id;
		switch($treaty_type_id)
		{
			/**
			 * Surplus, Quota Share & Surplus
			 */
			case IQB_RI_TREATY_TYPE_QS:
				$treaty_comp_fields = ['qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3'];
				break;

			/**
			 * Surplus, Quota Share & Surplus
			 */
			case IQB_RI_TREATY_TYPE_SP:
				$treaty_comp_fields = ['qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3'];
				break;

			/**
			 * Quota Share
			 */
			case IQB_RI_TREATY_TYPE_QT:
				$treaty_comp_fields = ['qs_retention_percent', 'qs_quota_percent'];
				break;

			/**
			 * EOL
			 */
			case IQB_RI_TREATY_TYPE_EOL:
				$treaty_comp_fields = ['eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];
				break;


			default:
				break;
		}
		$comp_fields = array_merge($comp_fields, $treaty_comp_fields);

		// Each compulsory fields must not be null
		foreach($comp_fields as $column )
		{
			if($treaty_record->{$column}  === NULL )
			{
				$valid = FALSE;
				break;
			}
		}

		return $valid;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('RI__get_net_premium_for_distribution'))
{
	/**
	 * Get Net Premium For Distribution
	 *
	 * Based on what we want to distribute - pool or basic premium,
	 * return the premium
	 *
	 * @param int $category Treaty Category
	 * @param obj $policy_installment_record Policy Installment Record
	 * @return float
	 */
	function RI__get_net_premium_for_distribution( $category,  $policy_installment_record)
	{
		if( $category == IQB_RI_TREATY_CATEGORY_NORMAL )
		{
			$net_premium = floatval($policy_installment_record->net_amt_basic_premium);
		}
		else
		{
			$net_premium = floatval($policy_installment_record->net_amt_pool_premium);
		}
		return $net_premium;
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
		$si_gross 				= floatval($policy_installment_record->endorsement_amt_sum_insured);
		$si_comp_cession 		= NULL;
		$si_treaty_total 		= NULL;
		$si_treaty_retaintion 	= NULL;
		$si_treaty_quota 		= NULL;
		$si_treaty_1st_surplus 	= NULL;
		$si_treaty_2nd_surplus 	= NULL;
		$si_treaty_3rd_surplus 	= NULL;
		$si_treaty_fac 			= NULL;


		/**
		 * Based on Treaty Category (Pool or Normal), we have to
		 * manage the gross, pool and net premium
		 */
		$premium_net = RI__get_net_premium_for_distribution( $treaty_record->category,  $policy_installment_record);


		$premium_comp_cession 		= NULL;
		$premium_treaty_total 		= NULL;
		$premium_treaty_retaintion 	= NULL;
		$premium_treaty_quota 		= NULL;
		$premium_treaty_1st_surplus = NULL;
		$premium_treaty_2nd_surplus = NULL;
		$premium_treaty_3rd_surplus = NULL;
		$premium_treaty_fac 		= NULL;


		/**
		 * Compulsory Cession - SI and Premium
		 */
		$comp_cessions 			= RI__compute_compulsory_cession( $treaty_record, $si_gross,  $premium_net);
		$si_comp_cession 		= $comp_cessions['si_comp_cession'];
		$premium_comp_cession 	= $comp_cessions['premium_comp_cession'];


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
		if(abs($si_treaty_total) != 0)
		{
			if((int)$treaty_record->treaty_type_id === IQB_RI_TREATY_TYPE_QS )
			{
				// Sum Insured
				$si_treaty_retaintion 	= ( $si_qs * $treaty_record->qs_retention_percent ) / 100.00;
				$si_treaty_quota		= ( $si_qs * $treaty_record->qs_quota_percent ) / 100.00;

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
		}


		// Compute 1st surplus
		$remained_si = $si_treaty_total - $si_qs;
		if( abs($remained_si) != 0 )
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
		if( abs($remained_si) != 0 )
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
		if( abs($remained_si) != 0 )
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
		if( abs($remained_si) != 0 )
		{
			$si_treaty_fac = $remained_si;

			// Premium
			$premium_treaty_fac 	= ( $si_treaty_fac / $si_treaty_total ) * $premium_treaty_total;
		}


		$ri_data = [

			/**
			 * Distribution Data - Exposure (SI)
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
		$si_gross 				= floatval($policy_installment_record->endorsement_amt_sum_insured);
		$si_comp_cession 		= NULL;
		$si_treaty_total 		= NULL;
		$si_treaty_retaintion 	= NULL;
		$si_treaty_quota 		= NULL;


		/**
		 * Based on Treaty Category (Pool or Normal), we have to
		 * manage the gross, pool and net premium
		 */
		$premium_net = RI__get_net_premium_for_distribution( $treaty_record->category,  $policy_installment_record);


		$premium_comp_cession 		= NULL;
		$premium_treaty_total 		= NULL;
		$premium_treaty_retaintion 	= NULL;
		$premium_treaty_quota 		= NULL;


		/**
		 * Compulsory Cession - SI and Premium
		 */
		$comp_cessions 			= RI__compute_compulsory_cession( $treaty_record, $si_gross,  $premium_net);
		$si_comp_cession 		= $comp_cessions['si_comp_cession'];
		$premium_comp_cession 	= $comp_cessions['premium_comp_cession'];


		// SI Treaty Total (95%), Premium Treaty Total
		$si_treaty_total 		= $si_gross - floatval($si_comp_cession);
		$premium_treaty_total 	= $premium_net - floatval($premium_comp_cession);


		/**
		 * Quota Share Distribution
		 */
		if($si_treaty_total)
		{
			$si_treaty_retaintion 	= ( $si_treaty_total * $treaty_record->qs_retention_percent ) / 100.00;
			$si_treaty_quota		= ( $si_treaty_total * $treaty_record->qs_quota_percent ) / 100.00;

			// Premium
			$premium_treaty_retaintion 	= ( $si_treaty_retaintion / $si_treaty_total ) * $premium_treaty_total;
			$premium_treaty_quota 		= ( $si_treaty_quota / $si_treaty_total ) * $premium_treaty_total;
		}




		$ri_data = [

			/**
			 * Distribution Data - Exposure (SI)
			 */
			'si_gross' 				=> $si_gross,
			'si_comp_cession' 		=> $si_comp_cession,
			'si_treaty_total' 		=> $si_treaty_total,
			'si_treaty_retaintion' 	=> $si_treaty_retaintion,
			'si_treaty_quota' 		=> $si_treaty_quota,

			/**
			 * Distribution Data - Premium
			 */
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
		$si_gross 				= floatval($policy_installment_record->endorsement_amt_sum_insured);
		$si_treaty_retaintion 	= NULL;
		$premium_treaty_retaintion 	= NULL;

		/**
		 * Based on Treaty Category (Pool or Normal), we have to
		 * manage the gross, pool and net premium
		 */
		$premium_net = RI__get_net_premium_for_distribution( $treaty_record->category,  $policy_installment_record);


		/**
		 * Compulsory Cession - SI and Premium
		 */
		$comp_cessions 			= RI__compute_compulsory_cession( $treaty_record, $si_gross,  $premium_net);
		$si_comp_cession 		= $comp_cessions['si_comp_cession'];
		$premium_comp_cession 	= $comp_cessions['premium_comp_cession'];



		// SI Treaty Total (95%), Premium Treaty Total (Both are retaintaion in this case)
		$si_treaty_retaintion 		= $si_gross - floatval($si_comp_cession);
		$premium_treaty_retaintion 	= $premium_net - floatval($premium_comp_cession);


		$ri_data = [

			/**
			 * Distribution Data - Exposure (SI)
			 */
			'si_gross' 				=> $si_gross,
			'si_comp_cession' 		=> $si_comp_cession,
			'si_treaty_retaintion' 	=> $si_treaty_retaintion,

			/**
			 * Distribution Data - Premium
			 */
			'premium_net' 					=> $premium_net,
			'premium_comp_cession' 			=> $premium_comp_cession,
			'premium_treaty_retaintion' 	=> $premium_treaty_retaintion,
		];

		return $ri_data;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('RI__compute_compulsory_cession'))
{
	/**
	 * Compute Compulsory Cession - SI and Premium
	 * @param object $treaty_record
	 * @param float $si 	Total Sum Insured
	 * @param float $premium 	Total Premium (Basic or Pool)
	 * @return type
	 */
	function RI__compute_compulsory_cession( $treaty_record, $si, $premium )
	{
		$si_comp_cession 		= NULL;
		$premium_comp_cession 	= NULL;

		// Compulsory Cession
		if( $treaty_record->flag_comp_cession_apply == IQB_FLAG_ON && abs($si) != 0 )
		{
			// SI = Percent or default max
			$percent_amount 	= ( $si * $treaty_record->comp_cession_percent ) / 100.00;
			$default_max_amount = $treaty_record->comp_cession_max_amt;
			$si_comp_cession 	= abs($percent_amount) < $default_max_amount ? $percent_amount : $default_max_amount;

			// Premium
			$premium_comp_cession = ( $si_comp_cession / $si ) * $premium;

		}

		return [
			'si_comp_cession' 		=> $si_comp_cession,
			'premium_comp_cession' 	=> $premium_comp_cession
		];
	}
}

// ------------------------------------------------------------------------



