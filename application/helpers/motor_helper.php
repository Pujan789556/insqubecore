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
        $no_claim_discount 		= $data['no_claim_discount'] ?? 0;
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
        if($policy_record->policy_package === 'cp')
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
			$dr_voluntary_excess 		= $data['dr_voluntary_excess'] ?? 0.00;
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
			$__premium_A_row_7 = $__premium_A_row_5 - $__premium_A_row_6;
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

			// ---------------------------------------------------------------------------------------


			/**
			 * Risk Pool (इ)
			 */
			$__cost_table_I = [
				'column_head' => 'इ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];
			$flag_risk_mob = $data['riks_group']['flag_risk_mob'] ?? NULL;
			$flag_risk_terorrism = $data['riks_group']['flag_risk_terorrism'] ?? NULL;

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
		}



		/**
		 * Third Party (आ)
		 */
		$amount_noClaimDiscount_on_thirdParty = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if($policy_record->policy_package === 'cp' && $no_claim_discount != 0)
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
		if($policy_record->policy_package === 'cp')
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
			'stamp_duty' 	=> $stamp_duty
		];

		if($policy_record->policy_package === 'tp')
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
            if( $attributes->engine_capacity >= $t->ec_min && $attributes->engine_capacity <= $t->ec_max )
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
        $no_claim_discount 		= $data['no_claim_discount'] ?? 0;
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
        if($policy_record->policy_package === 'cp')
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
			$flag_commercial_use = $data['flag_commercial_use'] ?? FALSE;
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
			$dr_voluntary_excess 		= $data['dr_voluntary_excess'] ?? FALSE;
			$amount_voluntary_excess 	= $dr_voluntary_excess
											? _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess]
											: 0.00;
			$__premium_A_row_10 		= 0.00;
			if( $dr_voluntary_excess )
			{
				$__premium_A_row_10 = $__premium_A_row_GA * ($dr_voluntary_excess/100.00);
			}
			$__cost_table_A['sections'][] = [
				'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
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
			$__premium_A_row_NGA = $__premium_A_row_13 = $__premium_A_row_GHA - $__premium_A_row_12;
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
			$flag_towing = $data['flag_towing'] ?? FALSE;
			$__premium_A_row_16 = 0.00;
			if( $flag_towing )
			{
				$__premium_A_row_16 = $tariff_record->pramt_towing ?? 0.00;
			}
			$__cost_table_A['sections'][] = [
				'title' => "दुर्घटना भएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमा वापत बीमा शल्ुक थप",
				'amount' => $__premium_A_row_16
			];


			// Section Sub Total (अ Total)
			$premium_A_total  = $__premium_A_row_17 = $__premium_A_row_CHA + $__premium_A_row_16;
			$__cost_table_A['sections'][] = [
				'title' 	=> "जम्मा",
				'amount' 	=> $premium_A_total,
				'section_total' => true
			];


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


			// ---------------------------------------------------------------------------------------


			/**
			 * Risk Pool (उ)
			 */
			$__cost_table_U = [
				'column_head' => 'उ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to'
			];

			$flag_risk_mob = $data['riks_group']['flag_risk_mob'] ?? NULL;
			$flag_risk_terorrism = $data['riks_group']['flag_risk_terorrism'] ?? NULL;


			// Mob/Strike
			$__premium_U_row_1 = 0.00;
			if($flag_risk_mob)
			{
				$__premium_U_row_1 = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
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
				$__premium_U_row_2 = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);

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
		}



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
		if($policy_record->policy_package === 'cp' && $no_claim_discount != 0)
		{
			$__premium_AA_row_2 = $__premium_AA_row_1 * ($no_claim_discount/100.00);
		}
		$__cost_table_AA['sections'][] = [
			'title' => "सि. सि. अनुसारको बीमाशुल्क",
			'amount' => $__premium_AA_row_1
		];

		// No claim Dicount Only if Comprehensive
		if($policy_record->policy_package === 'cp')
		{
			$__cost_table_AA['sections'][] = [
				'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “छ” को $no_claim_discount %",
				'amount' => $__premium_AA_row_2
			];
		}

		// Third Party : Sub Total (छ Total)
		$__cost_table_AA['sections'][] = [
			'title' 	=> 'जम्मा', // Subtotal
			'amount' 	=> $premium_AA_total,
			'section_total' => true
		];


		// आ TOTAL
		$premium_AA_total = $__premium_AA_row_1 - $__premium_AA_row_2;

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
			'stamp_duty' 	=> $stamp_duty
		];

		if($policy_record->policy_package === 'tp')
		{
			$__cost_table['attributes'] = json_encode( [$__cost_table_AA] );
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
		}

		return $__cost_table;
	}
}
// ------------------------------------------------------------------------





