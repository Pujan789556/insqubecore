<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Erection All Risk - Engineering Portfolio Helper Functions
 *
 * This file contains helper functions related to Engineering Portfolio
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		Engineering
 * @sub-portfolio 	Erection All Risk
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_ENG_EAR_row_snippet'))
{
	/**
	 * Get Policy Object - ENGINEERING - Row Snippet
	 *
	 * Row Partial View for ENGINEERING Object
	 *
	 * @param object $record Policy Object (ENGINEERING)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_ENG_EAR_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_eng_ear', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_select_text'))
{
	/**
	 * Get Policy Object - ENGINEERING - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_ENG_EAR_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

		$snippet = [
			'<strong>' . $attributes->risk_locaiton . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_validation_rules'))
{
	/**
	 * Get Policy Object - ENGINEERING - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_ENG_EAR_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$tbl_liabilities_dropdown 	= _OBJ_ENG_EAR_thirdparty_liability_dropdown(FALSE);
		$insured_items_dropdown 	= _OBJ_ENG_EAR_insured_items_dropdown(FALSE);

		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
			    [
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'Location of Risk',
			        'rules' => 'trim|required|htmlspecialchars|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[maintenance_period]',
			        '_key' => 'maintenance_period',
			        'label' => 'Maintenance Period (Months)',
			        'rules' => 'trim|required|integer|max_length[11]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
		    ],


		    /**
		     * Item Details
		     */
		    'items' => [
		    	[
			        'field' => 'object[items][sn][]',
			        '_key' => 'sn',
			        'label' => 'Item Title',
			        'rules' => 'trim|required|htmlspecialchars|max_length[10]',
			        '_type' => 'hidden',
			        '_data' 		=> $insured_items_dropdown,
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
			    ],
			    [
			        'field' => 'object[items][deductibles][]',
			        '_key' => 'deductibles',
			        'label' => 'Deductibles',
			        'rules' => 'trim|htmlspecialchars|max_length[500]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
		    ],

		    /**
		     * Risk Deductible
		     */
		    'risk' => [
		    	[
			        'field' => 'object[risk][deductibles]',
			        '_key' => 'deductibles',
			        'label' => 'Risk Deductible',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_default' => 'Rs. _____ of EAR only.',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ]
		    ],

		    /**
		     * Third party Liability
		     */
		    'third_party' => [
		    	[
			        'field' => 'object[third_party][liability][]',
			        '_key' => 'liability',
			        'label' => 'Insured Items',
			        'rules' => 'trim|required|alpha_dash|in_list['. implode(',', array_keys($tbl_liabilities_dropdown)) .']',
			        '_type'     => 'hidden',
			        '_data' 		=> $tbl_liabilities_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
		    	[
			        'field' => 'object[third_party][limit][]',
			        '_key' => 'limit',
			        'label' => 'Limit of Indemnity (Rs.)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[third_party][deductibles][]',
			        '_key' => 'deductibles',
			        'label' => 'Deductibles',
			        'rules' => 'trim|htmlspecialchars|max_length[150]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ]
		    ],

		    /**
		     * Other Information
		     */
		    'others' => [
		    	[
			        'field' => 'object[others][limit_per_event]',
			        '_key' => 'limit_per_event',
			        'label' => 'Per Event Limit (Rs.)',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[others][show_limit_per_event]',
			        '_key' => 'show_limit_per_event',
			        'label' => 'Show Per Event Limit',
			        'rules' => 'trim|integer|exact_length[1]|in_list[1]',
			        '_type'     => 'checkbox',
			        '_checkbox_value' 	=> '1',
			        '_required' => false
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

if ( ! function_exists('_OBJ_ENG_EAR_insured_items_dropdown'))
{
	/**
	 * Third party liabilities dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_ENG_EAR_insured_items_dropdown( $flag_blank_select = true, $group_section = true )
	{
		$dropdown = [
			'1. Plant & Equipments to be erected' => [
				'I1.a.i' => '1.a.i Landed Cost of Imported machinery as at Factory site - Invoice Cost',
				'I1.a.ii' => '1.a.ii Landed Cost of Imported machinery as at Factory site - Freight insurance, handling, Clearing & forwarding charges upto factory site',
				'I1.a.iii' => '1.a.iii Landed Cost of Imported machinery as at Factory site - Customs duty',

				'I1.b.i' => '1.b.i On machinery fabricated or manufactured in India - Invoice cost including insurance, handling clearing and transport, upto Factory site',
				'I1.b.ii' => '1.b.ii On machinery fabricated or manufactured in India - Freight.',

				'I1.c' => '1.c On Cost of Erection including salaries of all Foreign and Indian Technicians and Wages of all skilled and unskilled labour employed at Factory Site during erection.',

				'I1.d.i' => '1.d.i On Building in which the above Plant and Machinery is to be erected - Permanent Civil Engineering Works',
				'I1.d.ii' => '1.d.ii On Building in which the above Plant and Machinery is to be erected - Temporary Works'
			],

			'2. Clearance & Removal of Debris' => [
				'I2' => 'Clearance & Removal of Debris'
			],

			'3. Value of Surrounding Property to be covered' =>[
				'I3' => '3. Value of Surrounding Property to be covered'
			],

			'4. Construction Plant and Machinery' =>[
				'I4' => '4. Construction Plant and Machinery to be used at the Project Site (Detailed as per attached list)'
			],

			'5. Extra charges for express freight' =>[
				'I5' => '5. Extra charges for express freight (excluding Air Freight), overtime, Sunday and Holiday rates or wages'
			],

			'6. On increased replacement value' => [
				'I6.a' => '6.a On increased replacement value (including duty on such additional replacement value) which may have to be paid on replacement of imported Plant and Machinery as per Item 1-(a) above.',
				'I6.b' => '6.b On increased replacement value which may have to be paid on replacement of Indigenous Plant and Machinery as per Item 1-(b) above.',
			]
		];

		if( !$group_section )
		{
			$dd = [];
			foreach( $dropdown as $title=>$group_items )
			{
				$dd = array_merge($dd, $group_items);
			}
			$dropdown = $dd;
		}

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_thirdparty_liability_dropdown'))
{
	/**
	 * Third party liabilities dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_ENG_EAR_thirdparty_liability_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'tpl_injdth' 				=> '1. Bodily Injury/Death',
			'tpl_injdth_per_person' 	=> '1.1. Any One Person',
			'tpl_property'				=> '2. Property'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}



// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - ENGINEERING Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @param string 	$mode 	What to Compute [all|except_duty|duty_only]
	 * @param bool 		$forex_convert 	Convert sum insured into NPR
	 * @return float
	 */
	function _OBJ_ENG_EAR_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * Sum up all the item's sum insured amount to get the total Sum Insured
		 * Amount
		 */
		$items_sum_insured 	= $data['items']['sum_insured'] ?? [];
		$amt_sum_insured 	= 0.00;

		foreach($items_sum_insured as $si_per_item)
		{
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$amt_sum_insured +=  $si_per_item;
		}
		return $amt_sum_insured;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_compute_tpl_amount'))
{
	/**
	 * Get Third Party Liability of Policy Object - ENGINEERING Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param array 	$data 	TPL data array
	 * @return float
	 */
	function _OBJ_ENG_EAR_compute_tpl_amount( $data )
	{
		$total_tpl_amount 	= 0.00;

		// We do not compute 1.2 on Total ( which is the second item on the list)
		unset($data[1]);

		foreach($data as $si_per_item)
		{
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$total_tpl_amount +=  $si_per_item;
		}

		return $total_tpl_amount;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_ENG_EAR_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for ENGINEERING Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_ENG_EAR_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');

		$template_dropdown 			= $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );
		$insured_items_dropdown 	= _OBJ_ENG_EAR_insured_items_dropdown(FALSE);

		$validation_rules = [
			/**
			 * Itemwise Premium Rates
			 */
			'items' => [
				[
			        'field' => 'premium[items][sn][]',
			        '_key' => 'sn',
			        'label' => 'Item Title',
			        'rules' => 'trim|required|htmlspecialchars|max_length[10]',
			        '_type' => 'hidden',
			        '_data' 		=> $insured_items_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
                [
	                'field' => 'premium[items][rate][]',
	                'label' => 'Default Premium Rate (%)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
	                '_required' => true
	            ]
			],

			/**
			 * Third party and pool premium
			 */
			'tppl' => [
				[
	                'field' => 'premium[tp_rate]',
	                'label' => 'Third Party Liability Rate',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'tp_rate',
	                '_required' => true
	            ],
	            [
                    'field' => 'premium[flag_pool_risk]',
                    'label' => 'Pool Risk',
                    'rules' => 'trim|integer|in_list[1]',
                    '_key' 		=> 'flag_pool_risk',
                    '_type'     => 'checkbox',
                    '_checkbox_value' 	=> '1',
                    '_required' => false,
                ]
			],

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
			]
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


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_compute_premium_total_by_items'))
{
	/**
	 * Compute Default total premium of insured items.
	 *
	 * @param array 	$data 	POST item's premium data array with sn and rate
	 * @return float
	 */
	function _OBJ_ENG_EAR_compute_premium_total_by_items( $data, $items )
	{

		$sum_insured_list 	= $items->sum_insured;
		$rates 				= $data['rate'];

		$total_premium_by_item 	= 0.00;

		$count = count($sum_insured_list);
		for($i = 0; $i < $count; $i++ )
		{
			$per_item_rate 	= floatval($rates[$i] ?? 0);
			$per_item_si 	= floatval($sum_insured_list[$i] ?? 0);

			$total_premium_by_item += ( $per_item_si * $per_item_rate ) / 100.00;
		}

		return $total_premium_by_item;
	}
}

// ------------------------------------------------------------------------
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------


