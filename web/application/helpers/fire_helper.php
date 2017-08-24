<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Fire Portfolio Helper Functions
 *
 * This file contains helper functions related to Fire Portfolio
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


if ( ! function_exists('_PO_FIRE_compute_short_term_premium'))
{
	/**
	 * FIRE PORTFOLIO: Compute Short Term Policy Premium
	 *
	 * @param object $policy_record Policy Record
	 * @param object $pfs_record Portfolio Settings Record
	 * @param array $cost_table Cost Table computed by Specific cost table function
	 * @return	array
	 */
	function _PO_FIRE_compute_short_term_premium( $policy_record, $pfs_record, $cost_table )
	{
		echo '@TODO: _PO_FIRE_compute_short_term_premium'; exit;
		/**
		 * SHORT TERM POLICY?
		 * ---------------------
		 *
		 * If the policy is short term policy, we have to calculate the short term values
		 *
		 */
		$CI =& get_instance();
        $CI->load->model('fiscal_year_model');
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
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_FIRE_row_snippet'))
{
	/**
	 * Get Policy Object - Fire - Row Snippet
	 *
	 * Row Partial View for Fire Object
	 *
	 * @param object $record Policy Object (Fire)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_FIRE_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_fire', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_select_text'))
{
	/**
	 * Get Policy Object - FIRE - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_FIRE_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$items 		= $attributes->items ?? NULL;
		$item_count = count( $items->category ?? [] );
		$snippet = [];
		for($i = 0; $i < $item_count; $i++ )
		{
			$snippet[] = '<strong>' .
							_OBJ_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ] .
						'</strong>, ' .
						_OBJ_FIRE_item_ownership_dropdown(FALSE)[ $items->ownership[$i] ] . ', ' .
						'<em>Rs. ' . $items->sum_insured[$i] . '</em>';
		}
		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_validation_rules'))
{
	/**
	 * Get Policy Object - FIRE - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_FIRE_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post = $CI->input->post();
		$object = $post['object'] ?? NULL;

		$category_dropdown 	= _OBJ_FIRE_item_category_dropdown( FALSE );
		$ownership_dropdown = _OBJ_FIRE_item_ownership_dropdown( FALSE );
		$conscat_dropdown 	= _OBJ_FIRE_item_building_category_dropdown( FALSE );
		$district_dropdown 	= district_dropdown( FALSE );

		$v_rules = [
			/**
			 * Item List
			 */
			'items' =>[
				[
			        'field' => 'object[items][category][]',
			        '_key' => 'category',
			        'label' => 'Item Category',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($category_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $category_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][sum_insured][]',
			        '_key' => 'sum_insured',
			        'label' => 'Item Price(Sum Insured)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][ownership][]',
			        '_key' => 'ownership',
			        'label' => 'Item Ownership',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($ownership_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $ownership_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ]
		    ],

		    /**
		     * Land Owner Details (Building)
		     */
		    'land_building_owner' => [
		    	[
			        'field' => 'object[land_building][owner_name][]',
			        '_key' => 'owner_name',
			        'label' => 'Owner Name(s)',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][owner_address][]',
			        '_key' => 'owner_address',
			        'label' => 'Owner Address',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'textarea',
			        'rows' 		=> 4,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][owner_contacts][]',
			        '_key' => 'owner_contacts',
			        'label' => 'Owner Contacts(Mobile/Phone)',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],


			    /**
			     * Land Details (Building)
			     */
		    	[
			        'field' => 'object[land_building][plot_no][]',
			        '_key' => 'plot_no',
			        'label' => 'Land Plot No.',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][house_no][]',
			        '_key' => 'house_no',
			        'label' => 'House No.',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][tole][]',
			        '_key' => 'tole',
			        'label' => 'Tole/Street Address',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][district][]',
			        '_key' => 'district',
			        'label' => 'District',
			        'rules' => 'trim|numeric|exact_length[2]|in_list['. implode(',', array_keys($district_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $district_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][vdc][]',
			        '_key' => 'vdc',
			        'label' => 'VDC/Municipality',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][ward_no][]',
			        '_key' => 'ward_no',
			        'label' => 'Ward No.',
			        'rules' => 'trim|integer|max_length[2]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][storey_no][]',
			        '_key' => 'storey_no',
			        'label' => 'No. of Stories',
			        'rules' => 'trim|integer|max_length[4]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][category][]',
			        '_key' => 'category',
			        'label' => 'Construction Category',
			        'rules' => 'trim|integer|exact_length[1]|in_list['. implode(',', array_keys($conscat_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $conscat_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][used_for][]',
			        '_key' => 'used_for',
			        'label' => 'Used For',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
		    ]
		];

		// return formatted?
		$fromatted_v_rules = [];
		if($formatted === TRUE)
		{
			foreach ($v_rules as $key=>$section)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $section);
			}
			return $fromatted_v_rules;
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_item_building_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item Building Construction category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_item_building_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'1' => 'First',
			'2' => 'Second',
			'3'	=> 'Third',
			'4' => 'Fourth',
			'5' => 'Open Space'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_item_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_item_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'BWALL' => 'Boundary Wall',
			'BLDNG' => 'Building',
			'GOODS'	=> 'Goods',
			'MCNRY' => 'Machinary',
			'OTH' 	=> 'Others'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_item_ownership_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_item_ownership_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'O' => 'Owned',
			'R' => 'Rented'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - FIRE Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_FIRE_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * A single Fire Policy may hold multiple Items
		 */
		$sum_inusred_arr = $data['items']['sum_insured'];
		$amt_sum_insured = 0;
		foreach($sum_inusred_arr as $si)
		{
			$amt_sum_insured += $si;
		}
		return $amt_sum_insured;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_transactional_attributes'))
{
	/**
	 * Get the list of transactional attributes for FIRE Portfolio
	 *
	 * These are the object attributes, whose change will affect on
	 * 	- Sum Insured Amount
	 * 	- Premium
	 *
	 * For tariff-portfolio, we must need this list to generate cost reference table.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return float
	 */
	function _OBJ_FIRE_transactional_attributes( $portfolio_id )
	{
		echo '@TODO: _OBJ_FIRE_transactional_attributes'; exit;
		return  [

			// Vehicle Ownership
			'ownership',

			// Disable Friendly Vehicle?
			'flag_mcy_df',

			// Engine Capacity Unig
			'ec_unit',

			// Engine Capacity
			'engine_capacity',

			// Vehicle Price
			'price_vehicle',

			// Accessories Price
			'price_accessories',

			// Carrying Uning
			'carrying_unit',

			// Carrying Capacity
			'carrying_capacity',

			// Staff Count
			'staff_count',

			// Trailer Price
			'trailer_price'
		];
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for FIRE Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $tariff_record 	Portfolio Tariff Record
	 * @param bool $formatted 			Return Sectioned or Formatted
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_FIRE_premium_validation_rules($policy_record, $pfs_record, $tariff_record, $formatted = true, $return = 'specific' )
	{
		echo '@TODO: _TXN_FIRE_premium_validation_rules'; exit;

		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );

		$validation_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'common_all' => [
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
			],



			/**
			 * Third Party Validation Rules
			 * ----------------------------
			 * We only need stamp duty. The rest are auto computed from tariff.
			 */
			'third_party' => [
				[
	                'field' => 'amt_stamp_duty',
	                'label' => 'Stamp Duty(Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_default' 	=> $pfs_record->stamp_duty,
	                '_required' => true
	            ]
			],

			/**
			 * Comprehensive Common Validation Rules
			 * ---------------------------------------
			 * These rules apply to MCY/PVC/CVC
			 */
			'comprehensive_common' => [
				[
                    'field' => 'other_cost_fields[dr_voluntary_excess]',
                    'label' => 'Voluntary Excess',
                    'rules' => 'trim|prep_decimal|decimal|max_length[5]',
                    '_key' 		=> 'dr_voluntary_excess',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess),
                    '_required' => false
                ],
                [
                    'field' => 'other_cost_fields[no_claim_discount]',
                    'label' => 'No Claim Discount',
                    'rules' => 'trim|prep_decimal|decimal|max_length[5]',
                    '_key' 		=> 'no_claim_discount',
                    '_type'     => 'dropdown',
                    '_data' 	=> _PO_MOTOR_no_claim_discount_dropdown($tariff_record->no_claim_discount),
                    '_required' => false
                ],
                [
                    'field' => 'other_cost_fields[flag_risk_pool]',
                    'label' => 'Pool Risk (जोखिम समूह बीमा)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_risk_pool',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
                    '_help_text' => '<small>This covers both (हुलदंगा, हडताल र द्वेशपूर्ण कार्य जोखिम बीमा) & (आतंककारी/विध्वंशात्मक कार्य जोखिम बीमा) </small>'
                ],
                [
	                'field' => 'amt_stamp_duty',
	                'label' => 'Stamp Duty(Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_default' 	=> $pfs_record->stamp_duty,
	                '_required' => true
	            ]
			],

			/**
			 * Comprehensive PVC Only Validation Rules
			 * ---------------------------------------
			 * These rules apply to Private Vehicle
			 */
			'comprehensive_pvc' => [

				// Commercial Use
				[
                    'field' => 'other_cost_fields[flag_commercial_use]',
                    'label' => 'Commercial Use (निजी प्रयोजनको लागि भाडामा दिएको)',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_commercial_use',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false
                ],

                // Pay for Towing
				[
                    'field' => 'other_cost_fields[flag_towing]',
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
                    'field' => 'other_cost_fields[flag_private_use]',
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
                    'field' => 'other_cost_fields[flag_towing]',
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
		 * Do we need to return all validation rules or Policy Package Specific only?
		 */
		$rules = [];
		if( $return === 'specific')
		{
			if($policy_record->policy_package == IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY)
			{
				$rules['third_party'] = $validation_rules['third_party'];
				$rules['common_all'] = $validation_rules['common_all'];
			}
			else
			{
				$rules['comprehensive_common'] = $validation_rules['comprehensive_common'];

				// Portfolio Specific Rules
				if( (int)$policy_record->portfolio_id === IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID )
				{
					$rules['comprehensive_pvc'] = $validation_rules['comprehensive_pvc'];
				}
				elseif( (int)$policy_record->portfolio_id === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID )
				{
					$rules['comprehensive_cvc'] = $validation_rules['comprehensive_cvc'];
				}

				// common to all
				$rules['common_all'] = $validation_rules['common_all'];
			}
		}
		else
		{
			$rules = $validation_rules;
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

