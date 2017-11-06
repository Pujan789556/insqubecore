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

if ( ! function_exists('_OBJ_FIRE_compute_sum_insured_amount'))
{
	/**
	 * Compute Sum Insured Amount of Policy Object - FIRE Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_FIRE_compute_sum_insured_amount( $portfolio_id, $data )
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

if ( ! function_exists('_FIRE_compute_premium_per_risk_per_item'))
{
	/**
	 * Compute Single Risk Premium per Item
	 *
	 * @param decimal 	$item_sum_insured Sum Inusred Amount of a Fire Item
	 * @param decimal 	$rate
	 * @return chars 	$rate_base PT|RT [Per Thousand | Percent]
	 */
	function _FIRE_compute_premium_per_risk_per_item( $item_sum_insured, $rate, $rate_base )
	{
		$premium = 0.00;
		/**
		 * Rate Per Thousand of Sum Insured Amount
		 */
		if($rate_base == 'PT')
		{
			$premium = ( $item_sum_insured / 1000.00 ) * $rate;
		}
		else
		{
			$premium = ( $item_sum_insured * $rate ) / 100.00;
		}

		return $premium;
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
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_FIRE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );

		$rate_base_dropdown = [
			'PT'	=> 'Per Thousand',
			'RT'	=> 'Percent'
		];

		$validation_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => [
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
			],

			/**
			 * Premium Validation Rules - Template
			 */
			'premium' => [
                [
	                'field' => 'premium[risk]',
	                'label' => 'Rate',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[rate]',
	                'label' => 'Rate',
	                'rules' => 'trim|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
	                '_required' => true
	            ],
	            [
                    'field' => 'premium[rate_base]',
                    'label' => 'Premium Rate Base',
                    'rules' => 'trim|alpha|exact_length[2]|in_list['.implode(',', array_keys($rate_base_dropdown)).']',
                    '_key' 		=> 'rate_base',
                    '_type'     => 'dropdown',
                    '_data' 	=> $rate_base_dropdown,
                    '_required' => false
                ],
			]
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$rules = $validation_rules['basic'];

			/**
			 * Premium Validation Rules
			 */
			$premium_elements 	= $validation_rules['premium'];
			$object_attributes 	= $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
			$items 				= $object_attributes->items;
			$item_count 		= count($items->category);

			// Per Item We have validation rules
			for($i=0; $i < $item_count; $i++ )
			{
				// Loop through each portfolio risks
				foreach($portfolio_risks as $risk_id=>$risk_name)
				{
					foreach ($premium_elements as $elem)
                    {
                    	$elem['field'] .= "[{$risk_id}][{$i}]";
                    	$rules[] = $elem;
                    }
				}
			}
			return $rules;
		}
		return $validation_rules;

	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_premium_goodies'))
{
	/**
	 * Get Policy Policy Transaction Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 * @param object $portfolio_risks Portfolio Risks
	 *
	 * @return	array
	 */
	function _TXN_FIRE_premium_goodies($policy_record, $policy_object, $portfolio_risks)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_FIRE_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_FIRE'))
{
	/**
	 * Fire Portfolio : Save a Policy Transaction Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_FIRE($policy_record, $txn_record)
	{
		$CI =& get_instance();

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $CI->input->post() )
		{

			/**
			 * Policy Object Record
			 */
			$policy_object 		= get_object_from_policy_record($policy_record);

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			/**
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_FIRE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data = $CI->input->post();

				/**
				 * Do we have a valid method?
				 */
				try{

					/**
					 * Compute Premium From Post Data
					 * ------------------------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Pool Premium
					 *  b. Base Premium
					 */


					/**
					 * Portfolio Risks Rows
					 */
					$portfolio_risks = $CI->portfolio_model->portfolio_risks($policy_record->portfolio_id);

					/**
					 * Fire Items with Sum Insured
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$items              = $object_attributes->items;
					$item_count         = count($items->category);



					/**
					 * Let's Loop Through each Fire Item
					 */
					$premium_computation_table 	= [];
					$base_premium 				= 0.00;
					$pool_premium 				= 0.00;
					$commissionable_premium 	= 0.00;
					$direct_discount 			= 0.00;
					$agent_commission 			= 0.00;

					for($i=0; $i < $item_count; $i++ )
					{
						$item_sum_insured 	= $items->sum_insured[$i];
						foreach($portfolio_risks as $pr)
						{
							$rate = $post_data['premium']['rate'][$pr->id][$i];

							// Compute only if rate is supplied
							if($rate)
							{
								$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
								$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

								// Assign to Pool or Base based on Risk Type
								if( $pr->type == IQB_RISK_TYPE_BASIC )
								{
									$base_premium += $premium;
								}
								else
								{
									$pool_premium += $premium;
								}

								// Commissionable Premium?
								if($pr->agent_commission == IQB_FLAG_ON )
								{
									$commissionable_premium += $premium;
								}
							}
						}
					}

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$direct_discount = ( $base_premium * $pfs_record->direct_discount ) / 100.00 ;
						$base_premium -= $direct_discount;

						// NULLIFY Commissionable premium, Agent Commission
						$commissionable_premium = NULL;
						$agent_commission = NULL;
					}
					else
					{
						$agent_commission = ( $commissionable_premium * $pfs_record->agent_commission ) / 100.00;
					}


					/**
					 * Let's Compute the Total Premium
					 */
					$total_premium 	= $base_premium + $pool_premium;
					$taxable_amount = $total_premium + $post_data['amt_stamp_duty'];

					/**
					 * Compute VAT
					 */
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Transactional Data
					 */
					$txn_data = [
						'amt_total_premium' 	=> $total_premium,
						'amt_pool_premium' 		=> $pool_premium,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $amount_vat,
						'txn_details' 			=> $post_data['txn_details'],
						'remarks' 				=> $post_data['remarks'],
					];


					/**
					 * Premium Computation Table
					 * -------------------------
					 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
					 */
					$premium_computation_table = json_encode($post_data['premium']);
					$txn_data['premium_computation_table'] = $premium_computation_table;



					/**
					 * Cost Calculation Table - Schedule Data
					 *
					 * 	Property Details
					 * 	------------------------------------
					 * 	| Property | Sum Insured | Premium |
					 * 	------------------------------------
					 *  |		   | 			 |		   |
					 * 	------------------------------------
					 *
					 * 	Risk Details
					 * 	------------------
					 * 	| Risk | Premium |
					 * 	------------------
					 * 	|	   |		 |
					 * 	------------------
					 */
					$property_table = [];
					for($i=0; $i < $item_count; $i++ )
					{
						$item_sum_insured 		= $items->sum_insured[$i];
						$property_category 		= _OBJ_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ];
						$single_property_row 	= [ $property_category, $items->sum_insured[$i] ];

						$per_property_premium 		= 0.00;
						$per_property_base_premium 	= 0.00;
						$per_property_pool_premium 	= 0.00;

						foreach($portfolio_risks as $pr)
						{
							$rate = $post_data['premium']['rate'][$pr->id][$i];

							// Compute only if rate is supplied
							if($rate)
							{
								$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
								$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

								// Assign to Pool or Base based on Risk Type
								if( $pr->type == IQB_RISK_TYPE_BASIC )
								{
									$per_property_base_premium += $premium;
								}
								else
								{
									$per_property_pool_premium += $premium;
								}
							}
						}

						/**
						 * Direct Discount Applies?
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							$direct_discount 			= ( $per_property_base_premium * $pfs_record->direct_discount ) / 100.00 ;
							$per_property_base_premium -= $direct_discount;
						}

						$per_property_premium 	= $per_property_base_premium  + $per_property_pool_premium;
						$single_property_row[] 	= $per_property_premium;
						$property_table[] 		= $single_property_row;
					}

					// --------------------------------------------------------------------------------------------

					$risk_table = [];
					foreach($portfolio_risks as $pr)
					{
						$per_risk_premium 		= 0.00;
						$per_risk_base_premium 	= 0.00;
						$per_risk_pool_premium 	= 0.00;

						for($i=0; $i < $item_count; $i++ )
						{
							$item_sum_insured 	= $items->sum_insured[$i];
							$rate 				= $post_data['premium']['rate'][$pr->id][$i];

							// Compute only if rate is supplied
							if($rate)
							{
								$rate_base = $post_data['premium']['rate_base'][$pr->id][$i];
								$premium = _FIRE_compute_premium_per_risk_per_item($item_sum_insured, $rate, $rate_base);

								// Assign to Pool or Base based on Risk Type
								if( $pr->type == IQB_RISK_TYPE_BASIC )
								{
									$per_risk_base_premium += $premium;
								}
								else
								{
									$per_risk_pool_premium += $premium;
								}
							}
						}

						/**
						 * Direct Discount Applies?
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							$direct_discount 		= ( $per_risk_base_premium * $pfs_record->direct_discount ) / 100.00 ;
							$per_risk_base_premium 	-= $direct_discount;
						}
						$per_risk_premium 	= $per_risk_base_premium  + $per_risk_pool_premium;


						/**
						 * Include the risk only with premium
						 */
						if( $per_risk_premium )
						{
							$risk_table[] 		= [$pr->name, $per_risk_premium];
						}
					}

					$cost_calculation_table = json_encode([
						'property_table' 	=> $property_table,
						'risk_table'		=> $risk_table
					]);

					$txn_data['cost_calculation_table'] = $cost_calculation_table;

					return $CI->policy_txn_model->save($txn_record->id, $txn_data);


					/**
					 * @TODO
					 *
					 * 1. Build RI Distribution Data For This Policy
					 * 2. RI Approval Constraint for this Policy
					 */

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
					'title' 	=> 'Validation Error!',
					'message' 	=> validation_errors()
				]);
        	}
		}
	}
}