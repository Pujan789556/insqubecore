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
// DROPDOWN HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_PORTFOLIO_MOTOR_voluntary_excess_dropdown'))
{
	/**
	 * Get "Voluntary Excess" Dropdown for Given Portfolio Tariff
	 *
	 * @param JSON $dr_voluntary_excess  Tariff Record's "Voluntary Excess" JSON
	 * @return	array
	 */
	function _PORTFOLIO_MOTOR_voluntary_excess_dropdown( $dr_voluntary_excess, $flag_blank_select = true, $prefix = 'Rs. ' )
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


if ( ! function_exists('_PORTFOLIO_MOTOR_no_claim_discount_dropdown'))
{
	/**
	 * Get "No Claim Discount" Dropdown for Given Portfolio Tariff
	 *
	 * @param JSON $no_claim_discount  Tariff Record's "No Claim Discount" JSON
	 * @return	array
	 */
	function _PORTFOLIO_MOTOR_no_claim_discount_dropdown( $no_claim_discount, $flag_blank_select = true, $suffix = ' years' )
	{
		$dropdown = [];
		$no_claim_discount = $no_claim_discount ? json_decode($no_claim_discount) : NULL;
		if($no_claim_discount )
		{
			foreach ($no_claim_discount as $r)
			{
				$dropdown[$r->rate] =  $r->years . $suffix;
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
// COMPUTATIONAL TABLE
// ------------------------------------------------------------------------

if ( ! function_exists('_PORTFOLIO_MOTOR_MCY_cost_table'))
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
	function _PORTFOLIO_MOTOR_MCY_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data  )
	{
		$cost_table = [];
		$attributes = json_decode($policy_object->attributes);

		// Form Posted Data - Extra Fields Required to Perform Cost Calculation
		$post_data_extra_fields = $data['extra_fields'] ?? NULL;

		// Tariff Extracts
		$default_tariff = json_decode($tariff_record->tariff);

		// Vehicle Price
		$vehicle_price 				= $attributes->price_vehicle;
		$vehicle_price_accessories 	= $attributes->price_accessories;
		$vehicle_total_price  		= $vehicle_price + $vehicle_price_accessories;

		// Vehicle Age
		$vehicle_registration_date 	= new DateTime($attributes->reg_date);
		$today 						= new DateTime(date('Y-m-d'));
		$interval 					= $vehicle_registration_date->diff($today);
		$vehicle_age_in_yrs 		= $interval->y;

		//
		// Defaults: Thirdparty Premium, Default Rate, Vehicle Over Age Rate
		//
		$premiumm_third_party 	= 0.00;
		$default_rate 			= 0.00;
		$vehicle_over_age_rate 	= 0.00;
        foreach ($default_tariff as $t)
        {
            if( $attributes->engine_capacity >= $t->ec_min && $attributes->engine_capacity <= $t->ec_max )
            {
                $premiumm_third_party = $t->third_party;
                $default_rate = $t->rate->rate;

                if( $t->age->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age1_max )
				{
					$vehicle_over_age_rate = $t->age->rate1;
				}
				else if( $t->age->age2_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age2_max )
				{
					$vehicle_over_age_rate = $t->age->rate2;
				}
                break;
            }
        }

        // No Claim Discount - Years & Rate
        $no_claim_discount 		= $post_data_extra_fields['no_claim_discount'] ?? 0;
        $no_claim_discount 		= $no_claim_discount ? $no_claim_discount : 0;
		$year_no_claim_discount = $no_claim_discount ? _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, false, '')[$no_claim_discount] : 0;

        // Rsik Group
        $tariff_rsik_group = json_decode($tariff_record->riks_group);

        /**
         * Package Comprehensive
         */
        $premium_A_total 	= 0.00;
        $premium_AA_total 	= 0.00;
        $premium_I_total 	= 0.00;
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Defaults (अ)
			 */
			$__cost_table_A = [
				'column_head' => 'अ',
				'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत',
				'title_en' 	=> 'Insurance against vehicle damage/loss & accident insurance amounted to'
			];

			// Default Premium =  X% of Vehicle Price
			$__premium_A_row_1 =  $vehicle_total_price * ($default_rate/100.00);


			// Additional cost due to vehicle over aging
			$__premium_A_row_2 = 0.00;
			if( $vehicle_over_age_rate > 0.00 )
			{
				$__premium_A_row_2 = $__premium_A_row_1 * ($vehicle_over_age_rate/100.00);
			}

			$__cost_table_A['sections'][] = [
				'label' 	=> 'क',
				'title' 	=> "सि.सि.तथा घोषित मूल्य (सरसामान सहित) अनुसार बीमा शुल्क (घोषित मूल्यको {$default_rate}%)",
				'amount' 	=> $__premium_A_row_1
			];
			$__cost_table_A['sections'][] = [
				'title' 	=> "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को {$vehicle_over_age_rate}%",
				'amount' 	=> $__premium_A_row_2
			];

			// Sub Total : ख
			$__premium_A_row_3 = $__premium_A_row_1 + $__premium_A_row_2;
			$__cost_table_A['sections'][] = [
				'label' 	=> 'ख',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_3
			];


			// Discount on Voluntary Excess : discount_KHA
			$__premium_A_row_4 				= 0.00;
			$dr_voluntary_excess 		= $post_data_extra_fields['dr_voluntary_excess'] ?? 0.00;
			$amount_voluntary_excess 	= $dr_voluntary_excess ? _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess] : 0.00;

			if($dr_voluntary_excess)
			{
				$__premium_A_row_4 = $__premium_A_row_3 * ($dr_voluntary_excess/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' 	=> "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
				'amount' 	=> $__premium_A_row_4
			];


			// Sub Total : ग
			$__premium_A_row_5 = $__premium_A_row_3 - $__premium_A_row_4;
			$__cost_table_A['sections'][] = [
				'label' 	=> 'ग',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_5
			];


			// No Claim Discount : discount_GA
			$__premium_A_row_6 			= 0.00;
			if($no_claim_discount)
			{
				$__premium_A_row_6 = $__premium_A_row_5 * ($no_claim_discount/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' 	=> "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “ग” को $no_claim_discount %",
				'amount' 	=> $__premium_A_row_6
			];


			// Sub Total : घ
			$__agent_comissionable_amount = $__premium_A_row_7 = $__premium_A_row_5 - $__premium_A_row_6;
			$__cost_table_A['sections'][] = [
				'label' 	=> 'घ',
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_7
			];


			//
			// Agent Commission/Direct Discount? -> Applies only Non-GOVT
			//
			$__premium_A_row_8 = 0.00;
			if( $policy_record->flag_dc === 'D' && $attributes->ownership === IQB_PORTFOLIO_OWNERSHIP_NON_GOVT )
			{
				// X% of GHA
				$__premium_A_row_8 = $__premium_A_row_7 * ($pfs_record->direct_discount/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' 	=> "प्रत्यक्ष बीमा वापत छूटः “घ” को {$pfs_record->direct_discount} %",
				'amount' 	=> $__premium_A_row_8
			];

			// अ Total
			$premium_A_total = $__premium_A_row_7  - $__premium_A_row_8;
			$__cost_table_A['sections'][] = [
				'title' 		=> "जम्मा",
				'amount' 		=> $premium_A_total,
				'section_total' => true
			];

			$__cost_table_A['sub_total'] = $premium_A_total;

			// ---------------------------------------------------------------------------------------


			/**
			 * Risk Pool (इ)
			 */
			$__cost_table_I = [
				'column_head' => 'इ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];
			$flag_risk_mob = $post_data_extra_fields['flag_risk_mob'] ?? NULL;
			$flag_risk_terorrism = $post_data_extra_fields['flag_risk_terorrism'] ?? NULL;

			$premium_risk_mob = 0.00;
			if($flag_risk_mob)
			{
				$premium_risk_mob = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
			}
			$__cost_table_I['sections'][] = [
				'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
				'amount' => $premium_risk_mob
			];

			$premium_risk_terorrism = 0.00;
			$premium_for_insured_covered_on_terorrism = 0.00;
			if($flag_risk_terorrism)
			{
				$premium_risk_terorrism = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);
				$__cost_table_I['sections'][] = [
					'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
					'amount' => $premium_risk_terorrism
				];
				// Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

				// Driver Covered
				// Passenger Cover
				// Driver Count  = 1
				// Passenger Count = Seat Capacity - 1
				// Tariff Rate: rate_additionl_per_thousand_on_extra_rate


				$insured_value_tariff = json_decode($tariff_record->insured_value_tariff);

				$no_of_seat = $attributes->carrying_capacity;
				$passenger_count = $no_of_seat - 1;

				// Driver Premium
				$premium_driver = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

				// Passenger Premium
				$premium_passenger = $passenger_count * (($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);

				$premium_for_insured_covered_on_terorrism = $premium_driver + $premium_passenger;

				$__cost_table_I['sections'][] = [
					'title' => "(ग) चालक तथा पछाडि बस्ने व्यक्तिको आतंकबाद वापत {$tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate} प्रति हजारका दरले ",
					'amount' => $premium_for_insured_covered_on_terorrism
				];
			}
			// इ TOTAL
			$premium_I_total = $premium_risk_mob + $premium_risk_terorrism + $premium_for_insured_covered_on_terorrism;

			$__cost_table_I['sections'][] = [
				'title' 	=> 'जम्मा', // Subtotal
				'amount' 	=> $premium_I_total,
				'section_total' => true
			];

			$__cost_table_I['sub_total'] = $premium_I_total;
		}



		/**
		 * Third Party (आ)
		 */
		$amount_noClaimDiscount_on_thirdParty = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount != 0)
		{
			$amount_noClaimDiscount_on_thirdParty = $premiumm_third_party * ($no_claim_discount/100.00);
		}

		// आ TOTAL
		$premium_AA_total = $premiumm_third_party - $amount_noClaimDiscount_on_thirdParty;

		// ---------------------------------------------------------------------------------------


		//
		// Disabled Friendly Discount
		//
		$vehile_flag_mcy_df = $attributes->flag_mcy_df ?? FALSE;
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
		$total_amount = $premium_total_A_AA  + $premium_I_total;


		//
		// Stamp Duty
		//
		$stamp_duty = $data['stamp_duty'];


		/**
		 * Cost Table: Third Party Only
		 */
		$__cost_table_AA = [
			'column_head' => 'आ',
			'title_np' 	=> 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
			'title_en' 	=> 'Third party liability insurance amounted to',
			'sections' 	=> [
				// Cost according to CC
				[
					'title' 	=> "सि. सि. अनुसारको बीमाशुल्क",
					'amount' 	=> $premiumm_third_party,
					'label' 	=> 'ङ'
				]
			]
		];

		// Noclaim Dicount Only if Comprehensive
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			$__cost_table_AA['sections'][] = [
				'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “ङ” को $no_claim_discount %",
				'amount' => $amount_noClaimDiscount_on_thirdParty
			];
		}

		// Third Party : Sub Total
		$__cost_table_AA['sections'][] = [
			'title' 	=> 'जम्मा', // Subtotal
			'amount' 	=> $premium_AA_total,
			'section_total' => true
		];

		$__cost_table_AA['sub_total'] = $premium_AA_total;


		/**
		 * Disabled Friendly Discount
		 */
		$__cost_table_flag_mcy_df = [];
		if($vehile_flag_mcy_df)
		{
			$__cost_table_flag_mcy_df = [
				'column_head' => '*',
				'title_np' 	=> 'अपाङ्ग मैत्री छुट',
				'title_en' 	=> 'Disabled-friendly discount',
				'sections' => [
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

		$__cost_table = [
			'total_amount'  => $total_amount,
			'stamp_duty' 	=> $stamp_duty,
			'extra_fields' 	=> $post_data_extra_fields ? json_encode($post_data_extra_fields) : NULL
		];

		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
		{
			$__cost_table['attributes'] = json_encode(array_filter([$__cost_table_AA, $__cost_table_flag_mcy_df]));
		}
		else
		{
			$__cost_table_comprehensive = array_filter([
				// Comprehensive
				$__cost_table_A,

				// Third Party
				$__cost_table_AA,

				// Discount Disabled Friendly
				$__cost_table_flag_mcy_df,

				// Pool Risk
				$__cost_table_I
			]);

			$__cost_table['attributes'] = json_encode($__cost_table_comprehensive);

			/**
			 * Agent Commission?
			 * ------------------
			 *
			 * Applies only if the vehicle is non-govt and no direct discount
			 */
			if($policy_record->flag_dc === 'C' && $attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
			{
				$__cost_table['comission_amount'] = $__agent_comissionable_amount;
			}
		}

		return $__cost_table;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_PORTFOLIO_MOTOR_PVC_cost_table'))
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
	function _PORTFOLIO_MOTOR_PVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data  )
	{
		$cost_table = [];
		$attributes = json_decode($policy_object->attributes);

		// Form Posted Data - Extra Fields Required to Perform Cost Calculation
		$post_data_extra_fields = $data['extra_fields'] ?? NULL;


		// Tariff Extracts
		$default_tariff = json_decode($tariff_record->tariff);

		// Trolly Tariff
		$trolly_tariff = $tariff_record->trolly_tariff ? json_decode($tariff_record->trolly_tariff) : NULL;

		// Vehicle Price
		$vehicle_price 				= (float)$attributes->price_vehicle;
		$vehicle_price_accessories 	= (float)$attributes->price_accessories;
		$vehicle_total_price  		= $vehicle_price + $vehicle_price_accessories;

		// Trolly/Trailer Price
		$trailer_price 				= $attributes->trailer_price ? (float)$attributes->trailer_price : 0.00;

		// Vehicle Age
		$vehicle_registration_date 	= new DateTime($attributes->reg_date);
		$today 						= new DateTime(date('Y-m-d'));
		$interval 					= $vehicle_registration_date->diff($today);
		$vehicle_age_in_yrs 		= $interval->y;

		//
		// Tariff Rate Defaults for Given Engine Capacity
		//  	To Minus Amount, Base Fragment, Base Fragment Rate, Rest Fragment Rate, Third Party Rate, Vehicle Over Age Rate
		// 		Example: First 20 lakhs 1.24% + Rest Amount's 1.6% - Rs. 3000
		//
		$minus_amount_according_to_cc 	= 0.00;
		$base_fragment 					= 0.00;
		$base_fragment_rate 			= 0.00;
		$rest_fragment_rate 			= 0.00;
		$premiumm_third_party 			= 0.00;
		$vehicle_over_age_rate 			= 0.00;

        foreach ($default_tariff as $t)
        {
        	// Should also match Engine Capacity Type
            if( $attributes->engine_capacity >= $t->ec_min && $attributes->engine_capacity <= $t->ec_max && $attributes->ec_unit === $t->ec_type )
            {
            	$minus_amount_according_to_cc 			= (float)$t->rate->minus_amount;
                $base_fragment 							= (float)$t->rate->base_fragment;
                $base_fragment_rate 					= (float)$t->rate->base_fragment_rate;
                $rest_fragment_rate 					= (float)$t->rate->rest_fragment_rate;
                $premiumm_third_party 					= (float)$t->third_party;

                if( $t->age->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age1_max )
				{
					$vehicle_over_age_rate = (float)$t->age->rate1;
				}
				else if( $t->age->age2_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age2_max )
				{
					$vehicle_over_age_rate = (float)$t->age->rate2;
				}
                break;
            }
        }

        // No Claim Discount - Years & Rate
        $no_claim_discount 		= $post_data_extra_fields['no_claim_discount'] ?? 0;
        $no_claim_discount 		= $no_claim_discount ? $no_claim_discount : 0;
		$year_no_claim_discount = $no_claim_discount ? _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, false, '')[$no_claim_discount] : 0;

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
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			/**
			 * Defaults (अ)
			 */
			$__cost_table_A = [
				'column_head' => 'अ',
				'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
				'title_en' 	=> 'Insurance against vehicle damage/loss',
			];


			// Base Premium
			$__premium_A_row_1 = 0.00;
			$__premium_A_row_2 = 0.00;
			if($vehicle_total_price > $base_fragment ) // if Vehicle Price > 20 Lakhs
			{
				$__premium_A_row_1 = $base_fragment * ($base_fragment_rate / 100.00);
				$__premium_A_row_2 = ( $vehicle_total_price -  $base_fragment ) * ($rest_fragment_rate/100.00);
			}
			else
			{
				$__premium_A_row_1 = $vehicle_total_price * ($base_fragment_rate / 100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' 	=> "सि.सि.अनुसार घोषित मूल्य मध्ये पहिलो बीस लाखसम्मको बीमाशुल्क (घोषित मूल्यको {$base_fragment_rate}%)",
				'amount' 	=> $__premium_A_row_1
			];
			$__cost_table_A['sections'][] = [
				'title' 	=> "बाँकी घोषित मूल्यको बीमा शुल्क (बाँकी घोषित मूल्यको {$rest_fragment_rate}%)",
				'amount' 	=> $__premium_A_row_2
			];


			// Trailer/Trolly Premium
			$__premium_A_row_3 = 0.00;
			if( $trailer_price )
			{
				$__premium_A_row_3 = ( $trailer_price * ($trolly_tariff->rate/100.00) ) - $trolly_tariff->minus_amount;
			}
			$__cost_table_A['sections'][] = [
				'title' 	=> "ट्रेलर (मालसामान ओसार्ने) वापतको बीमाशुल्क ( घोषित मूल्यको{$trolly_tariff->rate}% - रु. {$trolly_tariff->minus_amount})का दरले",
				'amount' 	=> $__premium_A_row_3
			];


			// Discount according to CC
			$__premium_A_row_4 = $minus_amount_according_to_cc;
			$__cost_table_A['sections'][] = [
				'title' 	=> "सि.सि. अनुसार बीमाशुल्कमा छूट",
				'amount' 	=> $__premium_A_row_4
			];


			// क Sub Total
			$__premium_A_row_KA = $__premium_A_row_5 = $__premium_A_row_1 + $__premium_A_row_2 + $__premium_A_row_3 - $__premium_A_row_4;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_5,
				'label' 	=> 'क'
			];


			// Old Age Premium
			$__premium_A_row_6 = 0.00;
			if( $vehicle_over_age_rate > 0.00 )
			{
				$__premium_A_row_6 = $__premium_A_row_KA * ($vehicle_over_age_rate/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को {$vehicle_over_age_rate}%",
				'amount' => $__premium_A_row_6
			];


			// ख Sub Total
			$__premium_A_row_KHA = $__premium_A_row_7 = $__premium_A_row_KA + $__premium_A_row_6;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_KHA,
				'label' 	=> 'ख'
			];


			// Private on Hire (Commercial Use)
			$flag_commercial_use = $post_data_extra_fields['flag_commercial_use'] ?? FALSE;
			$__premium_A_row_8 = 0.00;
			if( $flag_commercial_use )
			{
				$__premium_A_row_8 = $__premium_A_row_KHA * ($tariff_record->rate_pvc_on_hire/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "निजी प्रयोजनको लागि भाडामा दिए वापत थप: “ख” को {$tariff_record->rate_pvc_on_hire}%",
				'amount' => $__premium_A_row_8
			];


			// ग Sub Total
			$__premium_A_row_GA = $__premium_A_row_9 = $__premium_A_row_KHA + $__premium_A_row_8;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_GA,
				'label' 	=> 'ग'
			];


			// Discount on Voluntary Excess - GA ko X%
			$dr_voluntary_excess 		= $post_data_extra_fields['dr_voluntary_excess'] ?? FALSE;
			$amount_voluntary_excess 	= $dr_voluntary_excess
											? _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess]
											: 0.00;
			$__premium_A_row_10 		= 0.00;
			if( $dr_voluntary_excess )
			{
				$__premium_A_row_10 = $__premium_A_row_GA * ($dr_voluntary_excess/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ग” को {$dr_voluntary_excess} %",
				'amount' => $__premium_A_row_10
			];


			// घ Sub Total
			$__premium_A_row_GHA = $__premium_A_row_11 = $__premium_A_row_GA - $__premium_A_row_10;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_GHA,
				'label' 	=> 'घ'
			];


			// NO Claim Discount
			$__premium_A_row_12 = 0.00;
			if($no_claim_discount)
			{
				$__premium_A_row_12 = $__premium_A_row_GHA * ($no_claim_discount/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “घ” को {$no_claim_discount} %",
				'amount' => $__premium_A_row_12
			];


			// ङ Sub Total
			$__agent_comissionable_amount = $__premium_A_row_NGA = $__premium_A_row_13 = $__premium_A_row_GHA - $__premium_A_row_12;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_NGA,
				'label' 	=> 'ङ'
			];


			//
			// Agent Commission/Direct Discount? -> Applies only Non-GOVT
			//
			$__premium_A_row_14 = 0.00;
			if( $policy_record->flag_dc === 'D' && $attributes->ownership === IQB_PORTFOLIO_OWNERSHIP_NON_GOVT )
			{
				// X% of ङ
				$__premium_A_row_14 = $__premium_A_row_NGA * ($pfs_record->direct_discount/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "प्रत्यक्ष बीमा वापत छुटः “ङ” को {$pfs_record->direct_discount} %",
				'amount' => $__premium_A_row_14
			];

			// च Sub Total
			$__premium_A_row_CHA = $__premium_A_row_15 = $__premium_A_row_NGA - $__premium_A_row_14;
			$__cost_table_A['sections'][] = [
				'title' 	=> "",
				'amount' 	=> $__premium_A_row_CHA,
				'label' 	=> 'च'
			];


			// Towing
			$flag_towing = $post_data_extra_fields['flag_towing'] ?? FALSE;
			$__premium_A_row_16 = 0.00;
			if( $flag_towing )
			{
				$__premium_A_row_16 = $tariff_record->pramt_towing ?? 0.00;
			}
			$__cost_table_A['sections'][] = [
				'title' => "दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा वापत बीमा शुल्क थप",
				'amount' => $__premium_A_row_16
			];


			// Section Sub Total (अ Total)
			$premium_A_total  = $__premium_A_row_17 = $__premium_A_row_CHA + $__premium_A_row_16;
			$__cost_table_A['sections'][] = [
				'title' 	=> "जम्मा",
				'amount' 	=> $premium_A_total,
				'section_total' => true
			];

			// Section Sub Total
			$__cost_table_A['sub_total'] = $premium_A_total;
		}

			// ---------------------------------------------------------------------------------------
			//  Driver/Insured Accident
			//
			// 	This applies to both thirdparty and comprehensive
			// ---------------------------------------------------------------------------------------

			/**
			 * Driver Accident Insurance (इ)
			 */
			$accident_premium = $tariff_record->accident_premium ? json_decode($tariff_record->accident_premium) : NULL;
			$insured_value_tariff = $tariff_record->insured_value_tariff ? json_decode($tariff_record->insured_value_tariff) : NULL;
			$__cost_table_I = [
				'column_head' => 'इ',
				'title_np' 	=> 'चालकको दुर्घटना बीमा वापत',
				'title_en' 	=> 'Driver Accident Insurance'
			];

			$premium_I_total =  $accident_premium->pramt_driver_accident;
			$__cost_table_I['sections'][] = [
				'title' => "चालक (बीमांक रु. {$insured_value_tariff->driver}  को रु. {$accident_premium->pramt_driver_accident} का दरले)",
				'amount' => $premium_I_total
			];

			// Section Sub Total
			$__cost_table_I['sub_total'] = $premium_I_total;

			// ---------------------------------------------------------------------------------------


			/**
			 * Insured Party/Passenger Accident Insurance (ई)
			 */
			$__cost_table_EE = [
				'column_head' => 'ई',
				'title_np' 	=> 'बीमित तथा यात्रीको दुर्घटना बीमा वापत',
				'title_en' 	=> 'Insured Party & Passenger Accident Insurance'
			];

			$premium_EE_total =  ($attributes->carrying_capacity - 1) * $accident_premium->pramt_accident_per_passenger;
			$__cost_table_EE['sections'][] = [
				'title' => "प्रति व्यक्ति बीमांक रु. {$insured_value_tariff->passenger} को लागी बीमाशुल्क प्रति सिट रु. {$accident_premium->pramt_accident_per_passenger} का दरले",
				'amount' => $premium_EE_total
			];

			// Section Sub Total
			$__cost_table_EE['sub_total'] = $premium_EE_total;

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
			$__cost_table_U = [
				'column_head' => 'उ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];

			$flag_risk_mob = $post_data_extra_fields['flag_risk_mob'] ?? NULL;
			$flag_risk_terorrism = $post_data_extra_fields['flag_risk_terorrism'] ?? NULL;


			// Mob/Strike
			$__premium_U_row_1 = 0.00;
			if($flag_risk_mob)
			{
				$__premium_U_row_1 = (float)($vehicle_total_price + $trailer_price) * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
			}
			$__cost_table_U['sections'][] = [
				'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
				'amount' => $__premium_U_row_1
			];

			// Terorrism
			$__premium_U_row_2 = 0.00;
			$__premium_U_row_3 = 0.00;
			$__premium_U_row_4 = 0.00;
			$premium_for_insured_covered_on_terorrism = 0.00;
			if($flag_risk_terorrism)
			{
				$__premium_U_row_2 = (float)($vehicle_total_price + $trailer_price) * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);

				// Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

				// Driver Covered
				// Passenger Cover
				// Driver Count  = 1
				// Passenger Count = Seat Capacity - 1
				// Tariff Rate: rate_additionl_per_thousand_on_extra_rate


				$insured_value_tariff = json_decode($tariff_record->insured_value_tariff);

				$no_of_seat = $attributes->carrying_capacity;
				$passenger_count = $no_of_seat - 1;

				// Driver Premium
				$__premium_U_row_3 = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

				// Passenger Premium
				$__premium_U_row_4 = $passenger_count * (($insured_value_tariff->passenger/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);
			}

			// उ TOTAL
			$premium_U_total = $__premium_U_row_1 + $__premium_U_row_2 + $__premium_U_row_3 + $__premium_U_row_4;

			$__cost_table_U['sections'][] = [
				'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
				'amount' => $__premium_U_row_2
			];
			$__cost_table_U['sections'][] = [
				'title' => "(ग) चालक",
				'amount' => $__premium_U_row_3
			];
			$__cost_table_U['sections'][] = [
				'title' => "(घ) बीमित तथा यात्री",
				'amount' => $__premium_U_row_4
			];

			$__cost_table_U['sections'][] = [
				'title' 	=> 'जम्मा', // Subtotal
				'amount' 	=> $premium_U_total,
				'section_total' => true
			];

			// Section Sub Total
			$__cost_table_U['sub_total'] = $premium_U_total;
		}


		// ---------------------------------------------------------------------------------------

		/**
		 * Third Party (आ)
		 */
		$__cost_table_AA = [
			'column_head' => 'आ',
			'title_np' 	=> 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
			'title_en' 	=> 'Third party liability insurance amounted to'
		];
		$__premium_AA_row_1 = $premiumm_third_party;
		$__premium_AA_row_2 = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount != 0)
		{
			$__premium_AA_row_2 = $__premium_AA_row_1 * ($no_claim_discount/100.00);
		}
		$__cost_table_AA['sections'][] = [
			'title' => "सि. सि. अनुसारको बीमाशुल्क",
			'amount' => $__premium_AA_row_1
		];

		// No claim Dicount Only if Comprehensive
		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
		{
			$__cost_table_AA['sections'][] = [
				'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “छ” को $no_claim_discount %",
				'amount' => $__premium_AA_row_2
			];
		}

		// Third Party : Sub Total (छ Total)
		$premium_AA_total = $__premium_AA_row_1 - $__premium_AA_row_2;
		$__cost_table_AA['sections'][] = [
			'title' 	=> 'जम्मा', // Subtotal
			'amount' 	=> $premium_AA_total,
			'section_total' => true
		];

		// Section Sub Total
		$__cost_table_AA['sub_total'] = $premium_AA_total;

		// ---------------------------------------------------------------------------------------



		//
		// Grand Total
		//
		$total_amount = $premium_A_total + $premium_AA_total  + $premium_I_total + $premium_EE_total + $premium_U_total;


		//
		// Stamp Duty
		//
		$stamp_duty = $data['stamp_duty'];



		$__cost_table = [
			'total_amount'  => $total_amount,
			'stamp_duty' 	=> $stamp_duty,
			'extra_fields' 	=> $post_data_extra_fields ? json_encode($post_data_extra_fields) : NULL
		];

		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
		{
			$__cost_table['attributes'] = json_encode( [$__cost_table_AA, $__cost_table_I, $__cost_table_EE ] );
		}
		else
		{

			$__cost_table_comprehensive = array_filter([

				// Comprehensive
				$__cost_table_A,

				// Third Party
				$__cost_table_AA,

				// Driver Accident
				$__cost_table_I,

				// Passenger Accident
				$__cost_table_EE,

				// Risk Group
				$__cost_table_U
			]);

			$__cost_table['attributes'] = json_encode($__cost_table_comprehensive);


			/**
			 * Agent Commission?
			 * ------------------
			 *
			 * Applies only if the vehicle is non-govt and no direct discount
			 */
			if($policy_record->flag_dc === 'C' && $attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
			{
				$__cost_table['comission_amount'] = $__agent_comissionable_amount;
			}
		}

		return $__cost_table;
	}
}

// -----------------------------------------------------------------------

if ( ! function_exists('_PORTFOLIO_MOTOR_CVC_cost_table'))
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
    function _PORTFOLIO_MOTOR_CVC_cost_table( $policy_record, $policy_object, $tariff_record, $pfs_record, $data  )
    {
        $object_attributes = json_decode($policy_object->attributes);

        // Form Posted Data - Extra Fields Required to Perform Cost Calculation
        $post_data_extra_fields = $data['extra_fields'] ?? NULL;


        // Tariff Extracts
        $default_tariff = json_decode($tariff_record->tariff);

        // Trolly Tariff
        $trolly_tariff = $tariff_record->trolly_tariff ? json_decode($tariff_record->trolly_tariff) : NULL;

        // Vehicle Price
        $vehicle_price              = (float)$object_attributes->price_vehicle;
        $vehicle_price_accessories  = (float)$object_attributes->price_accessories;
        $vehicle_total_price        = $vehicle_price + $vehicle_price_accessories;

        // Trolly/Trailer Price
        $trailer_price              = $object_attributes->trailer_price ? (float)$object_attributes->trailer_price : 0.00;

        // Vehicle Age
        $vehicle_registration_date  = new DateTime($object_attributes->reg_date);
        $today                      = new DateTime(date('Y-m-d'));
        $interval                   = $vehicle_registration_date->diff($today);
        $vehicle_age_in_yrs         = $interval->y;

        //
        // Tariff Rate Defaults for Given Engine Capacity
        //      To Minus Amount, Base Fragment, Base Fragment Rate, Rest Fragment Rate, Third Party Rate, Vehicle Over Age Rate
        //      Example: First 20 lakhs 1.24% + Rest Amount's 1.6% - Rs. 3000
        //
        $primary_tariff_vehicle = _PORTFOLIO_MOTOR_CVC_primary_tariff_vehicle($object_attributes, $default_tariff, $vehicle_age_in_yrs);

        // No Claim Discount - Years & Rate
        $no_claim_discount      = $post_data_extra_fields['no_claim_discount'] ?? 0;
        $no_claim_discount      = $no_claim_discount ? $no_claim_discount : 0;
        $year_no_claim_discount = $no_claim_discount ? _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, false, '')[$no_claim_discount] : 0;

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
        $premium_OO_total = 0.00;
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {
            /**
             * Defaults (अ)
             */
            $__cost_table_A = [
                'column_head' => 'अ',
                'title_np'  => 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा वापत',
                'title_en'  => 'Insurance against vehicle damage/loss',
            ];

            // Statement 1 is common for all
            $statement_1 = "घोषित मूल्य (सरसामान सहित) अनुसार शुरु बीमा शुल्क (घोषित मूल्यको " . $primary_tariff_vehicle['rate'] . " %)";

            // Common Row 1 - Ghosit Mulya ko X %
            $__premium_A_row_1 = $vehicle_total_price * ($primary_tariff_vehicle['rate'] / 100.00);
            $__cost_table_A['sections'][] = [
                'title'     => $statement_1,
                'amount'    => $__premium_A_row_1
            ];

            // Trailer Bapat ko Sulka ( Trailer ko ghosit mulya ko X %)
            $statement_trailer = "ट्रेलर (मालसामान ओसार्ने) वापतको बीमाशुल्क ( घोषित मूल्यको{$trolly_tariff->rate}% - रु. {$trolly_tariff->minus_amount})का दरले";
            $__premium_A_row_trailer = 0.00;
            if( $trailer_price )
            {
                  $__premium_A_row_trailer = ( $trailer_price * ($trolly_tariff->rate/100.00) ) - $trolly_tariff->minus_amount;
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
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Bharbahan xamata anusar suru bima sulka ma xut
                    $__premium_A_row_3 = $primary_tariff_vehicle['minus_amount'];
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_3,
                        'amount'    => $__premium_A_row_3
                    ];

                    // Trailer Premium
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_2 - $__premium_A_row_3 + $__premium_A_row_trailer;;
                    $__cost_table_A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER:
                case IQB_MOTOR_CVC_TYPE_TEMPO:
                    // Trailer Premium
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_trailer;;
                    $__cost_table_A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER:
                    // Seat xamata anusar suru bimasulka ma xut
                    $statement_2        = "सिट क्षमता अनुसार शुरु बीमाशुल्कमा छूट";
                    $__premium_A_row_2  = $primary_tariff_vehicle['minus_amount'];
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Trailer Premium
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_trailer,
                        'amount'    => $__premium_A_row_trailer
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 - $__premium_A_row_2 + $__premium_A_row_trailer;
                    $__cost_table_A['sections'][] = [
                        'title'     => "",
                        'amount'    => $__premium_A_row_KA,
                        'label'     => 'क'
                    ];
                    break;

                case IQB_MOTOR_CVC_TYPE_TAXI:
                    $statement_2 = "सि.सि.अनुसार शुरु बीमा शुल्कमा थप";
                    $__premium_A_row_2 = $primary_tariff_vehicle['plus_amount'];
                    $__cost_table_A['sections'][] = [
                        'title'     => $statement_2,
                        'amount'    => $__premium_A_row_2
                    ];

                    // Subtotal
                    $__premium_A_row_KA = $__premium_A_row_1 + $__premium_A_row_2;
                    $__cost_table_A['sections'][] = [
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
            }
            $__cost_table_A['sections'][] = [
                'title' => "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को " . $primary_tariff_vehicle['over_age_rate'] . " %",
                'amount' => $__premium_A_row_old_age
            ];

            //
            // ख Sub Total
            //
            $__premium_A_row_KHA =  $__premium_A_row_KA + $__premium_A_row_old_age;
            $__cost_table_A['sections'][] = [
                'title'     => "",
                'amount'    => $__premium_A_row_KHA,
                'label'     => 'ख'
            ];

            // ----------------------------------------------------------------------------------

            // Discount on Voluntary Excess - GA ko X%
            $dr_voluntary_excess        = $post_data_extra_fields['dr_voluntary_excess'] ?? FALSE;
            $amount_voluntary_excess    = $dr_voluntary_excess
                                            ? _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess]
                                            : 0.00;
            $__discount_A_row__voluntary_excess         = 0.00;
            if( $dr_voluntary_excess )
            {
                $__discount_A_row__voluntary_excess = $__premium_A_row_KHA * ($dr_voluntary_excess/100.00);
            }
            $__cost_table_A['sections'][] = [
                'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
                'amount' => $__discount_A_row__voluntary_excess
            ];

            //
            // ग Sub Total
            //
            $__premium_A_row_GA =  $__premium_A_row_KHA - $__discount_A_row__voluntary_excess;
            $__cost_table_A['sections'][] = [
                'title'     => "",
                'amount'    => $__premium_A_row_GA,
                'label'     => 'ग'
            ];

            // ----------------------------------------------------------------------------------

            // NO CLAIM DISCOUNT
            $__discount_A_row__no_claim_discount = 0.00;
            if($no_claim_discount)
            {
                $__discount_A_row__no_claim_discount = $__premium_A_row_GA * ($no_claim_discount/100.00);
            }
            $__cost_table_A['sections'][] = [
                'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “ग” को {$no_claim_discount} %",
                'amount' => $__discount_A_row__no_claim_discount
            ];

            //
            // घ Sub Total
            //
            $__premium_A_row_GHA =  $__premium_A_row_GA - $__discount_A_row__no_claim_discount;
            $__cost_table_A['sections'][] = [
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
            if( $object_attributes->ownership === IQB_PORTFOLIO_OWNERSHIP_NON_GOVT )
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
                        $flag_private_use = $post_data_extra_fields['flag_private_use'] ?? FALSE;
                        $statement = "निजी प्रयोजनमा प्रयोग गरे वापत छुट: “घ” को {$tariff_record->dr_cvc_on_personal_use} प्रतिशत";
                        if( $flag_private_use )
                        {
                            $__discount_A_row_on_personal_use = $__premium_A_row_GHA * ($tariff_record->dr_cvc_on_personal_use/100.00);
                        }
                        break;

                    default:
                        # code...
                        break;
                }
                $__cost_table_A['sections'][] = [
                    'title'  => $statement,
                    'amount' => $__discount_A_row_on_personal_use
                ];

                //
                // ङ Sub Total
                //
                $__premium_A_row_NGA =  $__premium_A_row_GHA - $__discount_A_row_on_personal_use;
                $__cost_table_A['sections'][] = [
                    'title'     => "",
                    'amount'    => $__premium_A_row_NGA,
                    'label'     => 'ङ'
                ];
            }
            else
            {
                $__premium_A_row_NGA =  $__premium_A_row_GHA - $__discount_A_row_on_personal_use;
            }

            $__agent_comissionable_amount = $__premium_A_row_NGA;
            // ----------------------------------------------------------------------------------


            // Agent Commission/Direct Discount? -> Applies only Non-GOVT
            $__discount_A_row__direct_discount = 0.00;
            if( $policy_record->flag_dc === 'D' && $object_attributes->ownership === IQB_PORTFOLIO_OWNERSHIP_NON_GOVT )
            {
                // X% of ङ
                $__discount_A_row__direct_discount = $__premium_A_row_NGA * ($pfs_record->direct_discount/100.00);

                $__cost_table_A['sections'][] = [
                    'title' => "प्रत्यक्ष बीमा वापत छुटः “ङ” को {$pfs_record->direct_discount} %",
                    'amount' => $__discount_A_row__direct_discount
                ];
            }

            // Towing
            $flag_towing = $post_data_extra_fields['flag_towing'] ?? FALSE;
            $__premium_A_row_towing = 0.00;
            if( $flag_towing )
            {
                $__premium_A_row_towing = $tariff_record->pramt_towing ?? 0.00;
            }
            $__cost_table_A['sections'][] = [
                'title' => "दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा वापत बीमा शुल्क थप",
                'amount' => $__premium_A_row_towing
            ];

            // ----------------------------------------------------------------------------------


            //
            // अ Sub Total
            //
            $premium_A_total =  $__premium_A_row_NGA - $__discount_A_row__direct_discount + $__premium_A_row_towing;
            $__cost_table_A['sections'][] = [
                'title'     => "जम्मा",
                'amount'    => $premium_A_total,
                'section_total' => true
            ];

            // Section Sub Total
			$__cost_table_A['sub_total'] = $premium_A_total;

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
            $__cost_table_I = [
                'column_head' => 'इ',
                'title_np'  => 'चालकको दुर्घटना बीमा वापत',
                'title_en'  => 'Driver Accident Insurance'
            ];

            $premium_I_total =  $accident_premium->pramt_driver_accident;
            $__cost_table_I['sections'][] = [
                'title' => "चालक (बीमांक रु. {$insured_value_tariff->driver}  को रु. {$accident_premium->pramt_driver_accident} का दरले)",
                'amount' => $premium_I_total
            ];

            // Section Sub Total
			$__cost_table_I['sub_total'] = $premium_I_total;


            // ---------------------------------------------------------------------------------------
            //  ई) Staff Accident
            // ---------------------------------------------------------------------------------------

            $__cost_table_EE = [
                'column_head' => 'ई',
                'title_np'  => 'परिचालक तथा अन्य कर्मचारी (चालक बाहेक) को दुर्घटना बीमा वापत',
                'title_en'  => 'Staff Accident Insurance'
            ];

            $staff_count = $object_attributes->staff_count ?? 0;
            $premium_EE_total =  $accident_premium->pramt_accident_per_staff * $staff_count;
            $__cost_table_EE['sections'][] = [
                'title' => "प्रति ब्यक्ति (बीमांक रु. {$insured_value_tariff->staff}  को लागि प्रति ब्यक्ति रु. {$accident_premium->pramt_accident_per_staff} का दरले)",
                'amount' => $premium_EE_total
            ];

            // Section Sub Total
			$__cost_table_EE['sub_total'] = $premium_EE_total;


            // ---------------------------------------------------------------------------------------
            //  उ) Passenger Accident
            // ---------------------------------------------------------------------------------------

            $__cost_table_U = [
                'column_head' => 'उ',
                'title_np'  => 'यात्रीको दुर्घटना बीमा वापत',
                'title_en'  => 'Passenger Accident Insurance'
            ];

            $passenger_count    = $object_attributes->carrying_unit === 'S' ?  $object_attributes->carrying_capacity : 0;
            $premium_U_total   =  $accident_premium->pramt_accident_per_passenger * $passenger_count;
            $__cost_table_U['sections'][] = [
                'title' => "प्रति ब्यक्ति (बीमांक रु. {$insured_value_tariff->staff}  को लागि प्रति ब्यक्ति रु. {$accident_premium->pramt_accident_per_passenger} का दरले)",
                'amount' => $premium_U_total
            ];

            // Section Sub Total
			$__cost_table_U['sub_total'] = $premium_U_total;


		if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {

            // ---------------------------------------------------------------------------------------
            //  ऊ) Risk Group
            //
            //  NOTE: Please note that you have to compute using both the Vehicle total price and
            // 			Trailer/Trolly Price (if any)
            // ---------------------------------------------------------------------------------------
            $__cost_table_OO = [
                'column_head' => 'ऊ',
                'title_np'  => 'जोखिम समूह थप गरे वापत',
                'title_en'  => 'Pool risk insurance amounted to'
            ];

            $flag_risk_mob          = $post_data_extra_fields['flag_risk_mob'] ?? NULL;
            $flag_risk_terorrism    = $post_data_extra_fields['flag_risk_terorrism'] ?? NULL;

            // Mob/Strike
            $__premium_OO_row_1 = 0.00;
            if($flag_risk_mob)
            {
                $__premium_OO_row_1 = (float)($vehicle_total_price + $trailer_price) * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
            }
            $__cost_table_OO['sections'][] = [
                'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
                'amount' => $__premium_OO_row_1
            ];

            // Terorrism
            $__premium_OO_row_2 = 0.00;
            $__premium_OO_row_3 = 0.00;
            $__premium_OO_row_4 = 0.00;
            $__premium_OO_row_5 = 0.00;
            if($flag_risk_terorrism)
            {
                $__premium_OO_row_2 = (float)( ($vehicle_total_price + $trailer_price) * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00));

                // Premium for "Per Thousand Insured Amount" on Terorrism rate_additionl_per_thousand_on_extra_rate

                // Driver Covered
                // Staff Covered
                // Passenger Cover
                // Driver Count  = 1
                // Passenger Count = Seat Capacity
                // Tariff Rate: rate_additionl_per_thousand_on_extra_rate
                $passenger_count = $object_attributes->carrying_unit == 'S' ?   $object_attributes->carrying_capacity : 0;

                // Driver Premium
                $__premium_OO_row_3 = ($insured_value_tariff->driver/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

                // Staff
                $__premium_OO_row_4 = ($insured_value_tariff->staff/1000.00) * $staff_count * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate;

                // Passenger Premium
                $__premium_OO_row_5 = $passenger_count * (($insured_value_tariff->passenger/1000.00) * $tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate);
            }

            // उ TOTAL
            $premium_OO_total = $__premium_OO_row_1 + $__premium_OO_row_2 + $__premium_OO_row_3 + $__premium_OO_row_4 + $__premium_OO_row_5;

            $__cost_table_OO['sections'][] = [
                'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
                'amount' => $__premium_OO_row_2
            ];
            $__cost_table_OO['sections'][] = [
                'title' => "(ग) चालक",
                'amount' => $__premium_OO_row_3
            ];
            $__cost_table_OO['sections'][] = [
                'title' => "(घ) परिचालक तथा अन्य कर्मचारी",
                'amount' => $__premium_OO_row_4
            ];
            $__cost_table_OO['sections'][] = [
                'title' => "(ङ) यात्री",
                'amount' => $__premium_OO_row_5
            ];

            $__cost_table_OO['sections'][] = [
                'title'     => 'जम्मा', // Subtotal
                'amount'    => $premium_OO_total,
                'section_total' => true
            ];

            // Section Sub Total
			$__cost_table_OO['sub_total'] = $premium_OO_total;
        }

        // ---------------------------------------------------------------------------------------

        /**
         * Third Party (आ)
         */
        $__cost_table_AA = [
            'column_head' => 'आ',
            'title_np'  => 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
            'title_en'  => 'Third party liability insurance amounted to'
        ];
        $__premium_AA_row_1 = $primary_tariff_vehicle['third_party'];
        $__premium_AA_row_2 = 0.00;     // Compute No claim discount on Third Party if Comprehensive Package
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE && $no_claim_discount != 0)
        {
            $__premium_AA_row_2 = $__premium_AA_row_1 * ($no_claim_discount/100.00);
        }
        $__cost_table_AA['sections'][] = [
            'title' => "सि. सि. अनुसारको बीमाशुल्क",
            'amount' => $__premium_AA_row_1
        ];

        // No claim Dicount Only if Comprehensive
        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE)
        {
            $__cost_table_AA['sections'][] = [
                'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “छ” को $no_claim_discount %",
                'amount' => $__premium_AA_row_2
            ];
        }

        // Third Party : Sub Total
        $premium_AA_total = $__premium_AA_row_1 - $__premium_AA_row_2;
        $__cost_table_AA['sections'][] = [
            'title'     => 'जम्मा', // Subtotal
            'amount'    => $premium_AA_total,
            'section_total' => true
        ];

        // Section Sub Total
		$__cost_table_AA['sub_total'] = $premium_AA_total;

        // ---------------------------------------------------------------------------------------
        // Total Computation
        // ---------------------------------------------------------------------------------------

        //
        // Grand Total
        //
        $total_amount = $premium_A_total + $premium_AA_total  + $premium_I_total + $premium_EE_total + $premium_U_total + $premium_OO_total;


        //
        // Stamp Duty
        //
        $stamp_duty = $data['stamp_duty'];


        //
        // Premium Data
        //
        $__cost_table = [
            'total_amount'  => $total_amount,
            'stamp_duty'    => $stamp_duty,
            'extra_fields'  => $post_data_extra_fields ? json_encode($post_data_extra_fields) : NULL
        ];


        if(strtoupper($policy_record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
        {
            $__cost_table['attributes'] = json_encode( [$__cost_table_AA, $__cost_table_I, $__cost_table_EE, $__cost_table_U] );
        }
        else
        {

            $__cost_table_comprehensive = array_filter([

                // Comprehensive
                $__cost_table_A,

                // Third Party
                $__cost_table_AA,

                // Driver Accident
                $__cost_table_I,

                // Staff Accident
                $__cost_table_EE,

                // Passenger Accident
                $__cost_table_U,

                // Risk Group
                $__cost_table_OO
            ]);

            $__cost_table['attributes'] = json_encode($__cost_table_comprehensive);


            /**
             * Agent Commission?
             * ------------------
             *
             * Applies only if the vehicle is non-govt and no direct discount
             */
            if($policy_record->flag_dc === 'C' && $object_attributes->ownership === IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT )
            {
                $__cost_table['comission_amount'] = $__agent_comissionable_amount;
            }
        }

        return $__cost_table;
    }
}

// -----------------------------------------------------------------------

if ( ! function_exists('_PORTFOLIO_MOTOR_CVC_primary_tariff_vehicle'))
{
    /**
     * Get Primary Tariff of a Vehicle
     *
     * @param object $object_attributes
     * @param object $default_tariff  JSON decoded tariff column from Motor Tariff Table
     * @param integer $vehicle_age_in_yrs Vehicle Age in Years (From the Date of Registration)
     * @return array
     */
    function _PORTFOLIO_MOTOR_CVC_primary_tariff_vehicle($object_attributes, $default_tariff, $vehicle_age_in_yrs=0)
    {
        $primary_tariff_vehicle['rate']                 = 0.00;
        $primary_tariff_vehicle['minus_amount']         = 0.00;
        $primary_tariff_vehicle['plus_amount']          = 0.00;
        $primary_tariff_vehicle['ec_threshold']         = 0;
        $primary_tariff_vehicle['vehicle_capacity']         = 0; // depending upon cvc_type ( )
        $primary_tariff_vehicle['cost_per_ec_above']    = 0.00;

        $primary_tariff_vehicle['fragmented']             = 'N';
        $primary_tariff_vehicle['base_fragment']          = 0.00;
        $primary_tariff_vehicle['base_fragment_rate']     = 0.00;
        $primary_tariff_vehicle['rest_fragment_rate']     = 0.00;

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
                $primary_tariff_vehicle['rate']                           = (float)$t->rate->rate;
                $primary_tariff_vehicle['minus_amount']                = (float)$t->rate->minus_amount;
                $primary_tariff_vehicle['plus_amount']                 = (float)$t->rate->plus_amount;

                $primary_tariff_vehicle['ec_threshold']                      = $t->rate->ec_threshold;
                $primary_tariff_vehicle['cost_per_ec_above']            = (float)$t->rate->cost_per_ec_above;;

                $primary_tariff_vehicle['fragmented']                          = $t->rate->fragmented;
                $primary_tariff_vehicle['base_fragment']                          = (float)$t->rate->base_fragment;
                $primary_tariff_vehicle['base_fragment_rate']                    = (float)$t->rate->base_fragment_rate;
                $primary_tariff_vehicle['rest_fragment_rate']                     = (float)$t->rate->rest_fragment_rate;

                $primary_tariff_vehicle['third_party']                   = (float)$t->third_party;

                if( $t->age->age1_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age1_max )
                {
                    $primary_tariff_vehicle['over_age_rate'] = (float)$t->age->rate1;
                }
                else if( $t->age->age2_min <= $vehicle_age_in_yrs && $vehicle_age_in_yrs <= $t->age->age2_max )
                {
                    $primary_tariff_vehicle['over_age_rate'] = (float)$t->age->rate2;
                }
                break;
            }
        }

        return $primary_tariff_vehicle;
    }
}
// ------------------------------------------------------------------------





