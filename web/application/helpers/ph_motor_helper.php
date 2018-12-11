<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Motor Portfolio Helper Functions
 *
 * This file contains helper functions related to Motor Portfolio
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO DROPDOWN HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_PO_MOTOR_voluntary_excess_dropdown'))
{
	/**
	 * Get "Voluntary Excess" Dropdown for Given Portfolio Tariff
	 *
	 * @param JSON $dr_voluntary_excess  Tariff Record's "Voluntary Excess" JSON
	 * @return	array
	 */
	function _PO_MOTOR_voluntary_excess_dropdown( $dr_voluntary_excess, $flag_blank_select = true, $prefix = 'Rs. ' )
	{
		$dropdown = [];
		$dr_voluntary_excess = $dr_voluntary_excess ? json_decode($dr_voluntary_excess) : NULL;
		if($dr_voluntary_excess )
		{
			foreach ($dr_voluntary_excess as $r)
			{
				$dropdown[$r->rate] = $prefix .  $r->amount;
			}
		}

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('_PO_MOTOR_no_claim_discount_dropdown'))
{
	/**
	 * Get "No Claim Discount" Dropdown for Given Portfolio Tariff
	 *
	 * @param JSON $no_claim_discount  Tariff Record's "No Claim Discount" JSON
	 * @return	array
	 */
	function _PO_MOTOR_no_claim_discount_dropdown( $no_claim_discount, $lang = 'en', $flag_blank_select = true )
	{
		$dropdown = [];
		$no_claim_discount = $no_claim_discount ? json_decode($no_claim_discount) : NULL;
		if($no_claim_discount )
		{
			$label = "label_{$lang}";
			foreach ($no_claim_discount as $r)
			{
				$dropdown[$r->years] =  $r->{$label} ?? $r->years . ' years';
			}
		}

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('_PO_MOTOR_no_claim_discount_rate'))
{
	/**
	 * Get "No Claim Discount" rate for Given Portfolio Tariff
	 *
	 * @param int $year  No claim discount year
	 * @param JSON $tariff_no_claim_discount  Tariff Record's "No Claim Discount" JSON
	 * @return	array
	 */
	function _PO_MOTOR_no_claim_discount_rate($year, $tariff_no_claim_discount )
	{
		$rate = 0;
		$tariff_no_claim_discount = $tariff_no_claim_discount ? json_decode($tariff_no_claim_discount) : NULL;
		if($tariff_no_claim_discount )
		{
			foreach ($tariff_no_claim_discount as $r)
			{
				if($r->years == $year)
				{
					$rate = $r->rate;
					break;
				}
			}
		}
		return $rate;
	}
}

// ------------------------------------------------------------------------
// TARIFF BASED COST COMPUTATION FUNCTIONS
// ------------------------------------------------------------------------


if ( ! function_exists('_PO_MOTOR_premium_compute_method'))
{
    /**
     * Get MOTOR Cost Reference Computation Method
     *
     * @param int $portfolio_id
     * @return string
     */
    function _PO_MOTOR_premium_compute_method($portfolio_id)
    {
    	// Method to compute CRF data
		$method = '';

		switch ($portfolio_id)
		{
			case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
				$method = '_PO_MOTOR_MCY_premium';
				break;

			case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
				$method = '_PO_MOTOR_PVC_premium';
				break;

			case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
				$method = '_PO_MOTOR_CVC_premium';
				break;

			default:
				break;
		}

		return $method;
    }
}

// -----------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_MCY_premium'))
{
	/**
	 * Motorcycle Policy Cost Table
	 *
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Object Record
	 * @param object $tariff_record Tariff Record For this Portfolio->Sub-Portfolio
	 * @param object $pfs_record Portfolio Settings Record For this Policy's Fiscal year and Portfolio
	 * @param array $data Portfolio Related POST Data
	 *
	 * @return	array
	 */
	function _PO_MOTOR_MCY_premium( $policy_record, $endorsement_record, $tariff_record, $pfs_record, $post_data  )
	{
		$CI =& get_instance();

		/**
		 * Policy Object Record
		 *
		 * In case of endorsements, we will be needing both current policy object and edited object information
		 * to compute premium.
		 */
		$old_object = get_object_from_policy_record($policy_record);
		$new_object = NULL;
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			try {
				$new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
			} catch (Exception $e) {

				return $CI->template->json([
                    'status'        => 'error',
                    'title' 		=> 'Exception Occured',
                    'message' 	=> $e->getMessage()
                ], 404);
			}
		}

		// Newest object attributes should be used.
		$object_attributes  = json_decode($new_object->attributes ?? $old_object->attributes);


		// Form Posted Data - Extra Fields Required to Perform Cost Calculation
		$premium_computation_table = $post_data['premium'] ?? NULL;

		// Tariff Extracts
		$default_tariff = json_decode($tariff_record->tariff);

		/**
		 * NET Sum Insured & Its Breakdown
		 *
		 *  A. Net SI
		 * 	B. Vehicle Price
		 * 	C. Accessories Price
		 */
		$SI 			= _OBJ_si_net($old_object, $new_object);
		$SI_BREAKDOWN 	= _OBJ_si_breakdown_net($old_object, $new_object);
		$si_vehicle 	= $SI_BREAKDOWN['si_vehicle'];
		$si_accessories = $SI_BREAKDOWN['si_accessories'];
		$vehicle_total_si = $si_vehicle + $si_accessories;


		// Vehicle Age (If registration date is there use it otherwise mfd year)
		$ref_date = $object_attributes->reg_date ? $object_attributes->reg_date : $object_attributes->year_mfd . '-01-01';
		$vehicle_reference_date 	= new DateTime($ref_date);
		$today 						= new DateTime(date('Y-m-d'));
		$interval 					= $vehicle_reference_date->diff($today);
		$vehicle_age_in_yrs 		= $interval->y;

		$__remarks = NULL; // Premium Remarks, if tariff calculation falls below default premium

		//
		// Defaults: Thirdparty Premium, Default Rate, Vehicle Over Age Rate
		//
		$premiumm_third_party 	= 0.00;
		$default_rate 			= 0.00;
		$vehicle_over_age_rate 	= 0.00;
        foreach ($default_tariff as $t)
        {
            if( $object_attributes->engine_capacity >= $t->ec_min && $object_attributes->engine_capacity <= $t->ec_max )
            {
                $premiumm_third_party = $t->third_party;
                $default_rate = $t->default_rate;

                if( $t->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age1_max )
				{
					$vehicle_over_age_rate = $t->rate1;
				}
				else if( $t->age2_min <= $vehicle_age_in_yrs )
				{
					$vehicle_over_age_rate = $t->rate2;
				}
                break;
            }
        }

        // No Claim Discount - Years & Rate
  		$year_no_claim_discount = $premium_computation_table['no_claim_discount'] ?? 0;
		$no_claim_discount_rate = _PO_MOTOR_no_claim_discount_rate($year_no_claim_discount, $tariff_record->no_claim_discount);
		$text_ncd_prefix 		= _PO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, 'np', FALSE)[$year_no_claim_discount] ?? '';

        // Rsik Group
        $tariff_rsik_group = json_decode($tariff_record->riks_group);

        /**
         * Package Comprehensive
         */
        $premium_A_total 	= 0.00;
        $premium_AA_total 	= 0.00;
        $POOL_PREMIUM 	= 0.00;
        $commissionable_premium = NULL;
        $agent_commission 		= NULL;
        $direct_discount 		= NULL;
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Defaults (अ)
			 */
			$__CRF_cc_table__A = [
				'column_head' => 'अ',
				'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत',
				'title_en' 	=> 'Insurance against vehicle damage/loss & accident insurance amounted to'
			];

			// Default Premium =  X% of Vehicle Price
			$__premium_A_row_1 =  $vehicle_total_si * ($default_rate/100.00);
			$__CRF_cc_table__A['sections'][] = [
				'label' 	=> 'क',
				'title' 	=> "सि.सि.तथा घोषित मूल्य (सरसामान सहित) अनुसार बीमा शुल्क (घोषित मूल्यको {$default_rate}%)",
				'amount' 	=> $__premium_A_row_1
			];

			// Additional cost due to vehicle over aging
			$__premium_A_row_2 = 0.00;
			if( $vehicle_over_age_rate > 0.00 )
			{
				$__premium_A_row_2 = $__premium_A_row_1 * ($vehicle_over_age_rate/100.00);
				$__CRF_cc_table__A['sections'][] = [
					'title' 	=> "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को {$vehicle_over_age_rate}%",
					'amount' 	=> $__premium_A_row_2
				];
			}


			// Sub Total : ख
			$__premium_A_row_3 = $__premium_A_row_1 + $__premium_A_row_2;
			$__CRF_cc_table__A['sections'][] = [
				'label' 	=> 'ख',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_3
			];


			// Discount on Voluntary Excess : discount_KHA
			$__premium_A_row_4 				= 0.00;
			$dr_voluntary_excess 		= $premium_computation_table['dr_voluntary_excess'] ?? 0.00;
			$amount_voluntary_excess 	= $dr_voluntary_excess ? _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess] : 0.00;

			if($dr_voluntary_excess)
			{
				$__premium_A_row_4 = $__premium_A_row_3 * ($dr_voluntary_excess/100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
				'amount' 	=> $__premium_A_row_4
			];


			// Sub Total : ग
			$__premium_A_row_5 = $__premium_A_row_3 - $__premium_A_row_4;
			$__CRF_cc_table__A['sections'][] = [
				'label' 	=> 'ग',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_5
			];


			// No Claim Discount : discount_GA
			$__premium_A_row_6 			= 0.00;
			if($no_claim_discount_rate)
			{
				$__premium_A_row_6 = $__premium_A_row_5 * ($no_claim_discount_rate/100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "{$text_ncd_prefix} दावी नगरे वापत छूटः “ग” को $no_claim_discount_rate %",
				'amount' 	=> $__premium_A_row_6
			];



			// Sub Total : घ
			$commissionable_premium = $__premium_A_row_7 = $__premium_A_row_5 - $__premium_A_row_6;
			$__CRF_cc_table__A['sections'][] = [
				'label' 	=> 'घ',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_7
			];

			//
			// Agent Commission/Direct Discount? -> Applies only Non-GOVT
			//
			$__premium_A_row_8 = 0.00;
			if( $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT && $object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
			{
				// X% of GHA
				$__premium_A_row_8 = $__premium_A_row_7 * ($pfs_record->direct_discount/100.00);

				$direct_discount 	= $__premium_A_row_8;
				$dd_formatted 		= number_format($pfs_record->direct_discount, 2);
				$__CRF_cc_table__A['sections'][] = [
					'title' 	=> "प्रत्यक्ष बीमा वापत छूटः “घ” को {$dd_formatted} %",
					'amount' 	=> $direct_discount
				];
			}


			// अ Total
			$premium_A_total = $__premium_A_row_7  - $__premium_A_row_8;


			/**
			 * Default Premium Check -- Fresh/Renewal ONLY
			 * -------------------------------------------
			 *
			 * If $premium_A_total  is below the default premium amount, we should use
			 * the default premium amount from Motor Tariff.
			 */
			if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) && $tariff_record->default_premium > $premium_A_total )
			{

				// Premium Total and Commissionable Amount to Default.
				$premium_A_total 			= $tariff_record->default_premium;
				$commissionable_premium 	= $premium_A_total;

				// Remove all the sections
				$__CRF_cc_table__A = [
					'column_head' => 'अ',
					'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत',
					'title_en' 	=> 'Insurance against vehicle damage/loss & accident insurance amounted to'
				];
				$__CRF_cc_table__A['sections'] = [];
				$__CRF_cc_table__A['sections'][] = [
					'title' 	=> "सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत",
					'amount' 	=> $premium_A_total
				];

				// Let's have a remarks
				$__remarks = 'Tariff Calculation is below Default Premium Amount.';
			}


			$__CRF_cc_table__A['sections'][] = [
				'title' 		=> "जम्मा",
				'amount' 		=> $premium_A_total,
				'section_total' => true
			];

			$__CRF_cc_table__A['sub_total'] = $premium_A_total;

			// ---------------------------------------------------------------------------------------


			/**
			 * Risk Pool (इ)
			 */
			$__CRF_cc_table__I = [
				'column_head' => 'इ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];

			$flag_risk_pool = $premium_computation_table['flag_risk_pool'] ?? NULL;
			$premium_risk_mob = 0.00;
			$premium_risk_terorrism = 0.00;
			$premium_for_insured_covered_on_terorrism = 0.00;
			if($flag_risk_pool)
			{
				$premium_risk_mob 		= $vehicle_total_si * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
				$premium_risk_terorrism = $vehicle_total_si * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);

				// Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

				// Driver Covered
				// Passenger Cover
				// Driver Count  = 1
				// Passenger Count = Seat Capacity - 1
				// Tariff Rate: rate_additionl_per_thousand_on_extra_rate

				$insured_value_tariff = json_decode($tariff_record->insured_value_tariff);

				$no_of_seat = $object_attributes->seating_capacity;
				$passenger_count = $no_of_seat - 1;

				// Driver Premium
				$premium_driver = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

				// Passenger Premium
				$premium_passenger = $passenger_count * (($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);

				$premium_for_insured_covered_on_terorrism = $premium_driver + $premium_passenger;
			}
			$__CRF_cc_table__I['sections'][] = [
				'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
				'amount' => $premium_risk_mob
			];
			$__CRF_cc_table__I['sections'][] = [
				'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
				'amount' => $premium_risk_terorrism
			];

			$__CRF_cc_table__I['sections'][] = [
				'title' => "(ग) चालक तथा पछाडि बस्ने व्यक्तिको आतंकबाद वापत {$tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate} प्रति हजारका दरले ",
				'amount' => $premium_for_insured_covered_on_terorrism
			];

			// इ TOTAL
			$POOL_PREMIUM = $premium_risk_mob + $premium_risk_terorrism + $premium_for_insured_covered_on_terorrism;

			$__CRF_cc_table__I['sections'][] = [
				'title' 	=> 'जम्मा', // Subtotal
				'amount' 	=> $POOL_PREMIUM,
				'section_total' => true
			];

			$__CRF_cc_table__I['sub_total'] = $POOL_PREMIUM;
		}



		/**
		 * Third Party (आ)
		 */
		$amount_noClaimDiscount_on_thirdParty = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount_rate != 0)
		{
			$amount_noClaimDiscount_on_thirdParty = $premiumm_third_party * ($no_claim_discount_rate/100.00);
		}

		// आ TOTAL
		$premium_AA_total = $premiumm_third_party - $amount_noClaimDiscount_on_thirdParty;

		// ---------------------------------------------------------------------------------------


		//
		// Disabled Friendly Discount
		//
		$vehile_flag_mcy_df = $object_attributes->flag_mcy_df ?? FALSE;
		$discount_MCY_DF = 0.00;
		if($vehile_flag_mcy_df)
		{
			// x% of (अ + आ)
			$discount_MCY_DF = ($premium_A_total + $premium_AA_total) * ($tariff_record->dr_mcy_disabled_friendly / 100.00);
		}

		// Total (अ + आ) - Disable Friendly Discount
		$premium_total_A_AA = $premium_A_total + $premium_AA_total - $discount_MCY_DF;


		//
		// Grand Total
		//
		$BASIC_PREMIUM = $premium_total_A_AA;


		//
		// Stamp Duty
		//
		$amt_stamp_duty = $post_data['amt_stamp_duty'];


		/**
		 * Cost Table: Third Party Only
		 */
		$tp_column_head = 'आ';
		$tp_label 		= 'ङ';
		if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
		{
			$tp_column_head = 'अ';
			$tp_label 		= 'क';
		}

		$__CRF_cc_table__AA = [
			'column_head' => $tp_column_head,
			'title_np' 	=> 'तेश्रो पक्ष प्रतिको दायित्व तथा व्यक्तिगत दुर्घटना बीमा वापत',
			'title_en' 	=> 'Third party liability insurance amounted to',
			'sections' 	=> [
				// Cost according to CC
				[
					'title' 	=> "सि. सि. अनुसारको बीमाशुल्क",
					'amount' 	=> $premiumm_third_party,
					'label' 	=> $tp_label
				]
			]
		];

		// Noclaim Dicount Only if Comprehensive
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			$__CRF_cc_table__AA['sections'][] = [
				'title' => "{$text_ncd_prefix} दावी नगरे वापत छूटः \"{$tp_label}\" को $no_claim_discount_rate %",
				'amount' => $amount_noClaimDiscount_on_thirdParty
			];
		}

		// Third Party : Sub Total
		$__CRF_cc_table__AA['sections'][] = [
			'title' 	=> 'जम्मा', // Subtotal
			'amount' 	=> $premium_AA_total,
			'section_total' => true
		];

		$__CRF_cc_table__AA['sub_total'] = $premium_AA_total;


		/**
		 * Disabled Friendly Discount
		 */
		$__CRF_cc_table__flag_mcy_df = [];
		if($vehile_flag_mcy_df)
		{
			$__CRF_cc_table__flag_mcy_df = [
				'column_head' => '*',
				'title_np' 	=> 'अपाङ्ग मैत्री छुट',
				'title_en' 	=> 'Disabled-friendly discount',
				'sub_total' => $discount_MCY_DF,
				'sections'  => [
					// Cost according to CC
					[
						'title' => "(अ) र (आ) को जम्मा रकममा {$tariff_record->dr_mcy_disabled_friendly} % छुट",
						'amount' => $discount_MCY_DF
					],

					// Sub Total
					[
						'title' 	=> 'जम्मा', // Subtotal
						'amount' 	=> $premium_total_A_AA,
						'section_total' => true
					]
				]
			];
		}

		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
		{
			$cost_calculation_table = array_filter([$__CRF_cc_table__AA, $__CRF_cc_table__flag_mcy_df]);
		}
		else
		{
			$__CRF_cc_table__comprehensive = array_filter([
				// Comprehensive
				$__CRF_cc_table__A,

				// Third Party
				$__CRF_cc_table__AA,

				// Discount Disabled Friendly
				$__CRF_cc_table__flag_mcy_df,

				// Pool Risk
				$__CRF_cc_table__I
			]);

			$cost_calculation_table = $__CRF_cc_table__comprehensive;

			/**
			 * Agent Commission?
			 * ------------------
			 *
			 * Applies only if the vehicle is non-govt and no direct discount
			 */
			if(
				$policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION
				&&
				$object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT
			){
				$agent_commission 	= ($commissionable_premium * $pfs_record->agent_commission)/100.00;
			}
		}


		/**
		 * Prepare Premium Data
		 */
		$premium_data = [
			'amt_basic_premium' 	=> $BASIC_PREMIUM,
			'amt_commissionable'	=> $commissionable_premium,
			'amt_agent_commission'  => $agent_commission,
			'amt_direct_discount' 	=> $direct_discount,
			'amt_pool_premium' 		=> $POOL_PREMIUM,
		];

		/**
		 * Perform Computation Basis for Endorsement
		 */
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			$premium_data = _ENDORSEMENT_apply_computation_basis($policy_record, $endorsement_record, $pfs_record, $premium_data );
		}
		/**
		 * Short Term Policy???
		 *
		 * Only Fres/Renewal Policy have Short Term Facility
		 */
		else if($policy_record->flag_short_term == IQB_FLAG_YES)
		{
			$spr_goodies 	= _POLICY__get_spr_goodies( $pfs_record, $policy_record->start_date, $policy_record->end_date );
			$premium_data 	= _POLICY__compute_short_term_premium( $spr_goodies['record']->rate ?? NULL, $premium_data, IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BOTH);
		}
		else
		{
			/**
			 * NULLIFY Sort Term Related Fields on Endorsement Table
			 */
			$premium_data = _POLICY__nullify_short_term_premium( $premium_data );
		}


		/**
		 * Compute VAT
		 *
		 * NOTE:
		 * On premium refund, we should also be refunding VAT
		 */
		if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
		{
			// We do not do anything here, because, VAT was applied only on Stamp Duty
			// For other portfolio, it must be set as -ve value

			/**
			 * !!! NO POOL PREMIUM !!!
			 *
			 * Pool premium is not refunded to customer.
			 * NULLify Pool Premium so that it reflects on VAT Computation
			 */
			$premium_data['amt_pool_premium'] = 0.00;
		}
		$CI->load->helper('account');
		$taxable_amount = $post_data['amt_stamp_duty'] + $premium_data['amt_basic_premium'] + $premium_data['amt_pool_premium'];
		$amount_vat 	= ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


		/**
		 * Prepare Other Data
		 */
		$gross_amt_sum_insured 	= $new_object->amt_sum_insured ?? $old_object->amt_sum_insured;
		$net_amt_sum_insured 	= $SI;
		$txn_data = array_merge($premium_data, [
			'gross_amt_sum_insured' => $gross_amt_sum_insured,
			'net_amt_sum_insured' 	=> $net_amt_sum_insured,
			'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
			'amt_vat' 				=> $amount_vat,
		]);

		/**
		 * Premium Computation Table
		 * -------------------------
		 */
		$txn_data['premium_computation_table'] = $premium_computation_table ? json_encode($premium_computation_table) : NULL;


		/**
		 * Cost Calculation Table
		 */
		$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
		return $CI->endorsement_model->save($endorsement_record->id, $txn_data);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_PVC_premium'))
{
	/**
	 * Private Vehicle Policy Cost Table
	 *
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Object Record
	 * @param object $tariff_record Tariff Record For this Portfolio->Sub-Portfolio
	 * @param object $pfs_record Portfolio Settings Record For this Policy's Fiscal year and Portfolio
	 * @param array $data Portfolio Related POST Data
	 *
	 * @return	array
	 */
	function _PO_MOTOR_PVC_premium( $policy_record, $endorsement_record, $tariff_record, $pfs_record, $post_data  )
	{
		$CI =& get_instance();

		/**
		 * Policy Object Record
		 *
		 * In case of endorsements, we will be needing both current policy object and edited object information
		 * to compute premium.
		 */
		$old_object = get_object_from_policy_record($policy_record);
		$new_object = NULL;
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			try {
				$new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
			} catch (Exception $e) {

				return $CI->template->json([
                    'status'        => 'error',
                    'title' 		=> 'Exception Occured',
                    'message' 	=> $e->getMessage()
                ], 404);
			}
		}

		// Newest object attributes should be used.
		$object_attributes  = json_decode($new_object->attributes ?? $old_object->attributes);

		// Form Posted Data - Extra Fields Required to Perform Cost Calculation
		$premium_computation_table = $post_data['premium'] ?? NULL;


		// Tariff Extracts
		$default_tariff = json_decode($tariff_record->tariff);

		// Trolly Tariff
		$trolly_tariff = $tariff_record->trolly_tariff ? json_decode($tariff_record->trolly_tariff) : NULL;

		/**
		 * NET Sum Insured & Its Breakdown
		 *
		 *  A. Net SI
		 * 	B. Vehicle Price
		 * 	C. Accessories Price
		 * 	D. Trailer Price
		 */
		$SI 			= _OBJ_si_net($old_object, $new_object);
		$SI_BREAKDOWN 	= _OBJ_si_breakdown_net($old_object, $new_object);
		$si_vehicle 	= $SI_BREAKDOWN['si_vehicle'];
		$si_accessories = $SI_BREAKDOWN['si_accessories'];
		$si_trailer 	= $SI_BREAKDOWN['si_trailer'];
		$vehicle_total_si = $si_vehicle + $si_accessories;


		// Vehicle Age (If registration date is there use it otherwise mfd year)
		$ref_date = $object_attributes->reg_date ? $object_attributes->reg_date : $object_attributes->year_mfd . '-01-01';
		$vehicle_reference_date 	= new DateTime($ref_date);
		$today 						= new DateTime(date('Y-m-d'));
		$interval 					= $vehicle_reference_date->diff($today);
		$vehicle_age_in_yrs 		= $interval->y;

		$__remarks = NULL; // Premium Remarks, if tariff calculation falls below default premium

		//
		// Tariff Rate Defaults for Given Engine Capacity
		//  	To Minus Amount, Base Fragment, Base Fragment Rate, Rest Fragment Rate, Third Party Rate, Vehicle Over Age Rate
		// 		Example: First 20 lakhs 1.24% + Rest Amount's 1.6% - Rs. 3000
		//
		$minus_amount_according_to_cc 	= 0.00;
		$default_si_amount 				= 0.00;
		$default_si_rate 			= 0.00;
		$remaining_si_rate 			= 0.00;
		$premiumm_third_party 			= 0.00;
		$vehicle_over_age_rate 			= 0.00;

        $commissionable_premium = NULL;
        $agent_commission 	= NULL;
        $direct_discount 	= NULL;

        foreach ($default_tariff as $t)
        {
        	// Should also match Engine Capacity Type
            if( $object_attributes->engine_capacity >= $t->ec_min && $object_attributes->engine_capacity <= $t->ec_max && $object_attributes->ec_unit === $t->ec_type )
            {
            	$minus_amount_according_to_cc 			= (float)$t->minus_amount;
                $default_si_amount 						= (float)$t->default_si_amount;
                $default_si_rate 					= (float)$t->default_si_rate;
                $remaining_si_rate 					= (float)$t->remaining_si_rate;
                $premiumm_third_party 					= (float)$t->third_party;

                if( $t->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age1_max )
				{
					$vehicle_over_age_rate = (float)$t->rate1;
				}
				else if( $t->age2_min <= $vehicle_age_in_yrs )
				{
					$vehicle_over_age_rate = (float)$t->rate2;
				}
                break;
            }
        }

        // No Claim Discount - Years & Rate
        $year_no_claim_discount = $premium_computation_table['no_claim_discount'] ?? 0;
		$no_claim_discount_rate = _PO_MOTOR_no_claim_discount_rate($year_no_claim_discount, $tariff_record->no_claim_discount);
		$text_ncd_prefix 		= _PO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, 'np', FALSE)[$year_no_claim_discount] ?? '';

        // Rsik Group
        $tariff_rsik_group = json_decode($tariff_record->riks_group);


        /**
         * Package Comprehensive
         */
        $premium_A_total = 0.00;
        $premium_AA_total = 0.00;
        $premium_I_total = 0.00;
        $premium_EE_total = 0.00;
        $POOL_PREMIUM = 0.00;
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Defaults (अ)
			 */
			$__CRF_cc_table__A = [
				'column_head' => 'अ',
				'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
				'title_en' 	=> 'Insurance against vehicle damage/loss',
			];


			// Base Premium
			$__premium_A_row_1 = 0.00;
			$__premium_A_row_2 = 0.00;
			if($vehicle_total_si > $default_si_amount ) // if Vehicle Price > 20 Lakhs
			{
				$__premium_A_row_1 = $default_si_amount * ($default_si_rate / 100.00);
				$__premium_A_row_2 = ( $vehicle_total_si -  $default_si_amount ) * ($remaining_si_rate/100.00);
			}
			else
			{
				$__premium_A_row_1 = $vehicle_total_si * ($default_si_rate / 100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "सि.सि.अनुसार घोषित मूल्य मध्ये पहिलो बीस लाखसम्मको बीमाशुल्क (घोषित मूल्यको {$default_si_rate}%)",
				'amount' 	=> $__premium_A_row_1
			];
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "बाँकी घोषित मूल्यको बीमा शुल्क (बाँकी घोषित मूल्यको {$remaining_si_rate}%)",
				'amount' 	=> $__premium_A_row_2
			];


			// Trailer/Trolly Premium
			$__premium_A_row_3 = 0.00;
			if( $si_trailer )
			{
				$__premium_A_row_3 = ( $si_trailer * ($trolly_tariff->rate/100.00) ) - $trolly_tariff->minus_amount;
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "ट्रेलर (मालसामान ओसार्ने) वापतको बीमाशुल्क ( घोषित मूल्यको{$trolly_tariff->rate}% - रु. {$trolly_tariff->minus_amount})का दरले",
				'amount' 	=> $__premium_A_row_3
			];


			// Discount according to CC
			$__premium_A_row_4 = $minus_amount_according_to_cc;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "सि.सि. अनुसार बीमाशुल्कमा छूट",
				'amount' 	=> $__premium_A_row_4
			];


			// क Sub Total
			$__premium_A_row_KA = $__premium_A_row_5 = $__premium_A_row_1 + $__premium_A_row_2 + $__premium_A_row_3 - $__premium_A_row_4;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_5,
				'label' 	=> 'क'
			];


			// Old Age Premium
			$__premium_A_row_6 = 0.00;
			if( $vehicle_over_age_rate > 0.00 )
			{
				$__premium_A_row_6 = $__premium_A_row_KA * ($vehicle_over_age_rate/100.00);
				$__CRF_cc_table__A['sections'][] = [
					'title' => "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को {$vehicle_over_age_rate}%",
					'amount' => $__premium_A_row_6
				];
			}



			// ख Sub Total
			$__premium_A_row_KHA = $__premium_A_row_7 = $__premium_A_row_KA + $__premium_A_row_6;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_KHA,
				'label' 	=> 'ख'
			];


			// Private on Hire (Commercial Use)
			$flag_commercial_use = $premium_computation_table['flag_commercial_use'] ?? FALSE;
			$__premium_A_row_8 = 0.00;
			if( $flag_commercial_use )
			{
				$__premium_A_row_8 = $__premium_A_row_KHA * ($tariff_record->rate_pvc_on_hire/100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' => "निजी प्रयोजनको लागि भाडामा दिए वापत थप: “ख” को {$tariff_record->rate_pvc_on_hire}%",
				'amount' => $__premium_A_row_8
			];


			// ग Sub Total
			$__premium_A_row_GA = $__premium_A_row_9 = $__premium_A_row_KHA + $__premium_A_row_8;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_GA,
				'label' 	=> 'ग'
			];


			// Discount on Voluntary Excess - GA ko X%
			$dr_voluntary_excess 		= $premium_computation_table['dr_voluntary_excess'] ?? FALSE;
			$amount_voluntary_excess 	= $dr_voluntary_excess
											? _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess]
											: 0.00;
			$__premium_A_row_10 		= 0.00;
			if( $dr_voluntary_excess )
			{
				$__premium_A_row_10 = $__premium_A_row_GA * ($dr_voluntary_excess/100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ग” को {$dr_voluntary_excess} %",
				'amount' => $__premium_A_row_10
			];


			// घ Sub Total
			$__premium_A_row_GHA = $__premium_A_row_11 = $__premium_A_row_GA - $__premium_A_row_10;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_GHA,
				'label' 	=> 'घ'
			];


			// NO Claim Discount
			$__premium_A_row_12 = 0.00;
			if($no_claim_discount_rate)
			{
				$__premium_A_row_12 = $__premium_A_row_GHA * ($no_claim_discount_rate/100.00);
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' => "{$text_ncd_prefix} दावी नगरे वापत छूटः “घ” को {$no_claim_discount_rate} %",
				'amount' => $__premium_A_row_12
			];


			// ङ Sub Total
			$commissionable_premium = $__premium_A_row_NGA = $__premium_A_row_13 = $__premium_A_row_GHA - $__premium_A_row_12;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_NGA,
				'label' 	=> 'ङ'
			];


			//
			// Agent Commission/Direct Discount? -> Applies only Non-GOVT
			//
			$__premium_A_row_14 = 0.00;
			if( $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT && $object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
			{
				// X% of ङ
				$__premium_A_row_14 = $__premium_A_row_NGA * ($pfs_record->direct_discount/100.00);

				$direct_discount 	= $__premium_A_row_14;
				$dd_formatted 		= number_format($pfs_record->direct_discount, 2);
				$__CRF_cc_table__A['sections'][] = [
					'title' => "प्रत्यक्ष बीमा वापत छुटः “ङ” को {$dd_formatted} %",
					'amount' => $__premium_A_row_14
				];
			}


			// च Sub Total
			$__premium_A_row_CHA = $__premium_A_row_15 = $__premium_A_row_NGA - $__premium_A_row_14;
			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_CHA,
				'label' 	=> 'च'
			];


			// Towing
			$flag_towing = $premium_computation_table['flag_towing'] ?? FALSE;
			$__premium_A_row_16 = 0.00;
			if( $flag_towing )
			{
				$__premium_A_row_16 = $tariff_record->pramt_towing ?? 0.00;
			}
			$__CRF_cc_table__A['sections'][] = [
				'title' => "दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा वापत बीमा शुल्क थप",
				'amount' => $__premium_A_row_16
			];


			// Section Sub Total (अ Total)
			$premium_A_total  = $__premium_A_row_17 = $__premium_A_row_CHA + $__premium_A_row_16;

			/**
			 * Default Premium Check -- Fresh/Renewal ONLY
			 * -------------------------------------------
			 *
			 * If $premium_A_total  is below the default premium amount, we should use
			 * the default premium amount from Motor Tariff.
			 */
			if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) && $tariff_record->default_premium > $premium_A_total )
			{

				// Premium Total and Commissionable Amount to Default.
				$premium_A_total 				= $tariff_record->default_premium;
				$commissionable_premium 	= $premium_A_total;

				// Remove all the sections
				$__CRF_cc_table__A = [
					'column_head' => 'अ',
					'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
					'title_en' 	=> 'Insurance against vehicle damage/loss',
				];
				$__CRF_cc_table__A['sections'] = [];
				$__CRF_cc_table__A['sections'][] = [
					'title' 	=> "सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत",
					'amount' 	=> $premium_A_total
				];

				// Let's have a remarks
				$__remarks = 'Tariff Calculation is below Default Premium Amount.';
			}


			$__CRF_cc_table__A['sections'][] = [
				'title' 	=> "जम्मा",
				'amount' 	=> $premium_A_total,
				'section_total' => true
			];

			// Section Sub Total
			$__CRF_cc_table__A['sub_total'] = $premium_A_total;
		}

			// ---------------------------------------------------------------------------------------
			//  Driver/Insured Accident
			//
			// 	This applies to both thirdparty and comprehensive
			// ---------------------------------------------------------------------------------------

			/**
			 * Driver Accident Insurance (इ)
			 */
			$column_head = 'इ';
            if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
			{
				$column_head = 'आ';
			}
			$accident_premium = $tariff_record->accident_premium ? json_decode($tariff_record->accident_premium) : NULL;
			$insured_value_tariff = $tariff_record->insured_value_tariff ? json_decode($tariff_record->insured_value_tariff) : NULL;
			$__CRF_cc_table__I = [
				'column_head' => $column_head,
				'title_np' 	=> 'चालकको दुर्घटना बीमा वापत',
				'title_en' 	=> 'Driver Accident Insurance'
			];

			$premium_I_total =  $accident_premium->pramt_driver_accident;
			$__CRF_cc_table__I['sections'][] = [
				'title' => "चालक (बीमांक रु. {$insured_value_tariff->driver}  को रु. {$accident_premium->pramt_driver_accident} का दरले)",
				'amount' => $premium_I_total
			];

			// Section Sub Total
			$__CRF_cc_table__I['sub_total'] = $premium_I_total;

			// ---------------------------------------------------------------------------------------


			/**
			 * Insured Party/Passenger Accident Insurance (ई)
			 */
			$column_head = 'ई';
            if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
			{
				$column_head = 'इ';
			}
			$__CRF_cc_table__EE = [
				'column_head' => $column_head,
				'title_np' 	=> 'बीमित तथा यात्रीको दुर्घटना बीमा वापत',
				'title_en' 	=> 'Insured Party & Passenger Accident Insurance'
			];

			$passenger_count = $object_attributes->seating_capacity - 1;
			$premium_EE_total =  $passenger_count * $accident_premium->pramt_accident_per_passenger;
			$__CRF_cc_table__EE['sections'][] = [
				'title' => "प्रति व्यक्ति बीमांक रु. {$insured_value_tariff->passenger} को लागी बीमाशुल्क प्रति सिट रु. {$accident_premium->pramt_accident_per_passenger} का दरले ({$passenger_count} जना)",
				'amount' => $premium_EE_total
			];

			// Section Sub Total
			$__CRF_cc_table__EE['sub_total'] = $premium_EE_total;

			// ---------------------------------------------------------------------------------------


		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Risk Pool (उ)
			 * ---------------
			 *
			 * NOTE: Please note that you have to compute using both the Vehicle total price and
			 * 		Trailer/Trolly Price (if any)
			 */
			$__CRF_cc_table__U = [
				'column_head' => 'उ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];

			$flag_risk_pool = $premium_computation_table['flag_risk_pool'] ?? NULL;
			$__premium_U_row_1 = 0.00;
			$__premium_U_row_2 = 0.00;
			$__premium_U_row_3 = 0.00;
			$__premium_U_row_4 = 0.00;
			$premium_for_insured_covered_on_terorrism = 0.00;
			if($flag_risk_pool)
			{
				// Mob/Strike
				$__premium_U_row_1 = (float)($vehicle_total_si + $si_trailer) * ($tariff_rsik_group->rate_pool_risk_mob/100.00);

				// Terrorism
				$__premium_U_row_2 = (float)($vehicle_total_si + $si_trailer) * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);

				// Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

				// Driver Covered
				// Passenger Cover
				// Driver Count  = 1
				// Passenger Count = Seat Capacity - 1
				// Tariff Rate: rate_additionl_per_thousand_on_extra_rate


				$insured_value_tariff = json_decode($tariff_record->insured_value_tariff);

				$no_of_seat = $object_attributes->seating_capacity;
				$passenger_count = $no_of_seat - 1;

				// Driver Premium
				$__premium_U_row_3 = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

				// Passenger Premium
				$__premium_U_row_4 = $passenger_count * (($insured_value_tariff->passenger/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);

			}
			$__CRF_cc_table__U['sections'][] = [
				'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
				'amount' => $__premium_U_row_1
			];


			// उ TOTAL
			$POOL_PREMIUM = $__premium_U_row_1 + $__premium_U_row_2 + $__premium_U_row_3 + $__premium_U_row_4;

			$__CRF_cc_table__U['sections'][] = [
				'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
				'amount' => $__premium_U_row_2
			];
			$__CRF_cc_table__U['sections'][] = [
				'title' => "(ग) चालक",
				'amount' => $__premium_U_row_3
			];
			$__CRF_cc_table__U['sections'][] = [
				'title' => "(घ) बीमित तथा यात्री ({$passenger_count} जना)",
				'amount' => $__premium_U_row_4
			];

			$__CRF_cc_table__U['sections'][] = [
				'title' 	=> 'जम्मा', // Subtotal
				'amount' 	=> $POOL_PREMIUM,
				'section_total' => true
			];

			// Section Sub Total
			$__CRF_cc_table__U['sub_total'] = $POOL_PREMIUM;
		}


		// ---------------------------------------------------------------------------------------

		/**
		 * Third Party (आ)
		 */
		$tp_column_head = 'आ';
		$tp_label 		= 'छ';
		if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
		{
			$tp_column_head = 'अ';
			$tp_label 		= 'क';
		}
		$__CRF_cc_table__AA = [
			'column_head' => $tp_column_head,
			'title_np' 	=> 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
			'title_en' 	=> 'Third party liability insurance amounted to'
		];

		$__premium_AA_row_1 		 = $premiumm_third_party;
		$__CRF_cc_table__AA['sections'][] = [
			'title' => "सि. सि. अनुसारको बीमाशुल्क",
			'amount' => $__premium_AA_row_1,
			'label' => $tp_label
		];
		$trailer_third_party_premium = 0;
		if( $si_trailer )
		{
			$trailer_third_party_premium = $trolly_tariff->third_party;

			$__CRF_cc_table__AA['sections'][] = [
				'title' => "ट्रेलरको लागि",
				'amount' => $trailer_third_party_premium,
				'label' => ''
			];
		}

		$__premium_AA_row_2 = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount_rate != 0)
		{
			$__premium_AA_row_2 = ($__premium_AA_row_1 + $trailer_third_party_premium) * ($no_claim_discount_rate/100.00);
		}


		// No claim Dicount Only if Comprehensive
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			$__CRF_cc_table__AA['sections'][] = [
				'title' => "{$text_ncd_prefix} दावी नगरे वापत छूटः \"{$tp_label}\" को $no_claim_discount_rate %",
				'amount' => $__premium_AA_row_2
			];
		}

		// Third Party : Sub Total (छ Total)
		$premium_AA_total = ($__premium_AA_row_1 + $trailer_third_party_premium) - $__premium_AA_row_2;
		$__CRF_cc_table__AA['sections'][] = [
			'title' 	=> 'जम्मा', // Subtotal
			'amount' 	=> $premium_AA_total,
			'section_total' => true
		];

		// Section Sub Total
		$__CRF_cc_table__AA['sub_total'] = $premium_AA_total;

		// ---------------------------------------------------------------------------------------



		//
		// Grand Total
		//
		$BASIC_PREMIUM = $premium_A_total + $premium_AA_total  + $premium_I_total + $premium_EE_total;


		//
		// Stamp Duty
		//
		$amt_stamp_duty 	= $post_data['amt_stamp_duty'];


		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
		{
			$cost_calculation_table = [$__CRF_cc_table__AA, $__CRF_cc_table__I, $__CRF_cc_table__EE ];
		}
		else
		{

			$__CRF_cc_table__comprehensive = array_filter([

				// Comprehensive
				$__CRF_cc_table__A,

				// Third Party
				$__CRF_cc_table__AA,

				// Driver Accident
				$__CRF_cc_table__I,

				// Passenger Accident
				$__CRF_cc_table__EE,

				// Risk Group
				$__CRF_cc_table__U
			]);

			$cost_calculation_table = $__CRF_cc_table__comprehensive;


			/**
			 * Agent Commission?
			 * ------------------
			 *
			 * Applies only if the vehicle is non-govt and no direct discount
			 */
			if(
				$policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION
					&&
				$object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT
			){
				$agent_commission 	= ($commissionable_premium * $pfs_record->agent_commission)/100.00;
			}
		}

		/**
		 * Prepare Premium Data
		 */
		$premium_data = [
			'amt_basic_premium' 	=> $BASIC_PREMIUM,
			'amt_commissionable'	=> $commissionable_premium,
			'amt_agent_commission'  => $agent_commission,
			'amt_direct_discount' 	=> $direct_discount,
			'amt_pool_premium' 		=> $POOL_PREMIUM,
		];


		/**
		 * Perform Computation Basis for Endorsement
		 */
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			$premium_data = _ENDORSEMENT_apply_computation_basis($policy_record, $endorsement_record, $pfs_record, $premium_data );
		}
		/**
		 * Short Term Policy???
		 *
		 * Only Fres/Renewal Policy have Short Term Facility
		 */
		else if($policy_record->flag_short_term == IQB_FLAG_YES)
		{
			$spr_goodies 	= _POLICY__get_spr_goodies( $pfs_record, $policy_record->start_date, $policy_record->end_date );
			$premium_data 	= _POLICY__compute_short_term_premium( $spr_goodies['record']->rate ?? NULL, $premium_data, IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BOTH);
		}
		else
		{
			/**
			 * NULLIFY Sort Term Related Fields on Endorsement Table
			 */
			$premium_data = _POLICY__nullify_short_term_premium( $premium_data );
		}

		/**
		 * Compute VAT
		 *
		 * NOTE:
		 * On premium refund, we should also be refunding VAT
		 */
		if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
		{
			// We do not do anything here, because, VAT was applied only on Stamp Duty
			// For other portfolio, it must be set as -ve value

			/**
			 * !!! NO POOL PREMIUM !!!
			 *
			 * Pool premium is not refunded to customer.
			 * NULLify Pool Premium so that it reflects on VAT Computation
			 */
			$premium_data['amt_pool_premium'] = 0.00;
		}
		$CI->load->helper('account');
		$taxable_amount = $post_data['amt_stamp_duty'] + $premium_data['amt_basic_premium'] + $premium_data['amt_pool_premium'];
		$amount_vat 	= ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


		/**
		 * Prepare Other Data
		 */
		$gross_amt_sum_insured 	= $new_object->amt_sum_insured ?? $old_object->amt_sum_insured;
		$net_amt_sum_insured 	= $SI;
		$txn_data = array_merge($premium_data, [
			'gross_amt_sum_insured' => $gross_amt_sum_insured,
			'net_amt_sum_insured' 	=> $net_amt_sum_insured,
			'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
			'amt_vat' 				=> $amount_vat,
		]);

		/**
		 * Premium Computation Table
		 * -------------------------
		 */
		$txn_data['premium_computation_table'] = $premium_computation_table ? json_encode($premium_computation_table) : NULL;


		/**
		 * Cost Calculation Table
		 */
		$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
		return $CI->endorsement_model->save($endorsement_record->id, $txn_data);
	}
}

