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
		$premiumm_thirdparty 	= 0.00;
		$default_rate 			= 0.00;
		$vehicle_over_age_rate 	= 0.00;
        foreach ($default_tariff as $t)
        {
            if( $attributes->engine_capacity >= $t->ec_min && $attributes->engine_capacity <= $t->ec_max )
            {
                $premiumm_thirdparty = $t->third_party;
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

        // Rsik Group
        $tariff_rsik_group = json_decode($tariff_record->riks_group);


        /**
         * Package Comprehensive
         */
        $premium_A_total = 0.00;
        $premium_I_total = 0.00;
        if($policy_record->policy_package === 'cp')
		{
			/**
			 * Defaults (अ)
			 */

			// premium_ka =  X% of Vehicle Price
			$premium_KA =  $vehicle_total_price * ($default_rate/100.00);


			// Additional cost due to vehicle over aging
			$premium_KA_additional = 0.00;
			if( $vehicle_over_age_rate > 0.00 )
			{
				$premium_KA_additional = $premium_KA * ($vehicle_over_age_rate/100.00);
			}
			// premium_KHA =  premium_KA + premium_KA_additional
			$premium_KHA = $premium_KA + $premium_KA_additional;


			// Discount on Voluntary Excess : discount_KHA
			$discount_KHA 				= 0.00;

			$dr_voluntary_excess 		= $data['dr_voluntary_excess'] ?? 0.00;
			$amount_voluntary_excess 	= $dr_voluntary_excess ? _PORTFOLIO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '')[$dr_voluntary_excess] : 0.00;

			if($dr_voluntary_excess)
			{
				$discount_KHA = $premium_KHA * ($dr_voluntary_excess/100.00);
			}
			// premium_ga = premium_KHA - discount_KHA
			$premium_GA = $premium_KHA - $discount_KHA;


			// No Claim Discount : discount_GA
			$no_claim_discount 		= $data['no_claim_discount'] ?? 0.00;
			$year_no_claim_discount = $no_claim_discount ? _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, false, '')[$no_claim_discount] : '';
			$discount_GA 			= 0.00;
			if($no_claim_discount)
			{
				$discount_GA = $premium_GA * ($no_claim_discount/100.00);
			}
			// premium_GHA = premium_GA - discount_GA
			$premium_GHA = $premium_GA - $discount_GA;

			//
			// Agent Commission/Direct Discount? -> Applies only Non-GOVT
			//
			$discount_GHA = 0.00;
			if( $policy_record->flag_dc === 'D' && $attributes->ownership === IQB_PORTFOLIO_OWNERSHIP_NON_GOVT )
			{
				// X% of GHA
				$discount_GHA = $premium_GHA * ($pfs_record->direct_discount/100.00);
			}

			// अ Total
			$premium_A_total = $premium_GHA  - $discount_GHA;

			// ---------------------------------------------------------------------------------------

			/**
			 * Risk Pool (इ)
			 */
			$flag_risk_mob = $data['riks_group']['flag_risk_mob'] ?? NULL;
			$flag_risk_terorrism = $data['riks_group']['flag_risk_terorrism'] ?? NULL;

			$premium_risk_mob = 0.00;
			if($flag_risk_mob)
			{
				$premium_risk_mob = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_mob/100.00);
			}

			$premium_risk_terorrism = 0.00;
			$premium_for_insured_covered_on_terorrism = 0.00;
			if($flag_risk_terorrism)
			{
				$premium_risk_terorrism = $vehicle_total_price * ($tariff_rsik_group->rate_pool_risk_terorrism/100.00);

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
			}



			// इ TOTAL
			$premium_I_total = $premium_risk_mob + $premium_risk_terorrism + $premium_for_insured_covered_on_terorrism;
		}



		/**
		 * Third Party (आ)
		 */

		$year_no_claim_discount = 0;
		$no_claim_discount = 0;
		$premium_NGA = $premiumm_thirdparty;
		$discount_NGA = 0.00;		// Compute No claim discount on Third Party if Comprehensive Package
		if($policy_record->policy_package === 'cp')
		{
			$no_claim_discount 		= $data['no_claim_discount'] ?? 0.00;
			$year_no_claim_discount = $no_claim_discount ? _PORTFOLIO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount, false, '')[$no_claim_discount] : '';
			if($no_claim_discount)
			{
				$discount_NGA = $premium_NGA * ($no_claim_discount/100.00);
			}
		}

		// आ TOTAL
		$premium_AA_total = $premium_NGA - $discount_NGA;

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
		$__cost_table_third_party = [
			'column_head' => 'आ',
			'title_np' 	=> 'तेश्रो पक्ष प्रतिको दायित्व बीमा वापत',
			'title_en' 	=> 'Third party liability insurance amounted to',
			'sections' 	=> [

				// Section Ka
				[
					// Cost according to CC
					[
						'title' 	=> "सि. सि. अनुसारको बीमाशुल्क",
						'amount' 	=> $premium_NGA,
						'label' 	=> 'ङ'
					],

					// No Claim Discount
					[
						'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “ङ” को $no_claim_discount %",
						'amount' => $discount_NGA
					],

					// Sub Total
					[
						'title' 	=> 'जम्मा', // Subtotal
						'amount' 	=> $premium_AA_total,
						'section_total' => true
					]
				]
			]
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

					// Section Ka
					[
						// Cost according to CC
						[
							'title' => "(अ) र (आ) को जम्मा रकममा {$tariff_record->dr_mcy_disabled_friendly} %̈ छुट",
							'amount' => $discount_MCY_DF
						],

						// Sub Total
						[
							'title' 	=> 'जम्मा', // Subtotal
							'amount' 	=> $premium_total_A_AA,
							'section_total' => true
						]
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
			$__cost_table['attributes'] = json_encode(array_filter([$__cost_table_third_party, $__cost_table_flag_mcy_df]));
		}
		else
		{

			$__cost_table_default = [
				'column_head' => 'अ',
				'title_np' 	=> 'सवारी साधनको क्षति/हानि–नोक्सानी बिरुद्धको बीमा तथा दुर्घटना बीमा वापत',
				'title_en' 	=> 'Insurance against vehicle damage/loss & accident insurance amounted to',
				'sections' => [

					// Section Ka
					[
						// Ghosit Mulya Ko X%
						[
							'title' 	=> "सि.सि.तथा घोषित मूल्य (सरसामान सहित) अनुसार बीमा शुल्क (घोषित मूल्यको {$default_rate}%)",
							'amount' 	=> $premium_KA,
							'label' 	=> 'क'
						],

						// Purano Vaebapatko Thap Mulya
						[
							'title' => "सवारी साधन {$vehicle_age_in_yrs} वर्ष पुरानो भए वापत थप बीमाशुल्क: “क” को {$vehicle_over_age_rate}%",
							'amount' => $premium_KA_additional
						],

						// Sub Total
						[
							'title' 	=> '', // Subtotal
							'amount' 	=> $premium_KHA,
							'label' 	=> 'ख'
						]
					],

					// Section Kha
					[
						// Voluntary Excess Discount
						[
							'title' => "बीमित स्वयंले व्यहोर्ने स्वेच्छीक अधिक रु {$amount_voluntary_excess} वापत छुटः “ख” को {$dr_voluntary_excess} %",
							'amount' => $discount_KHA
						],

						// Subtotal
						[
							'title' 	=> '', // Subtotal
							'amount' 	=> $premium_GA,
							'label' 	=> 'ग'
						]
					],

					// Section Ga
					[
						// NO Claim Discount
						[
							'title' => "{$year_no_claim_discount} वर्षसम्म दावी नगरे वापत छूटः “ग” को $no_claim_discount %",
							'amount' => $discount_GA
						],

						// Subtotal
						[
							'title' 	=> '', // Subtotal
							'amount' 	=> $premium_GHA,
							'label' 	=> 'घ'
						]
					],

					// Section Gha
					[
						// Direct Discount
						[
							'title' => "प्रत्यक्ष बीमा वापत छूटः “घ” को {$pfs_record->direct_discount} %",
							'amount' => $discount_GHA
						],

						// Subtotal
						[
							'title' 		=> 'जम्मा', // Subtotal
							'amount' 		=> $premium_A_total,
							'section_total' => true
						]
					]
				]
			];

			$__cost_table_risk_pool = [
				'column_head' => 'इ',
				'title_np' 	=> 'जोखिम समूह थप गरे वापत',
				'title_en' 	=> 'Pool risk insurance amounted to',
				'sections' => [

					// Section Ka
					[
						// Cost according to CC
						[
							'title' => "(क) हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_mob}% का दरले)",
							'amount' => $premium_risk_mob
						],

						// Purano Vaebapatko Thap Mulya
						[
							'title' => "(ख) आतंककारी तथा विध्वंसात्मक कार्य (घोषित मूल्यको {$tariff_rsik_group->rate_pool_risk_terorrism}%का दरले)",
							'amount' => $premium_risk_terorrism
						],

						// Driver & Passenger Charge if Terorrism is Selected
						[
							'title' => "(ग) चालक तथा पछाडि बस्ने व्यक्तिको आतंकबाद वापत {$tariff_rsik_group->rate_additionl_per_thousand_on_extra_rate} प्रति हजारका दरले ",
							'amount' => $premium_for_insured_covered_on_terorrism
						],

						// Sub Total
						[
							'title' 	=> 'जम्मा', // Subtotal
							'amount' 	=> $premium_I_total,
							'section_total' => true
						]
					]
				]
			];


			$__cost_table_comprehensive = array_filter([
				// Comprehensive
				$__cost_table_default,

				// Third Party
				$__cost_table_third_party,

				// Discount Disabled Friendly
				$__cost_table_flag_mcy_df,

				// Pool Risk
				$__cost_table_risk_pool
			]);

			$__cost_table['attributes'] = json_encode($__cost_table_comprehensive);
		}

		return $__cost_table;
	}
}
// ------------------------------------------------------------------------



// ------------------------------------------------------------------------





