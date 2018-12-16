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
		$excess_dropdown 			= _OBJ_ENG_EAR_excess_dropdown(FALSE);


		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[principal]',
			        '_key' => 'principal',
			        'label' => 'Name & Address of Pricipal',
			        'rules' => 'trim|required|max_length[1000]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'Location of Risk',
			        'rules' => 'trim|required|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[contract_title]',
			        '_key' => 'contract_title',
			        'label' => 'Contract Title',
			        'rules' => 'trim|required|max_length[250]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[contract_no]',
			        '_key' => 'contract_no',
			        'label' => 'Contract No.',
			        'rules' => 'trim|required|max_length[100]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[testing_period]',
			        '_key' => 'testing_period',
			        'label' => 'Testing Period (Months)',
			        'rules' => 'trim|integer|max_length[11]',
			        '_type' => 'text',
			        '_required' 	=> false
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
			        'rules' => 'trim|required|max_length[10]',
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
			        '_default' => 0,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][deductibles][]',
			        '_key' => 'deductibles',
			        'label' => 'Deductibles',
			        'rules' => 'trim|max_length[500]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ]
		    ],

		    /**
		     * Risk Deductible
		     */
		    'risk' => [
		    	[
			        'field' => 'object[risk][deductibles]',
			        '_key' => 'deductibles',
			        'label' => 'Risk Deductible',
			        'rules' => 'trim|required|max_length[100]',
			        '_type' => 'text',
			        '_default' => '__% of claim amount subject to minimum of Rs. ______.',
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
			        '_default' 	=> 0,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[third_party][deductibles][]',
			        '_key' => 'deductibles',
			        'label' => 'Deductibles',
			        'rules' => 'trim|max_length[150]',
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
			        'label' => 'Limit per event',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_default' 	=> 'Per event limit is restricted upto Rs. ___ only.',
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

				/**
				 * Not Needed - Suggested by Rama
				 *
				'I1.a.ii' => '1.a.ii Landed Cost of Imported machinery as at Factory site - Freight insurance, handling, Clearing & forwarding charges upto factory site',
				'I1.a.iii' => '1.a.iii Landed Cost of Imported machinery as at Factory site - Customs duty',

				'I1.b.i' => '1.b.i On machinery fabricated or manufactured in India - Invoice cost including insurance, handling clearing and transport, upto Factory site',
				'I1.b.ii' => '1.b.ii On machinery fabricated or manufactured in India - Freight.',

				'I1.c' => '1.c On Cost of Erection including salaries of all Foreign and Indian Technicians and Wages of all skilled and unskilled labour employed at Factory Site during erection.',

				'I1.d.i' => '1.d.i On Building in which the above Plant and Machinery is to be erected - Permanent Civil Engineering Works',
				'I1.d.ii' => '1.d.ii On Building in which the above Plant and Machinery is to be erected - Temporary Works'
				*/
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

if ( ! function_exists('_OBJ_ENG_EAR_excess_dropdown'))
{
	/**
	 * Excess dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_ENG_EAR_excess_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'j.a' 	=> 'A. For storage & erection claims',
			'j.b' 	=> 'B. For testing period claims',
			'j.c' 	=> 'For Acts of God claims (as per Memo 6)',
			'j.c' 	=> 'For fire/explosion claims'
		];

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
	 * @return float
	 */
	function _OBJ_ENG_EAR_compute_sum_insured_amount( $portfolio_id, $data )
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

if ( ! function_exists('_OBJ_ENG_EAR_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * FORMAT Object ITEMS
	 *
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_ENG_EAR_pre_save_tasks( array $data, $record = NULL )
	{

		$items 		= $data['object']['items'];
		$item_rules = _OBJ_ENG_EAR_validation_rules(IQB_SUB_PORTFOLIO_ENG_EAR_ID)['items'];

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
		$insured_items_dropdown = _OBJ_ENG_EAR_insured_items_dropdown(FALSE);

		$validation_rules = [
			/**
			 * Itemwise Premium Rates
			 */
			'items' => [
				[
			        'field' => 'premium[items][sn][]',
			        '_key' => 'sn',
			        'label' => 'Item Title',
			        'rules' => 'trim|required|max_length[10]',
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
	                '_default' 	=> 0,
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
	                '_default' 	=> 0,
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

if ( ! function_exists('_TXN_ENG_EAR_premium_goodies'))
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
	function _TXN_ENG_EAR_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_ENG_EAR_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
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

		$rates 				= $data['rate'];
		$total_premium_by_item 	= 0.00;

		$count = count($items);
		for($i = 0; $i < $count; $i++ )
		{
			$per_item_rate 	= floatval($rates[$i] ?? 0);
			$per_item_si 	= floatval($items[$i]->sum_insured ?? 0);

			$total_premium_by_item += ( $per_item_si * $per_item_rate ) / 100.00;
		}

		return $total_premium_by_item;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_ENG_EAR'))
{
	/**
	 * Update Policy Premium Information - ENGINEERING - ERECTION ALL RISK
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_ENG_EAR($policy_record, $endorsement_record)
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

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


			/**
			 * !!! MANUAL PREMIUM COMPUTATION ENDORSEMENT !!!
			 *
			 * Manual Endorsement should be done on
			 * 	- Premium Upgrade
			 * 	- Premium Refund
			 */
			if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
			{
				return _ENDORSEMENT__save_premium_manual($endorsement_record->id, $pfs_record->agent_commission);
			}


			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_ENG_EAR_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					 */
					$cost_calculation_table 	= [];


					/**
					 * Extract Information from Object
					 * 	A. Object Atrributes
					 * 	B. Sum Insured Amount
					 * 	C. Third party liability Amount
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount
					$TPL_AMOUNT 		= _OBJ_ENG_EAR_compute_tpl_amount($object_attributes->third_party->limit ?? []);


					/**
					 * Get post premium Data
					 * 	a. Default Premium (All items in total)
					 * 	b. Third party Rate
					 * 	c. Pool Premium Flag
					 * 	d. Other common data
					 */
					$post_premium 				= $post_data['premium'];
					$items_premium 				= _OBJ_ENG_EAR_compute_premium_total_by_items($post_premium['items'], $object_attributes->items);
					$tp_rate 					= floatval($post_premium['tp_rate']);
					$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


					// A = Default Premium for all item
					$A = $items_premium;
					$cost_calculation_table[] = [
						'label' => "Basic Premium",
						'value' => $A
					];

					// B = TP X TP Rate %
					$B = ( $TPL_AMOUNT * $tp_rate ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "Third Party Premium",
						'value' => $B
					];

					// C = A + B
					$C = $A + $B;



					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on NET Premium
					 */
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $C * $pfs_record->direct_discount ) / 100.00 ;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "Direct discount ({$dd_formatted}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $C;
						$agent_commission 		= ( $C * $pfs_record->agent_commission ) / 100.00;
					}

					// E = C - Direct Discount
					$E = $C - $direct_discount;


					/**
					 * Pool Premium
					 */
					$POOL_PREMIUM 	= 0.00;
					$pool_rate 		= 0.00;
					if($flag_pool_risk)
					{
						// Pool Premium = x% of Default SI (A - 2. Clearance & Removal of Debris )
						$pool_rate = floatval($pfs_record->pool_premium);

						// Debris Premium
						$debris_record = _OBJ_ENG_EAR_get_debris_record($object_attributes->items); // SN = I2
						$si_debris 		= floatval($debris_record->sum_insured);

						$POOL_PREMIUM = ( ($SI - $si_debris) * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "Pool Premium",
						'value' => $POOL_PREMIUM
					];

					$NET_BASIC_PREMIUM = $E;
					$cost_calculation_table[] = [
						'label' => "Total Premium",
						'value' => $NET_BASIC_PREMIUM + $POOL_PREMIUM
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
						'amt_pool_premium' 		=> $POOL_PREMIUM,
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

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EAR_get_debris_record'))
{
	/**
	 * Get Debris Record from ITEM set
	 *
	 * @param array 	$items 	Array of items objects
	 * @return float
	 */
	function _OBJ_ENG_EAR_get_debris_record( $items )
	{
		$record = NULL;
		foreach($items as $item_record)
		{
			if($item_record->sn == 'I2')
			{
				$record = $item_record;
				break;
			}
		}

		return $record;
	}
}

// ------------------------------------------------------------------------
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------



