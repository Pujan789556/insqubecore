<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube  Portfolio Helper Functions
 *
 * This file contains helper functions related to following Burglary Portfolios:
 * 	a. Burglary - Jewelry
 * 	b. Burglary - Housebreaking
 * 	c. Burglary - Cash in Safe
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


if ( ! function_exists('_OBJ_MISC_BRG_row_snippet'))
{
	/**
	 * Get Policy Object - Row Snippet
	 *
	 * Row Partial View for  Object
	 *
	 * @param object $record Policy Object
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MISC_BRG_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_brg', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BRG_select_text'))
{
	/**
	 * Get Policy Object - BRG - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MISC_BRG_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BRG_validation_rules'))
{
	/**
	 * Get Policy Object - BRG - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MISC_BRG_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$conscat_dropdown 	= _OBJ_MISC_BRG_item_building_category_dropdown( FALSE );
		$district_dropdown 	= district_dropdown( 'both', FALSE );

		$v_rules = [

			/**
			 * Basic Data
			 */
			'basic' =>[
			    [
			        'field' => 'object[excess]',
			        '_key' => 'excess',
			        'label' => 'अधिक',
			        'rules' => 'trim|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => false
			    ],
		    ],

		    /**
		     * Item Details
		     */
		    'items' => [
		    	[
			        'field' => 'object[items][description][]',
			        '_key' => 'description',
			        'label' => 'Description',
			        'rules' => 'trim|required|max_length[2000]',
			        '_type' => 'textarea',
			        'rows'  => 4,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][sum_insured][]',
			        '_key' => 'sum_insured',
			        'label' => 'Sum Insured(Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
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
			        'rules' => 'trim|numeric|max_length[2]|in_list['. implode(',', array_keys($district_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $district_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][vdc][]',
			        '_key' => 'vdc',
			        'label' => 'VDC/Municipality',
			        'rules' => 'trim|max_length[150]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][ward_no][]',
			        '_key' => 'ward_no',
			        'label' => 'Ward No.',
			        'rules' => 'trim|max_length[10]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][storey_no][]',
			        '_key' => 'storey_no',
			        'label' => 'No. of Stories',
			        'rules' => 'trim|max_length[20]',
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
			        'rules' => 'trim|max_length[100]',
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

if ( ! function_exists('_OBJ_MISC_BRG_item_building_category_dropdown'))
{
	/**
	 * Get Policy Object - BRG - Object's Item Building Construction category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_BRG_item_building_category_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_MISC_BRG_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Perform tasks that are required before saving a policy objects.
	 * Return the processed data for further computation or saving in DB
	 *
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_MISC_BRG_pre_save_tasks( array $data, $record )
	{
		/**
		 * Format Items
		 */
		$data = _OBJ_MISC_BRG_format_items($data);

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BRG_format_items'))
{
	/**
	 * Format Fire Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_MISC_BRG_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules = _OBJ_MISC_BRG_validation_rules(IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID)['items'];

		$items_formatted = [];
		$count = count($items['sum_insured']);

		for($i=0; $i < $count; $i++)
		{
			$single = [];
			foreach($item_rules as $rule)
			{
				$key = $rule['_key'];
				$single[$key] = $items[$key][$i];
			}
			$items_formatted[] = $single;
		}

		$data['object']['items'] = $items_formatted;

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_BRG_compute_sum_insured_amount'))
{
	/**
	 * Compute Sum Insured Amount of Policy Object - BRG Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_MISC_BRG_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * Sum up all the item's sum insured amount to get the total Sum Insured
		 * Amount
		 */
		$amt_sum_insured 	= 0.00;
		$items = $data['items'] ?? [];
		foreach($items as $single)
		{
			$si_per_item = $single['sum_insured'];
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$amt_sum_insured +=  $si_per_item;
		}

		// NO SI Breakdown for this Portfolio
		return ['amt_sum_insured' => $amt_sum_insured];

	}
}

// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_BRG_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for BRG Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_MISC_BRG_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [
			/**
			 * Premium Validation Rules - Template
			 */
			'premium' => [
	            [
	                'field' => 'premium[rate]',
	                'label' => 'Rate (Rs per Thousand)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
	                '_required' => true
	            ]
			],

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => _ENDORSEMENT_premium_basic_v_rules( $policy_record->portfolio_id, $pfs_record ),

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => _POLICY_INSTALLMENT_validation_rules( $policy_record->portfolio_id, $pfs_record )
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$rules_formatted = [];
			foreach ($validation_rules as $section=> $section_rules)
			{
				$rules_formatted = array_merge($rules_formatted, $section_rules);
			}
			return $rules_formatted;
		}
		return $validation_rules;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_BRG_premium_goodies'))
{
	/**
	 * Get Policy Endorsement Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 *
	 * @return	array
	 */
	function _TXN_MISC_BRG_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_BRG_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_BRG'))
{
	/**
	 *  Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_MISC_BRG($policy_record, $endorsement_record)
	{
		$CI =& get_instance();

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $CI->input->post() )
		{

			/**
			 * Policy Object Record - Latest
			 */
			$policy_object 	= 	_OBJ__get_latest(
									$policy_record->object_id,
									$endorsement_record->txn_type,
									$endorsement_record->audit_object
								);

			$object_attributes  = json_decode($policy_object->attributes);


			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MISC_BRG_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data 		= $CI->input->post();
				$post_premium 	= $post_data['premium'];
				/**
				 * Do we have a valid method?
				 */
				try{

					/**
					 * Sum Insured
					 */
					$SI = floatval($policy_object->amt_sum_insured);

					/**
					 * Compute Premium From Post Data
					 * ------------------------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Base Premium
					 *  b. Direct Discount/Agent Commission
					 *
					 * NOTE: NO POOL PREMIUM
					 */
					$default_rate 	= floatval($post_premium['rate']); // Per Thousand Rate

					// A =  SI X Default Rate
					$A = ( $SI * $default_rate ) / 1000.00;
					$cost_calculation_table[] = [
						'label' => "बिमा शुल्क (रु {$default_rate} प्रति हजार)",
						'value' => $A
					];

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on Basic Premium
					 */

					// NULLIFY Commissionable premium, Agent Commission
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;
						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "प्रत्यक्ष छूट ({$dd_formatted}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}


					// Net Premium
					$NET_BASIC_PREMIUM = $A - $direct_discount;
					$cost_calculation_table[] = [
						'label' => "कुल बिमा शुल्क",
						'value' => $NET_BASIC_PREMIUM
					];

					// -----------------------------------------------------------------------------


					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
						'amt_commissionable'	=> $commissionable_premium,
						'amt_agent_commission'  => $agent_commission,
						'amt_direct_discount' 	=> $direct_discount,
						'amt_pool_premium' 		=> 0.00,
					];


					// -----------------------------------------------------------------------------

					/**
					 * SAVE PREMIUM
					 * --------------
					 */
					return $CI->endorsement_model->save_premium(
								$endorsement_record,
								$policy_record,
								$premium_data,
								$post_data,
								$cost_calculation_table
							);

				} catch (Exception $e){

					return $CI->template->json([
							'status' => 'error',
							'title' => 'Exception Occured',
							'message' => $e->getMessage()
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