// -----------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_CVC_premium'))
{
    /**
     * Commercial Vehicle Policy Cost Table
     *
     *
     * @param object $policy_record Policy Record
     * @param object $policy_object Object Record
     * @param object $tariff_record Tariff Record For this Portfolio->Sub-Portfolio
     * @param object $pfs_record Portfolio Settings Record For this Policy's Fiscal year and Portfolio
     * @param array $data Portfolio Related POST Data
     *
     * @return  array
     */
    function _PO_MOTOR_CVC_premium( $policy_record, $endorsement_record, $tariff_record, $pfs_record, $post_data  )
    {
        $CI =& get_instance();

		/**
		 * Policy Object Record
		 *
		 * In case of endorsements, we will be needing both current policy object and edited object information
		 * to compute premium.
		 */
		$old_object = get_object_from_policy_record($policy_record);
		$new_object = NULL;
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			try {
				$new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
			} catch (Exception $e) {

				return $CI->template->json([
                    'status'        => 'error',
                    'title' 		=> 'Exception Occured',
                    'message' 	=> $e->getMessage()
                ], 404);
			}
		}

		// Newest object attributes should be used.
		$object_attributes  = json_decode($new_object->attributes ?? $old_object->attributes);

        // Form Posted Data - Extra Fields Required to Perform Cost Calculation
        $premium_computation_table = $post_data['premium'] ?? NULL;


        // Tariff Extracts
        $default_tariff = json_decode($tariff_record->tariff);

        // Trolly Tariff
        $trolly_tariff = $tariff_record->trolly_tariff ? json_decode($tariff_record->trolly_tariff) : NULL;


        /**
		 * NET Sum Insured & Its Breakdown
		 *
		 *  A. Net SI
		 * 	B. Vehicle Price
		 * 	C. Accessories Price
		 * 	D. Trailer Price
		 */
		$SI 			= _OBJ_si_net($old_object, $new_object);
		$SI_BREAKDOWN 	= _OBJ_si_breakdown_net($old_object, $new_object);
		$si_vehicle 	= $SI_BREAKDOWN['si_vehicle'];
		$si_accessories = $SI_BREAKDOWN['si_accessories'];
		$si_trailer 	= $SI_BREAKDOWN['si_trailer'];
		$vehicle_total_si = $si_vehicle + $si_accessories;


		/**
		 * Seating Breakdown
		 *
		 * NOTE: For Passenger Carrier and Tractor - Staff does not occupy seat
		 */
		$seating_capacity   = $object_attributes->seating_capacity ?? 0;
		$staff_count 		= $object_attributes->staff_count ?? 0;
		if( in_array($object_attributes->cvc_type, [IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER, IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER]) )
		{
			$passenger_count = $seating_capacity - 1;
		}
		else
		{
			// Rest of vehicle type, staff also consumes seats
			$passenger_count    = $seating_capacity - $staff_count - 1; // 1 for driver
		}



        // Vehicle Age (If registration date is there use it otherwise mfd year)
		$ref_date = $object_attributes->reg_date ? $object_attributes->reg_date : $object_attributes->year_mfd . '-01-01';
		$vehicle_reference_date 	= new DateTime($ref_date);
		$today 						= new DateTime(date('Y-m-d'));
		$interval 					= $vehicle_reference_date->diff($today);
		$vehicle_age_in_yrs 		= $interval->y;

        $__remarks = NULL; // Premium Remarks, if tariff calculation falls below default premium

        //
        // Tariff Rate Defaults for Given Engine Capacity
        //      To Minus Amount, Base Fragment, Base Fragment Rate, Rest Fragment Rate, Third Party Rate, Vehicle Over Age Rate
        //      Example: First 20 lakhs 1.24% + Rest Amount's 1.6% - Rs. 3000
        //
        $primary_tariff_vehicle = _PO_MOTOR_CVC_primary_tariff_vehicle($object_attributes, $default_tariff, $vehicle_age_in_yrs);

        // No Claim Discount - Years & Rate
        $year_no_claim_discount = $premium_computation_table['no_claim_discount'] ?? 0;
		$no_claim_discount_rate = _PO_MOTOR_no_claim_discount_rate($year_no_claim_discount, $tariff_record->no_claim_discount);
		$text_ncd_prefix 		= _PO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, 'np', FALSE)[$year_no_claim_discount] ?? '';

        // Rsik Group
        $tariff_rsik_group = json_decode($tariff_record->riks_group);

         /**
         * Package Comprehensive
         */
        $premium_A_total = 0.00;
        $premium_AA_total = 0.00;
        $premium_I_total = 0.00;
        $premium_EE_total = 0.00;
        $premium_U_total = 0.00;
        $POOL_PREMIUM = 0.00;
        $commissionable_premium = NULL;
        $agent_commission 		= NULL;
        $direct_discount 		= NULL;
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {
            /**
             * Defaults (अ)
             */
            $__CRF_cc_table__A = [
                'column_head' => 'अ',
                'title_np'  => 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
                'title_en'  => 'Insurance against vehicle damage/loss',
            ];

            // Statement 1 is common for all
            $statement_1 = "घोषित मूल्य (सरसामान सहित) अनुसार शुरु बीमा शुल्क (घोषित मूल्यको " . $primary_tariff_vehicle['default_rate'] . " %)";

            // Common Row 1 - Ghosit Mulya ko X %
            $__premium_A_row_1 = $vehicle_total_si * ($primary_tariff_vehicle['default_rate'] / 100.00);
            $__CRF_cc_table__A['sections'][] = [
                'title'     => $statement_1,
                'amount'    => $__premium_A_row_1
            ];

            // Trailer Bapat ko Sulka ( Trailer ko ghosit mulya ko X %)
            $statement_trailer = "ट्रेलर (मालसामान ओसार्ने) वापतको बीमाशुल्क ( घोषित मूल्यको{$trolly_tariff->rate}% - रु. {$trolly_tariff->minus_amount})का दरले";
            $__premium_A_row_trailer = 0.00;
            if( $si_trailer )
            {
                  $__premium_A_row_trailer = ( $si_trailer * ($trolly_tariff->rate/100.00) ) - $trolly_tariff->minus_amount;
            }

            // ----------------------------------------------------------------------------------

            switch ( $object_attributes->cvc_type )
            {
                case IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL:
                case IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER:
                case IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY:
                case IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT:
                    $statement_2 = "भारबहन क्षमता अनुसार शरु बीमाशुल्कमा थप";
                    $statement_3 = "भारबहन क्षमता अनुसार शरु बीमाशुल्कमा छूट";

                    // Bharbahan xamata anusar suru bima sulka ma thap ( X ton mathi Per Ton Z)
                    $engine_capacity_above_threshold = ($primary_tariff_vehicle['vehicle_capacity'] > $primary_tariff_vehicle['ec_threshold']) ? $primary_tariff_vehicle['vehicle_capacity'] - $primary_tariff_vehicle['ec_threshold'] : 0;
                    $__premium_A_row_2 = $engine_capacity_above_threshold * $primary_tariff_vehicle['cost_per_ec_above'];
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Bharbahan xamata anusar suru bima sulka ma xut
                    $__premium_A_row_3 = $primary_tariff_vehicle['minus_amount'];
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_3,
                        'amount'    => $__premium_A_row_3
                    ];

                    // Trailer Premium
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_2 - $__premium_A_row_3 + $__premium_A_row_trailer;;
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER:
                case IQB_MOTOR_CVC_TYPE_TEMPO:
                    // Trailer Premium
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_trailer;;
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER:
                    // Seat xamata anusar suru bimasulka ma xut
                    $statement_2        = "सिट क्षमता अनुसार शुरु बीमाशुल्कमा छूट";
                    $__premium_A_row_2  = $primary_tariff_vehicle['minus_amount'];
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Trailer Premium
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 - $__premium_A_row_2 + $__premium_A_row_trailer;
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_TAXI:
                    $statement_2 = "सि.सि.अनुसार शुरु बीमा शुल्कमा थप";
                    $__premium_A_row_2 = $primary_tariff_vehicle['plus_amount'];
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_2;
                    $__CRF_cc_table__A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                default:
                    # code...
                    break;
            }

            // ----------------------------------------------------------------------------------

            // Old Age Premium
            $__premium_A_row_old_age = 0.00;
            if( $primary_tariff_vehicle['over_age_rate'] > 0.00 )
            {
                $__premium_A_row_old_age = $__premium_A_row_KA * ($primary_tariff_vehicle['over_age_rate']/100.00);

                $__CRF_cc_table__A['sections'][] = [
	                'title' => "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को " . $primary_tariff_vehicle['over_age_rate'] . " %",
	                'amount' => $__premium_A_row_old_age
	            ];
            }


            //
            // ख Sub Total
            //
            $__premium_A_row_KHA =  $__premium_A_row_KA + $__premium_A_row_old_age;
            $__CRF_cc_table__A['sections'][] = [
                'title'     => "",
                'amount'    => $__premium_A_row_KHA,
                'label'     => 'ख'
            ];

            // ----------------------------------------------------------------------------------

            // Discount on Voluntary Excess - GA ko X%
            $dr_voluntary_excess        = $premium_computation_table['dr_voluntary_excess'] ?? FALSE;
            $amount_voluntary_excess    = $dr_voluntary_excess
                                            ? _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess]
                                            : 0.00;
            $__discount_A_row__voluntary_excess         = 0.00;
            if( $dr_voluntary_excess )
            {
                $__discount_A_row__voluntary_excess = $__premium_A_row_KHA * ($dr_voluntary_excess/100.00);
            }
            $__CRF_cc_table__A['sections'][] = [
                'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
                'amount' => $__discount_A_row__voluntary_excess
            ];

            //
            // ग Sub Total
            //
            $__premium_A_row_GA =  $__premium_A_row_KHA - $__discount_A_row__voluntary_excess;
            $__CRF_cc_table__A['sections'][] = [
                'title'     => "",
                'amount'    => $__premium_A_row_GA,
                'label'     => 'ग'
            ];

            // ----------------------------------------------------------------------------------

            // NO CLAIM DISCOUNT
            $__discount_A_row__no_claim_discount = 0.00;
            if($no_claim_discount_rate)
            {
                $__discount_A_row__no_claim_discount = $__premium_A_row_GA * ($no_claim_discount_rate/100.00);
            }
            $__CRF_cc_table__A['sections'][] = [
                'title' => "{$text_ncd_prefix} दावी नगरे वापत छूटः “ग” को {$no_claim_discount_rate} %",
                'amount' => $__discount_A_row__no_claim_discount
            ];

            //
            // घ Sub Total
            //
            $__premium_A_row_GHA =  $__premium_A_row_GA - $__discount_A_row__no_claim_discount;
            $__CRF_cc_table__A['sections'][] = [
                'title'     => "",
                'amount'    => $__premium_A_row_GHA,
                'label'     => 'घ'
            ];

            // ----------------------------------------------------------------------------------

            /**
             * Private Use Discount
             *  - applies only Non-govt {goods carrier, passenger carrier}, NOT Taxi
             */
            $__premium_A_row_NGA = 0.00;
            $__discount_A_row_on_personal_use = 0.00;
            $statement = "निजी प्रयोजनमा प्रयोग गरे वापत छुट: \"घ\" को {$tariff_record->dr_cvc_on_personal_use} %";
            if( $object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
            {
                switch ( $object_attributes->cvc_type )
                {
                    case IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL:
                    case IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER:
                    case IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY:
                    case IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT:
                    case IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER:
                    case IQB_MOTOR_CVC_TYPE_TEMPO:
                    case IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER:
                        $flag_private_use = $premium_computation_table['flag_private_use'] ?? FALSE;
                        if( $flag_private_use )
                        {
                            $__discount_A_row_on_personal_use = $__premium_A_row_GHA * ($tariff_record->dr_cvc_on_personal_use/100.00);
                        }
                        break;

                    default:
                        # code...
                        break;
                }
                $__CRF_cc_table__A['sections'][] = [
                    'title'  => $statement,
                    'amount' => $__discount_A_row_on_personal_use
                ];

                //
                // ङ Sub Total
                //
                $__premium_A_row_NGA =  $__premium_A_row_GHA - $__discount_A_row_on_personal_use;
                $__CRF_cc_table__A['sections'][] = [
                    'title'     => "",
                    'amount'    => $__premium_A_row_NGA,
                    'label'     => 'ङ'
                ];
            }
            else
            {
                $__premium_A_row_NGA =  $__premium_A_row_GHA - $__discount_A_row_on_personal_use;
            }

            $commissionable_premium = $__premium_A_row_NGA;
            // ----------------------------------------------------------------------------------


            // Agent Commission/Direct Discount? -> Applies only Non-GOVT
            if( $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT && $object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
            {
                // X% of ङ
                $direct_discount = $__premium_A_row_NGA * ($pfs_record->direct_discount/100.00);
                $dd_formatted 	 = number_format($pfs_record->direct_discount, 2);
                $__CRF_cc_table__A['sections'][] = [
                    'title' => "प्रत्यक्ष बीमा वापत छुटः “ङ” को {$dd_formatted} %",
                    'amount' => $direct_discount
                ];
            }

            // Towing
            $flag_towing = $premium_computation_table['flag_towing'] ?? FALSE;
            $__premium_A_row_towing = 0.00;
            if( $flag_towing )
            {
                $__premium_A_row_towing = $tariff_record->pramt_towing ?? 0.00;
            }
            $__CRF_cc_table__A['sections'][] = [
                'title' => "दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा वापत बीमा शुल्क थप",
                'amount' => $__premium_A_row_towing
            ];

            // ----------------------------------------------------------------------------------


            //
            // अ Sub Total
            //
            $premium_A_total =  $__premium_A_row_NGA - $direct_discount + $__premium_A_row_towing;

            /**
			 * Default Premium Check -- Fresh/Renewal ONLY
			 * -------------------------------------------
			 *
			 * If $premium_A_total  is below the default premium amount, we should use
			 * the default premium amount from Motor Tariff.
			 */
			if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) && $tariff_record->default_premium > $premium_A_total )
			{

				// Premium Total and Commissionable Amount to Default.
				$premium_A_total 				= $tariff_record->default_premium;
				$commissionable_premium 	= $premium_A_total;

				// Remove all the sections
				$__CRF_cc_table__A = [
					'column_head' => 'अ',
					'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
					'title_en' 	=> 'Insurance against vehicle damage/loss',
				];
				$__CRF_cc_table__A['sections'] = [];
				$__CRF_cc_table__A['sections'][] = [
					'title' 	=> "सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत",
					'amount' 	=> $premium_A_total
				];

				// Let's have a remarks
				$__remarks = 'Tariff Calculation is below Default Premium Amount.';
			}


            $__CRF_cc_table__A['sections'][] = [
                'title'     => "जम्मा",
                'amount'    => $premium_A_total,
                'section_total' => true
            ];

            // Section Sub Total
			$__CRF_cc_table__A['sub_total'] = $premium_A_total;

		}

			// ---------------------------------------------------------------------------------------
			//  Driver/Staff/Passenger Accident
			//
			// 	This applies to both thirdparty and comprehensive
			// ---------------------------------------------------------------------------------------

            // ---------------------------------------------------------------------------------------
            //  इ) Driver Accident
            // ---------------------------------------------------------------------------------------

            $accident_premium = $tariff_record->accident_premium ? json_decode($tariff_record->accident_premium) : NULL;
            $insured_value_tariff = $tariff_record->insured_value_tariff ? json_decode($tariff_record->insured_value_tariff) : NULL;

            $column_head = 'इ';
            if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
			{
				$column_head = 'आ';
			}

            $__CRF_cc_table__I = [
                'column_head' => $column_head,
                'title_np'  => 'चालकको दुर्घटना बीमा वापत',
                'title_en'  => 'Driver Accident Insurance'
            ];

            $premium_I_total =  $accident_premium->pramt_driver_accident;
            $__CRF_cc_table__I['sections'][] = [
                'title' => "चालक (बीमांक रु. {$insured_value_tariff->driver}  को रु. {$accident_premium->pramt_driver_accident} का दरले)",
                'amount' => $premium_I_total
            ];

            // Section Sub Total
			$__CRF_cc_table__I['sub_total'] = $premium_I_total;


            // ---------------------------------------------------------------------------------------
            //  ई) Staff Accident
            // ---------------------------------------------------------------------------------------

			$column_head = 'ई';
            if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
			{
				$column_head = 'इ';
			}
            $__CRF_cc_table__EE = [
                'column_head' => $column_head,
                'title_np'  => 'परिचालक तथा अन्य कर्मचारी (चालक बाहेक) को दुर्घटना बीमा वापत',
                'title_en'  => 'Staff Accident Insurance'
            ];

            $premium_EE_total =  $accident_premium->pramt_accident_per_staff * $staff_count;
            $__CRF_cc_table__EE['sections'][] = [
                'title' => "प्रति ब्यक्ति (बीमांक रु. {$insured_value_tariff->staff}  को लागि प्रति ब्यक्ति रु. {$accident_premium->pramt_accident_per_staff} का दरले) ({$staff_count} जना)",
                'amount' => $premium_EE_total
            ];

            // Section Sub Total
			$__CRF_cc_table__EE['sub_total'] = $premium_EE_total;


            // ---------------------------------------------------------------------------------------
            //  उ) Passenger Accident
            // ---------------------------------------------------------------------------------------

			$column_head = 'उ';
            if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
			{
				$column_head = 'ई';
			}
            $__CRF_cc_table__U = [
                'column_head' => $column_head,
                'title_np'  => 'यात्रीको दुर्घटना बीमा वापत',
                'title_en'  => 'Passenger Accident Insurance'
            ];

            $premium_U_total   =  $accident_premium->pramt_accident_per_passenger * $passenger_count;
            $__CRF_cc_table__U['sections'][] = [
                'title' => "प्रति ब्यक्ति (बीमांक रु. {$insured_value_tariff->passenger}  को लागि प्रति ब्यक्ति रु. {$accident_premium->pramt_accident_per_passenger} का दरले) ({$passenger_count} जना)",
                'amount' => $premium_U_total
            ];

            // Section Sub Total
			$__CRF_cc_table__U['sub_total'] = $premium_U_total;


		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {

            // ---------------------------------------------------------------------------------------
            //  ऊ) Risk Group
            //
            //  NOTE: Please note that you have to compute using both the Vehicle total price and
            // 			Trailer/Trolly Price (if any)
            // ---------------------------------------------------------------------------------------
            $__CRF_cc_table__OO = [
                'column_head' => 'ऊ',
                'title_np'  => 'जोखिम समूह थप गरे वापत',
                'title_en'  => 'Pool risk insurance amounted to'
            ];

            $flag_risk_mob          = $premium_computation_table['flag_risk_mob'] ?? NULL;
            $flag_risk_terorrism    = $premium_computation_table['flag_risk_terorrism'] ?? NULL;

            // Mob/Strike
            $__premium_OO_row_1 = 0.00;
            $__premium_OO_row_2 = 0.00;
            $__premium_OO_row_3 = 0.00;
            $__premium_OO_row_4 = 0.00;
            $__premium_OO_row_5 = 0.00;
            $flag_risk_pool = $premium_computation_table['flag_risk_pool'] ?? NULL;
            if($flag_risk_pool)
            {
            	// Mob/Strike
                $__premium_OO_row_1 = (float)($vehicle_total_si + $si_trailer) * ($tariff_rsik_group->rate_pool_risk_mob/100.00);

                // Terrorism
                $__premium_OO_row_2 = (float)( ($vehicle_total_si + $si_trailer) * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00));

                // Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

                // Driver Covered
                // Staff Covered
                // Passenger Cover
                // Driver Count  = 1
                // Passenger Count = Seat Capacity
                // Tariff Rate: rate_additionl_per_thousand_on_extra_rate

                // Driver Premium
                $__premium_OO_row_3 = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

                // Staff
                $__premium_OO_row_4 = ($insured_value_tariff->staff/1000.00) * $staff_count * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

                // Passenger Premium
                $__premium_OO_row_5 = $passenger_count * (($insured_value_tariff->passenger/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);

            }
            $__CRF_cc_table__OO['sections'][] = [
                'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
                'amount' => $__premium_OO_row_1
            ];

            // उ TOTAL
            $POOL_PREMIUM = $__premium_OO_row_1 + $__premium_OO_row_2 + $__premium_OO_row_3 + $__premium_OO_row_4 + $__premium_OO_row_5;

            $__CRF_cc_table__OO['sections'][] = [
                'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
                'amount' => $__premium_OO_row_2
            ];
            $__CRF_cc_table__OO['sections'][] = [
                'title' => "(ग) चालक",
                'amount' => $__premium_OO_row_3
            ];
            $__CRF_cc_table__OO['sections'][] = [
                'title' => "(घ) परिचालक तथा अन्य कर्मचारी ({$staff_count} जना)",
                'amount' => $__premium_OO_row_4
            ];
            $__CRF_cc_table__OO['sections'][] = [
                'title' => "(ङ) यात्री ({$passenger_count} जना)",
                'amount' => $__premium_OO_row_5
            ];

            $__CRF_cc_table__OO['sections'][] = [
                'title'     => 'जम्मा', // Subtotal
                'amount'    => $POOL_PREMIUM,
                'section_total' => true
            ];

            // Section Sub Total
			$__CRF_cc_table__OO['sub_total'] = $POOL_PREMIUM;
        }

        // ---------------------------------------------------------------------------------------

        /**
         * Third Party (आ)
         */
        $tp_column_head = 'आ';
		$tp_label 		= 'छ';
		if( strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY )
		{
			$tp_column_head = 'अ';
			$tp_label 		= 'क';
		}

        $__CRF_cc_table__AA = [
            'column_head' => $tp_column_head,
            'title_np'  => 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
            'title_en'  => 'Third party liability insurance amounted to'
        ];

        $__premium_AA_row_1 = $primary_tariff_vehicle['third_party'];
        $__CRF_cc_table__AA['sections'][] = [
            'title' => "क्षमता अनुसारको बीमाशुल्क",
            'amount' => $__premium_AA_row_1,
            'label' => $tp_label
        ];

        $trailer_third_party_premium = 0;
		if( $si_trailer )
		{
			$trailer_third_party_premium = $trolly_tariff->third_party;

			$__CRF_cc_table__AA['sections'][] = [
				'title' => "ट्रेलरको लागि",
				'amount' => $trailer_third_party_premium,
				'label' => ''
			];
		}

        $__premium_AA_row_2 = 0.00;     // Compute No claim discount on Third Party if Comprehensive Package
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount_rate != 0)
        {
            $__premium_AA_row_2 = ($__premium_AA_row_1 + $trailer_third_party_premium) * ($no_claim_discount_rate/100.00);
        }


        // No claim Dicount Only if Comprehensive
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {
            $__CRF_cc_table__AA['sections'][] = [
                'title' => "{$text_ncd_prefix} दावी नगरे वापत छूटः \"{$tp_label}\" को $no_claim_discount_rate %",
                'amount' => $__premium_AA_row_2
            ];
        }

        // Third Party : Sub Total
        $premium_AA_total = ( $__premium_AA_row_1 + $trailer_third_party_premium ) - $__premium_AA_row_2;
        $__CRF_cc_table__AA['sections'][] = [
            'title'     => 'जम्मा', // Subtotal
            'amount'    => $premium_AA_total,
            'section_total' => true
        ];

        // Section Sub Total
		$__CRF_cc_table__AA['sub_total'] = $premium_AA_total;

        // ---------------------------------------------------------------------------------------
        // Total Computation
        // ---------------------------------------------------------------------------------------

        //
        // Grand Total
        //
        $BASIC_PREMIUM = $premium_A_total + $premium_AA_total  + $premium_I_total + $premium_EE_total + $premium_U_total;


        //
        // Stamp Duty
        //
        $amt_stamp_duty = $post_data['amt_stamp_duty'];


        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
        {
            $cost_calculation_table = [$__CRF_cc_table__AA, $__CRF_cc_table__I, $__CRF_cc_table__EE, $__CRF_cc_table__U];
        }
        else
        {

            $__CRF_cc_table__comprehensive = array_filter([

                // Comprehensive
                $__CRF_cc_table__A,

                // Third Party
                $__CRF_cc_table__AA,

                // Driver Accident
                $__CRF_cc_table__I,

                // Staff Accident
                $__CRF_cc_table__EE,

                // Passenger Accident
                $__CRF_cc_table__U,

                // Risk Group
                $__CRF_cc_table__OO
            ]);

            $cost_calculation_table = $__CRF_cc_table__comprehensive;


            /**
             * Agent Commission?
             * ------------------
             *
             * Applies only if the vehicle is non-govt and no direct discount
             */
            if(
            	$policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION
            	&&
            	$object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT
        	){
                $agent_commission 	= ($commissionable_premium * $pfs_record->agent_commission)/100.00;
            }
        }


        /**
		 * Prepare Premium Data
		 */
		$premium_data = [
			'amt_basic_premium' 	=> $BASIC_PREMIUM,
			'amt_commissionable'	=> $commissionable_premium,
			'amt_agent_commission'  => $agent_commission,
			'amt_direct_discount' 	=> $direct_discount,
			'amt_pool_premium' 		=> $POOL_PREMIUM,
		];


		/**
		 * Perform Computation Basis for Endorsement
		 */
		if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
		{
			$premium_data = _ENDORSEMENT_apply_computation_basis($policy_record, $endorsement_record, $pfs_record, $premium_data );
		}
		/**
		 * Short Term Policy???
		 *
		 * Only Fres/Renewal Policy have Short Term Facility
		 */
		else if($policy_record->flag_short_term == IQB_FLAG_YES)
		{
			$spr_goodies 	= _POLICY__get_spr_goodies( $pfs_record, $policy_record->start_date, $policy_record->end_date );
			$premium_data 	= _POLICY__compute_short_term_premium( $spr_goodies['record']->rate ?? NULL, $premium_data, IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BOTH);
		}
		else
		{
			/**
			 * NULLIFY Sort Term Related Fields on Endorsement Table
			 */
			$premium_data = _POLICY__nullify_short_term_premium( $premium_data );
		}

		/**
		 * Compute VAT
		 *
		 * NOTE:
		 * On premium refund, we should also be refunding VAT
		 */
		if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
		{
			// We do not do anything here, because, VAT was applied only on Stamp Duty
			// For other portfolio, it must be set as -ve value

			/**
			 * !!! NO POOL PREMIUM !!!
			 *
			 * Pool premium is not refunded to customer.
			 * NULLify Pool Premium so that it reflects on VAT Computation
			 */
			$premium_data['amt_pool_premium'] = 0.00;
		}
		$CI->load->helper('account');
		$taxable_amount = $post_data['amt_stamp_duty'] + $premium_data['amt_basic_premium'] + $premium_data['amt_pool_premium'];
		$amount_vat 	= ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


		/**
		 * Prepare Other Data
		 */
		$gross_amt_sum_insured 	= $new_object->amt_sum_insured ?? $old_object->amt_sum_insured;
		$net_amt_sum_insured 	= $SI;
		$txn_data = array_merge($premium_data, [
			'gross_amt_sum_insured' => $gross_amt_sum_insured,
			'net_amt_sum_insured' 	=> $net_amt_sum_insured,
			'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
			'amt_vat' 				=> $amount_vat,
		]);

		/**
		 * Premium Computation Table
		 * -------------------------
		 */
		$txn_data['premium_computation_table'] = $premium_computation_table ? json_encode($premium_computation_table) : NULL;


		/**
		 * Cost Calculation Table
		 */
		$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
		return $CI->endorsement_model->save($endorsement_record->id, $txn_data);
    }
}

// -----------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_CVC_primary_tariff_vehicle'))
{
    /**
     * Get Primary Tariff of a Vehicle
     *
     * @param object $object_attributes
     * @param object $default_tariff  JSON decoded tariff column from Motor Tariff Table
     * @param integer $vehicle_age_in_yrs Vehicle Age in Years (From the Date of Registration)
     * @return array
     */
    function _PO_MOTOR_CVC_primary_tariff_vehicle($object_attributes, $default_tariff, $vehicle_age_in_yrs=0)
    {
        $primary_tariff_vehicle['default_rate']         = 0.00;
        $primary_tariff_vehicle['minus_amount']         = 0.00;
        $primary_tariff_vehicle['plus_amount']          = 0.00;
        $primary_tariff_vehicle['ec_threshold']         = 0;
        $primary_tariff_vehicle['vehicle_capacity']     = 0; // depending upon cvc_type ( )
        $primary_tariff_vehicle['cost_per_ec_above']    = 0.00;

        $primary_tariff_vehicle['third_party']            = 0.00;
        $primary_tariff_vehicle['over_age_rate']          = 0.00;

        foreach ($default_tariff as $t)
        {
            // Should also match Engine Capacity Type/Carrying Capacity

        	if( in_array($object_attributes->cvc_type, [
        													IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL,
        													IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER,
        													IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER,
        													IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY,
        													IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT ]))
        	{
        		$capacity_type = $object_attributes->carrying_unit === $t->ec_type;
        		$capacity_column = "carrying_capacity";

        		// Computable Engine Capacity for Cost table
        		$primary_tariff_vehicle['vehicle_capacity'] = $object_attributes->carrying_capacity;
        	}
        	else
        	{
        		$capacity_type = $object_attributes->ec_unit === $t->ec_type;
        		$capacity_column = "engine_capacity";

        		// Computable Engine Capacity for Cost table
        		$primary_tariff_vehicle['vehicle_capacity'] = $object_attributes->engine_capacity;
        	}


            if( $object_attributes->{$capacity_column} >= $t->ec_min && $object_attributes->{$capacity_column} <= $t->ec_max && $capacity_type === TRUE )
            {
                $primary_tariff_vehicle['default_rate']                = (float)$t->default_rate;
                $primary_tariff_vehicle['minus_amount']                = (float)$t->minus_amount;
                $primary_tariff_vehicle['plus_amount']                 = (float)$t->plus_amount;

                $primary_tariff_vehicle['ec_threshold']                 = $t->ec_threshold;
                $primary_tariff_vehicle['cost_per_ec_above']            = (float)$t->cost_per_ec_above;;



                $primary_tariff_vehicle['third_party']                   = (float)$t->third_party;

                if( $t->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age1_max )
                {
                    $primary_tariff_vehicle['over_age_rate'] = (float)$t->rate1;
                }
                else if( $t->age2_min <= $vehicle_age_in_yrs )
                {
                    $primary_tariff_vehicle['over_age_rate'] = (float)$t->rate2;
                }
                break;
            }
        }

        return $primary_tariff_vehicle;
    }
}


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MOTOR_row_snippet'))
{
	/**
	 * Get Policy Object - Motor - Row Snippet
	 *
	 * Row Partial View for Motor Object
	 *
	 * @param object $record Policy Object (Motor)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MOTOR_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_motor', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_select_text'))
{
	/**
	 * Get Policy Object - Motor - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MOTOR_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$select_text = '';
		if($attributes)
		{
			$data = [
				$attributes->make,
				$attributes->model,
				'Reg No: ' . implode(' ', array_unique([$attributes->reg_no_prefix, $attributes->reg_no]) ),
				'Engine NO: ' . $attributes->engine_no,
				'Chasis No: ' . $attributes->chasis_no
			];

			$select_text = implode(', ', $data);
		}
		return $select_text;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_validation_rules'))
{
	/**
	 * Get Policy Object - Motor - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MOTOR_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post = $CI->input->post();
		$object = $post['object'] ?? NULL;

		// CVC TYPES in_list validation
		$cvc_type_list = array_keys( _OBJ_MOTOR_CVC_type_dropdown(FALSE) );
		$cvc_type_in_list = implode(',', $cvc_type_list);

		// To Be Intimated Set?
		$flag_to_be_intimated = $object['flag_to_be_intimated'] ?? NULL;
		if($flag_to_be_intimated)
		{
			$reg_no_prefix_rules  	= 'trim|required|alpha_numeric_spaces|max_length[30]|strtoupper|in_list[TO BE INTIMATED]';
			$reg_no_rules  			= 'trim|required|max_length[30]|strtoupper|in_list[TO BE INTIMATED]';
		}
		else
		{
			$reg_no_prefix_rules  	= 'trim|required|alpha_numeric_spaces|max_length[30]|strtoupper|callback__cb_motor_valid_reg_prefix';
			$reg_no_rules  			= 'trim|required|integer|max_length[4]|callback__cb_motor_duplicate_reg_no';
		}
		$ec_dropdown = _OBJ_MOTOR_carrying_unit_dropdown(FALSE);

		/**
		 * Object Sections
		 * -----------------
		 * 	a. Common Vehicle Section
		 * 	b. CVC Specific, Staff (CVC Specific)
		 * 	c. Trailer (Private/Commercial Vehicle)
		 */

		$v_rules = [

			// Vehicle Common Fields
			'vehicle-common' =>[
				[
			        'field' => 'object[ownership]',
			        '_key' => 'ownership',
			        'label' => 'Ownership',
			        'rules' => 'trim|required|alpha|in_list[G,N]',
			        '_type'     => 'dropdown',
			        '_data' 	=> _OBJ_MOTOR_ownership_dropdown( ),
			        '_default'  => 'N',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[flag_mcy_df]',
			        '_key' => 'flag_mcy_df',
			        'label' => 'Disabled friendly Vehicle',
			        'rules' => 'trim|integer|in_list[1]',
			        '_id' 		=> '_motor-vehicle-df',
			        '_type'     => 'checkbox',
			        '_checkbox_value' 	=> '1',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[engine_no]',
			        '_key' => 'engine_no',
			        'label' => 'Engine Number',
			        'rules' => 'trim|required|max_length[30]|strtoupper|callback__cb_motor_duplicate_engine_no',
			        '_id' 		=> '_motor-engine-no',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[chasis_no]',
			        '_key' => 'chasis_no',
			        'label' => 'Chasis Number',
			        'rules' => 'trim|required|max_length[30]|strtoupper|callback__cb_motor_duplicate_chasis_no',
			        '_id' 		=> '_motor-chasis-no',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[ec_unit]',
			        '_key' => 'ec_unit',
			        'label' => 'Engine Capacity Unit',
			        'rules' => 'trim|required|alpha|in_list[CC,HP,KW]',
			        '_id' 		=> '_motor-vehicle-ec-unit',
			        '_type'     => 'dropdown',
			        '_data' 	=> _OBJ_MOTOR_ec_unit_dropdown(),
			        '_required' => true
			    ],
			    [
			        'field' => 'object[engine_capacity]',
			        '_key' => 'engine_capacity',
			        'label' => 'Engine Capacity',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
			        '_id' 		=> '_motor-engine-capacity',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[year_mfd]',
			        '_key' => 'year_mfd',
			        'label' => 'Year Manufactured',
			        'rules' => 'trim|required|integer|exact_length[4]',
			        '_id' 		=> '_motor-mfd-year',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[puchase_date]',
			        '_key' => 'puchase_date',
			        'label' => 'Purchase Date',
			        'rules' => 'trim|valid_date',
			        '_id' 		=> '_motor-purchase-date',
			        '_type'     => 'date',
			        '_required' => false
			    ],
			    [
			    	'field' => 'object[vechile_status]',
			        '_key' => 'vechile_status',
			        'label' => 'Status on Purchase',
			        'rules' => 'trim|required|alpha|in_list[N,O]',
			        '_id' 		=> '_motor-purchase-status',
			        '_type'     => 'dropdown',
			        '_data' 	=> _OBJ_MOTOR_vehicle_status_dropdown(),
			        '_required' => true
			    ],
			    [
			        'field' => 'object[manufacturer]',
			        '_key' => 'manufacturer',
			        'label' => 'Manufacturer',
			        'rules' => 'trim|required|max_length[80]',
			        '_id' 		=> '_motor-manufacturer',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[make]',
			        '_key' => 'make',
			        'label' => 'Make',
			        'rules' => 'trim|required|max_length[60]',
			        '_id' 		=> '_motor-make',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[model]',
			        '_key' => 'model',
			        'label' => 'Model',
			        'rules' => 'trim|required|max_length[60]',
			        '_id' 		=> '_motor-model',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[price_vehicle]',
			        '_key' => 'price_vehicle',
			        'label' => 'Vehicle Price',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_id' 		=> '_motor-vehicle-price',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[accessories]',
			        '_key' => 'accessories',
			        'label' => 'Accessories',
			        'rules' => 'trim|max_length[500]',
			        '_id' 		=> '_motor-accessories',
			        '_type'     => 'textarea',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[price_accessories]',
			        '_key' => 'price_accessories',
			        'label' => 'Accessories Total Price',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
			        '_id' 		=> '_motor-accessories-price',
			        '_type'     => 'text',
			        '_default' 	=> 0.00,
			        '_required' => true
			    ]
		    ],

		    // Vehicle Common Fields - Registration Numbers
		    'vehicle-registration' => [
		    	// For New Vehicle, It can Have "To Be Intimated"
			    [
			        'field' => 'object[flag_to_be_intimated]',
			        '_key' => 'flag_to_be_intimated',
			        'label' => 'To Be Intimated',
			        'rules' => 'trim|integer|in_list[1]',
			        '_id' 		=> '_motor-vehicle-to-be-intimated',
			        '_type'     => 'checkbox',
			        '_checkbox_value' 	=> '1',
			        '_required' => false,
			        '_help_text' => 'Click here if the vehicle is not registered yet.'
			    ],

			    [
			        'field' => 'object[reg_no_prefix]',
			        '_key' => 'reg_no_prefix',
			        'label' => 'Registration Type',
			        'rules' => $reg_no_prefix_rules,
			        '_id' 		=> '_motor-vechicle-reg_no_prefix',
			        '_class' 	=> 'form-control typeahead vehicle-reg-input',
			        '_type'     => 'text',
			        '_help_text' => 'Example - BA 1 PA',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[reg_no]',
			        '_key' => 'reg_no',
			        'label' => 'Registration Number',
			        'rules' => $reg_no_rules,
			        '_class' 	=> 'form-control vehicle-reg-input',
			        '_id' 		=> '_motor-vechicle-reg_no',
			        '_type'     => 'text',
			        '_help_text' => 'Example - 3039',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[reg_date]',
			        '_key' => 'reg_date',
			        'label' => 'Registration Date',
			        'rules' => 'trim|valid_date',
			        '_class' 	=> 'form-control vehicle-reg-input',
			        '_id' 		=> '_motor-vechicle-reg_date',
			        '_type'     => 'date',
			        '_required' => false
			    ],
		    ],

		    // Vehicle Common Fields - Vehicle Transit from Custom to KTM or other places
		    'vehicle-custom' => [
		    	// For New Vehicle while transferring from Custom to City
			    [
			        'field' => 'object[custom_info]',
			        '_key' => 'custom_info',
			        'label' => 'Custom Info',
			        'rules' => 'trim|max_length[500]',
			        '_type'     => 'textarea',
			        '_required' => false,
			        '_help_text' => 'This field is for LC NO/Date, Invoice NO/Date, Bank Info etc for those vehicle in transit/custom to showroom.'
			    ]
		    ],


		    // Seating Capacity - PVC and CVC
		    'seating-capcity' => [
			    [
			        'field' => 'object[seating_capacity]',
			        '_key' => 'seating_capacity',
			        'label' => 'Total Seats (Including Driver Seat)',
			        'rules' => 'trim|required|integer|max_length[5]',
			        '_id' 		=> '_motor-seating-capacity',
			        '_type'     => 'text',
			        '_required' => true
			    ]
		    ],

		    // Carrying Capicity - CVC
		    'carrying-capcity' => [
		    	[
		            'field' => 'object[carrying_unit]',
			        '_key' => 'carrying_unit',
			        'label' => 'Unit',
			        'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($ec_dropdown)).']',
			        '_id' 		=> '_motor-vehicle-carrying-unit',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $ec_dropdown,
			        '_required' => true

			    ],
			    [
			        'field' => 'object[carrying_capacity]',
			        '_key' => 'carrying_capacity',
			        'label' => 'Carrying Capacity (भारबहन क्षमता )',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
			        '_id' 		=> '_motor-carrying-capacity',
			        '_type'     => 'text',
			        '_help_text' => '<i class="fa fa-info-circle"></i> Type zero(0) if NOT APPLICABLE.',
			        '_required' => true
			    ]
		    ],


		    // Commercial Vehicle Extra Fields
		    'vehicle-cvc' => [
		    	[
			        'field' => 'object[cvc_type]',
			        '_key' => 'cvc_type',
			        'label' => 'Commercial Vehicle Type',
			        'rules' => 'trim|required|alpha|strtoupper|in_list[' . $cvc_type_in_list . ']',
			        '_id' 		=> '_motor-vehicle-cvc-type',
			        '_type'     => 'dropdown',
			        '_data' 	=> _OBJ_MOTOR_CVC_type_dropdown(),
			        '_required' => true
			    ],
		    ],

		    // Staff Count (Commercial Vehicle Only)
		    'staff' => [
		    	[
			        'field' => 'object[staff_count]',
			        '_key' => 'staff_count',
			        'label' => 'Staff Count (Excluding Driver)',
			        'rules' => 'trim|required|integer|max_length[4]',
			        '_id' 		=> '_motor-staff-count',
			        '_type'     => 'text',
			        '_default' 	=> 0,
			        '_required' => false
			    ],
		    ],

		    // Trailer Section (Private and Commercial Vehicle Only)
		    'trailer' => [
		    	[
			        'field' => 'object[trailer_price]',
			        '_key' => 'trailer_price',
			        'label' => 'Trailer Price',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_id' 		=> '_motor-trailer-price',
			        '_type'     => 'text',
			        '_default' 	=> 0.00,
			        '_required' => false
			    ],
		    ],
		];

		if($portfolio_id == IQB_SUB_PORTFOLIO_MOTORCYCLE_ID)
		{
			$sections = ['vehicle-common', 'vehicle-registration', 'vehicle-custom', 'seating-capcity'];
		}
		else if($portfolio_id == IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID)
		{
			$sections = ['vehicle-common', 'vehicle-registration', 'vehicle-custom', 'seating-capcity', 'trailer'];
		}
		else
		{
			$sections = ['vehicle-cvc', 'vehicle-common', 'vehicle-registration', 'vehicle-custom', 'seating-capcity', 'carrying-capcity',  'staff', 'trailer'];
		}


		// return formatted?
		$fromatted_v_rules = [];
		$sectioned_v_rules = [];
		if($formatted === TRUE)
		{
			foreach ($sections as $section)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $v_rules[$section]);
			}
			return $fromatted_v_rules;
		}
		else
		{
			foreach ($sections as $section)
			{
				$sectioned_v_rules[$section] = $v_rules[$section];
			}
			return $sectioned_v_rules;
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_policy_package_dropdown'))
{
	/**
	 * Get Policy Packages - Motor
	 *
	 * Motor Policy Packages
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_policy_package_dropdown( $flag_blank_select = true)
	{
		$dropdown = [
			IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE  	=> 'Comprehensive',
			IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY 		=> 'Third Party',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - Motor Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_MOTOR_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$si_vehicle 		= $data['price_vehicle'] 		? floatval($data['price_vehicle']) : 0.00;
		$si_accessories 	= $data['price_accessories'] 	? floatval($data['price_accessories']) : 0.00;
		$si_trailer 		= isset($data['trailer_price']) ? floatval($data['trailer_price']) : 0.00;

		// Common Price for all three sub-portfolios
		$amt_sum_insured = $si_vehicle + $si_accessories;
		$si_breakdown = [
			'si_vehicle' 		=> $si_vehicle,
			'si_accessories'  	=> $si_accessories
		];

		// Add trailer price on Private Vehicle or Commercial Vehicle
		if($portfolio_id != IQB_SUB_PORTFOLIO_MOTORCYCLE_ID )
		{
			$amt_sum_insured += $si_trailer;

			$si_breakdown['si_trailer'] = $si_trailer;
		}

		$si_breakdown = json_encode($si_breakdown);

		// With SI Breakdown
		return ['amt_sum_insured' => $amt_sum_insured, 'si_breakdown' => $si_breakdown];

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_ownership_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Ownership Dropdown
	 *
	 * Motor ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_ownership_dropdown( $flag_blank_select = true )
	{
		$dropdown = [IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_GOVT => 'Government', IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT => 'Non-government'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_vehicle_status_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Ownership Dropdown
	 *
	 * Motor ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_vehicle_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['N' => 'New', 'O' => 'Old'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_sub_portfolio_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Sub Portfolio List
	 *
	 * Motor Sub-portfolio Dorpdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_sub_portfolio_dropdown( $flag_blank_select = true, $key="id")
	{
		$CI =& get_instance();
		$CI->load->model('portfolio_model');

		$dropdown = $CI->portfolio_model->dropdown_children(IQB_MASTER_PORTFOLIO_MOTOR_ID, $key);
		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_CVC_type_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Commercial Vehicle Dropdown
	 *
	 * Motor - Commercial Vehicle Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @param bool $sub_type Return Sub Type instead of primary type
	 * @return	bool
	 */
	function _OBJ_MOTOR_CVC_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL 	=> 'Goods Carrier - Truck', 	// GOODS CARRIER GENERAL
			IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER  	=> 'Goods Carrier - Tanker',	// GOODS CARRIER TANKER
			IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER  		=> 'Passenger Carrier', 		// PASSENGER CARRIER
			IQB_MOTOR_CVC_TYPE_TAXI 					=> 'Taxi',
			IQB_MOTOR_CVC_TYPE_TEMPO					=> 'Tempo (e-rikshaw, safa tempo, tricycle)',
			IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY 			=> 'Agriculture & Forestry Vehicle',
			IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER	=> 'Tractor & Power Triller',
			IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT	=> 'Construction Equipment Vehicle'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_ec_unit_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Engine Capacity Dropdown
	 *
	 * Motor Engine Capacity dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_ec_unit_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['CC' => 'Cubic Centimeter (CC)', 'HP' => 'Horse Power (HP)', 'KW' => 'Kilo Watt (KW)'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_ec_unit_tariff_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Engine Capacity Dropdown for Tariff Settings
	 *
	 * Motor Engine Capacity for Tariff Setting dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_ec_unit_tariff_dropdown( $flag_blank_select = true )
	{
		$dropdown = _OBJ_MOTOR_ec_unit_dropdown(FALSE) + _OBJ_MOTOR_carrying_unit_dropdown(FALSE);

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_carrying_unit_dropdown'))
{
	/**
	 * Get Policy Object - Motor - Carrying Capacity Dropdown
	 *
	 * Motor Carrying Capacity dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_MOTOR_carrying_unit_dropdown( $flag_blank_select = true )
	{
		$dropdown = ['T' => 'Ton', 'S' => 'Seat', 'NA' => 'Capacity Unknown'];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS FOR MOTOR PORTFOLIO
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MOTOR_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for Motor Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $tariff_record 	Portfolio Tariff Record
	 * @param bool $formatted 			Return Sectioned or Formatted
	 * @return array
	 */
	function _TXN_MOTOR_premium_validation_rules($policy_record, $pfs_record, $tariff_record, $formatted = false)
	{
		$validation_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 * This is the only required validation for Third Party
			 */
			'basic' => _ENDORSEMENT_premium_basic_v_rules( $policy_record->portfolio_id, $pfs_record ),

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => _POLICY_INSTALLMENT_validation_rules( $policy_record->portfolio_id, $pfs_record ),



			/**
			 * Third Party Validation Rules
			 * ----------------------------
			 * We only need stamp duty. The rest are auto computed from tariff.
			 */
			// 'third_party' => [
			// 	[
	  //               'field' => 'amt_stamp_duty',
	  //               'label' => 'Stamp Duty(Rs.)',
	  //               'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	  //               '_type'     => 'text',
	  //               '_default' 	=> $pfs_record->stamp_duty,
	  //               '_required' => true
	  //           ]
			// ],

			/**
			 * Comprehensive Common Validation Rules
			 * ---------------------------------------
			 * These rules apply to MCY/PVC/CVC
			 */
			'comprehensive_common' => [
				[
                    'field' => 'premium[dr_voluntary_excess]',
                    'label' => 'Voluntary Excess',
                    'rules' => 'trim|prep_decimal|decimal|max_length[5]',
                    '_key' 		=> 'dr_voluntary_excess',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess),
                    '_required' => false
                ],
                [
                    'field' => 'premium[no_claim_discount]',
                    'label' => 'No Claim Discount',
                    'rules' => 'trim|alpha_numeric|max_length[20]',
                    '_key' 		=> 'no_claim_discount',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount),
                    '_required' => false
                ],
                [
                    'field' => 'premium[flag_risk_pool]',
                    'label' => 'Pool Risk (जोखिम समूह बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_risk_pool',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_default' 			=> '1',
                    '_required' => false,
                    '_help_text' => '<small>This covers both (हुलदंगा, हडताल र द्वेशपूर्ण कार्य जोखिम बीमा) & (आतंककारी/विध्वंशात्मक कार्य जोखिम बीमा) </small>'
                ],
             //    [
	            //     'field' => 'amt_stamp_duty',
	            //     'label' => 'Stamp Duty(Rs.)',
	            //     'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	            //     '_type'     => 'text',
	            //     '_default' 	=> $pfs_record->stamp_duty,
	            //     '_required' => true
	            // ]
			],

			/**
			 * Comprehensive PVC Only Validation Rules
			 * ---------------------------------------
			 * These rules apply to Private Vehicle
			 */
			'comprehensive_pvc' => [

				// Commercial Use
				[
                    'field' => 'premium[flag_commercial_use]',
                    'label' => 'Commercial Use (निजी प्रयोजनको लागि भाडामा दिएको)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_commercial_use',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ],

                // Pay for Towing
				[
                    'field' => 'premium[flag_towing]',
                    'label' => 'Towing (दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_towing',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ]
			],

			/**
			 * Comprehensive CVC Only Validation Rules
			 * ---------------------------------------
			 * These rules apply to Commercial Vehicle
			 */
			'comprehensive_cvc' => [
				// Private Use
				[
                    'field' => 'premium[flag_private_use]',
                    'label' => 'Private Use',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_private_use',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
                    '_help_text' => '<small>* कार्यालय, पर्यटन र निजी प्रयोजनमा मात्र प्रयोग हुने सवारी साधनको तथा एम्बुलेन्स र शववाहनको ब्यापक बीमा गर्दा शरुु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।<br/>** निजी प्रयोेजनको लागि प्रयोग गर्ने सवारी साधन तथा दमकलको ब्यापक बीमा गर्दा शुरु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।</small>'
                ],

                // Pay for Towing
				[
                    'field' => 'premium[flag_towing]',
                    'label' => 'Towing (दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_towing',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ]
			]
		];

		/**
		 * Common validation rules for all type of policy package and portfolios
		 */
		$rules['basic'] 		= $validation_rules['basic'];
		$rules['installments'] 	= $validation_rules['installments'];
		if($policy_record->policy_package == IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Let's add premium validation fields
			 */
			$rules['premium'] = $validation_rules['comprehensive_common'];

			// Portfolio Specific Rules
			if( (int)$policy_record->portfolio_id === IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID )
			{
				// $rules['comprehensive_pvc'] = $validation_rules['comprehensive_pvc'];
				$rules['premium'] = array_merge($rules['premium'], $validation_rules['comprehensive_pvc']);
			}
			elseif( (int)$policy_record->portfolio_id === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID )
			{
				// $rules['comprehensive_cvc'] = $validation_rules['comprehensive_cvc'];
				$rules['premium'] = array_merge($rules['premium'], $validation_rules['comprehensive_cvc']);
			}

			// common to all
			// $rules['common_all'] = $validation_rules['common_all'];
		}

		/**
		 * Return Formatted or Sectioned
		 */
		if( !$formatted )
		{
			return $rules;
		}

		$v_rules = [];
		foreach($rules as $section=>$r)
		{
			$v_rules = array_merge($v_rules, $r);
		}
		return $v_rules;

	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MOTOR_premium_goodies'))
{
	/**
	 * Get Policy Endorsement Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 * 		2. Tariff Record if Applies
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 *
	 * @return	array
	 */
	function _TXN_MOTOR_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Object Attributes
		$attributes = json_decode($policy_object->attributes);

		// Tariff Configuration for this Portfolio
		$CI->load->model('tariff_motor_model');
		$CI->load->model('portfolio_setting_model'); // required as called from schedule

		$tariff_record = $CI->tariff_motor_model->get_single(
														$policy_record->fiscal_yr_id,
														$attributes->ownership,
														$policy_record->portfolio_id,
														$attributes->cvc_type ?? NULL
													);

		// Valid Tariff?
		$__flag_valid_tariff = TRUE;
		if( !$tariff_record )
		{
			$message 	= 'Tariff Configuration for this Portfolio is not found.';
			$title 		= 'Tariff Not Found!';
			$__flag_valid_tariff = FALSE;
		}
		else if( $tariff_record->active == IQB_STATUS_INACTIVE )
		{
			$message 	= 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
			$title 		= 'Tariff Not Active!';
			$__flag_valid_tariff = FALSE;
		}

		if( !$__flag_valid_tariff )
		{
			$message .= '<br/><br/>Portfolio: <strong>MOTOR</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MOTOR_premium_validation_rules( $policy_record, $pfs_record, $tariff_record );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MOTOR'))
{
	/**
	 * Motor Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  Policy Record
	 * @param object $endorsement_record 	 Endorsement Record
	 * @return json
	 */
	function __save_premium_MOTOR($policy_record, $endorsement_record)
	{
		$CI =& get_instance();

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $CI->input->post() )
		{

			/**
			 * Let's get the Required Records
			 */
			$policy_object 		= get_object_from_policy_record($policy_record);
			$premium_goodies 	= _TXN_MOTOR_premium_goodies($policy_record, $policy_object);

			$v_rules 			= $premium_goodies['validation_rules'];
			$tariff_record 		= $premium_goodies['tariff_record'];

			// Format Validation Rules
			$rules = [];
			foreach($v_rules as $section=>$r)
			{
				$rules = array_merge($rules, $r);
			}
            $CI->form_validation->set_rules($rules);
			if($CI->form_validation->run() === TRUE )
        	{

        		// Portfolio Settings Record For Given Fiscal Year and Portfolio
				$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

				// Premium Data
				$post_data = $CI->input->post();

				// Method to compute premium data
				$method = _PO_MOTOR_premium_compute_method($policy_record->portfolio_id);

				/**
				 * Do we have a valid method?
				 */
				if($method)
				{
					try{

						/**
						 * Get the Cost Reference Data
						 */
						return call_user_func($method, $policy_record, $endorsement_record, $tariff_record, $pfs_record, $post_data);


						// /**
						//  * Compute VAT ON Taxable Amount
						//  */
			   //      	$CI->load->helper('account');
				  //       $taxable_amount 		= $txn_data['amt_total_premium'] + $txn_data['amt_stamp_duty'];
				  //       $txn_data['amt_vat'] 	= ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);

						// $done 	  = $CI->endorsement_model->save($endorsement_record->id, $txn_data);

						// return $done;

					} catch (Exception $e){

						return $CI->template->json([
							'status' 	=> 'error',
							'message' 	=> $e->getMessage()
						], 404);
					}
				}
				else
				{
					return $CI->template->json([
						'status' 	=> 'error',
						'message' 	=> "No CRF computation method found for specified MOTOR portfolio!"
					], 404);
				}
        	}
        	else
        	{
        		return $CI->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Validation Error!',
					'message' 	=> validation_errors()
				]);
        	}
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MOTOR_vehicle_certificate_goodies'))
{
	/**
	 * Get list of goodies required for vehicle certificate
	 *
	 * @return	array
	 */
	function _OBJ_MOTOR_vehicle_certificate_goodies(  )
	{
		return [
			IQB_SUB_PORTFOLIO_MOTORCYCLE_ID => [
				'tp_human_damage' 	=> 'दुर्घटनाको कारणबाट मृत्यु भएमा प्रतिव्यक्ति रू ५,००,०००|०० तथा <br/>
									  दुर्घटनाको कारणबाट औषधोपचार खर्च प्रतिव्यक्ति रू ३,०००,००|००, तर <br/>
									  अधिकतम दायित्व प्रति घटना रू २५,००,०००|०० मात्र |',
				'tp_property_damage'=> 'अधिकतम दायित्व प्रति घटना रू २५,०००,०००,|०० मात्र |',
				'si_per_passenger' 	=> 'दुर्घटनाको कारणबाट मृत्यु भएमा रू ५,००,०००|०० दुई जनाको लागि मात्र |<br/>
										दुर्घटनाको कारणबाट औषधोपचार खर्च रू. ३,००,०००|०० दुई जनाको लागि मात्र मात्र |',
				'si_driver_other' 	=> '',
			],
			IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID => [
				'tp_human_damage' 	=> 'दुर्घटनाको कारणबाट मृत्यु भएमा प्रतिव्यक्ति रू ५,००,०००|०० तथा <br/>
									  दुर्घटनाको कारणबाट औषधोपचार खर्च प्रतिव्यक्ति रू ३,०००,००|००, तर <br/>
									  अधिकतम दायित्व प्रति घटना रू ८०,००,०००|०० मात्र |',
				'tp_property_damage'=> 'अधिकतम दायित्व प्रति घटना रू ८०,०००,०००,|०० मात्र |',
				'si_per_passenger' 	=> 'दुर्घटनाको कारणबाट मृत्यु भएमा रू ५,००,०००|०० सिट क्षमता बमोजिम मात्र |<br/>
										दुर्घटनाको कारणबाट औषधोपचार खर्च रू. ३,००,०००|०० सिट क्षमता बमोजिम मात्र |',
				'si_driver_other' 	=> '५,००,०००|०० बीमालेखमा जोखिम वहन गरे बमोजिम मात्र |',
			],
			IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID => [
				'tp_human_damage' 	=> 'दुर्घटनाको कारणबाट मृत्यु भएमा प्रतिव्यक्ति रू ५,००,०००|०० तथा <br/>
									  दुर्घटनाको कारणबाट औषधोपचार खर्च प्रतिव्यक्ति रू ३,०००,००|००, तर <br/>
									  अधिकतम दायित्व प्रति घटना रू ५०,००,०००|०० मात्र |',
				'tp_property_damage'=> 'अधिकतम दायित्व प्रति घटना रू ५०,०००,०००,|०० मात्र |',
				'si_per_passenger' 	=> 'रू ५,००,०००|०० सिट क्षमता तथा बीमालेखमा जोखिम वहन गरे बमोजिम मात्र |',
				'si_driver_other' 	=> '५,००,०००|०० बीमालेखमा जोखिम वहन गरे बमोजिम मात्र |',
			]
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_PO_MOTOR_get_compulsory_excess'))
{
	/**
	 * Get "Compulsory Excess" For Given Vehicle
	 *
	 * @param object $tariff_record  for given motor
	 * @param int $year_mfd  Year Manufactured of the Vehicle
	 * @return	object Compulsory Excess Record
	 */
	function _PO_MOTOR_get_compulsory_excess( $tariff_record, $year_mfd)
	{
		$record = NULL;
		if($tariff_record->pramt_compulsory_excess)
		{
			$pramt_compulsory_excess = json_decode($tariff_record->pramt_compulsory_excess);

			$year_now = date('Y');
			$diff = $year_now - $year_mfd;

			foreach($pramt_compulsory_excess as $single)
			{
				if($single->min_age <= $diff && $single->max_age >= $diff)
				{
					$record = $single;
					break;
				}
			}
		}
		else
		{
			throw new Exception("Exception [Helper: ph_motor_helper][Method: _PO_MOTOR_get_compulsory_excess()]: No Compulsory Excess found on Tariff for this PORTFOLIO!");
		}

		return $record;
	}
}