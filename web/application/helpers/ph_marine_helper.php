<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Marine Portfolio Helper Functions
 *
 * This file contains helper functions related to Marine Portfolio
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


if ( ! function_exists('_OBJ_MARINE_row_snippet'))
{
	/**
	 * Get Policy Object - Marine - Row Snippet
	 *
	 * Row Partial View for Marine Object
	 *
	 * @param object $record Policy Object (Marine)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MARINE_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_marine', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_select_text'))
{
	/**
	 * Get Policy Object - MARINE - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MARINE_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$transit 		= $attributes->transit ?? NULL;

		$snippet = [
			'<strong>' . $transit->from . '</strong>' .
								' - '.
			'<strong>' . $transit->to . '</strong>',

			'Invoice/Date: ' . $transit->invoice_no . '/' . $transit->invoice_date,

			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$transit->bl_no 	? $snippet[] = 'B/L No./Date : ' . $transit->bl_no . '/' . $transit->bl_date : '';
		$transit->lc_no 	? $snippet[] = 'L/C No./Date : ' . $transit->lc_no . '/' . $transit->lc_date : '';
		$transit->vessel 	? $snippet[] = 'Vessel. : ' . $transit->vessel : '';

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_validation_rules'))
{
	/**
	 * Get Policy Object - MARINE - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MARINE_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		$CI->load->helper('forex');

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$mode_of_transit_dropdown 		= _OBJ_MARINE_mode_of_transit_dropdown( FALSE );
		$insurance_cover_type_dropdown 	= _OBJ_MARINE_insurance_cover_type_dropdown( FALSE );
		$deductible_excess_dropdown 	= _OBJ_MARINE_deductible_excess_dropdown( FALSE );
		$invoice_currency_dropdown 		= dropdown_base_currency( FALSE );

		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[date_qn]',
			        '_key' => 'date_qn',
			        'label' => 'Date of Questionnaire',
			        'rules' => 'trim|required|valid_date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_type'             => 'date',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[description]',
			        '_key' => 'description',
			        'label' => 'Description of Goods to be Insured',
			        'rules' => 'trim|required|max_length[200]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[packing]',
			        '_key' => 'packing',
			        'label' => 'Details of Packing',
			        'rules' => 'trim|required|max_length[200]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[marks_numbers]',
			        '_key' => 'marks_numbers',
			        'label' => 'Marks & Numbers',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_required' =>false
			    ],
			    [
			        'field' => 'object[date_dept]',
			        '_key' => 'date_dept',
			        'label' => 'Estimated Date of Departure',
			        'rules' => 'trim|required|valid_date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_type'             => 'date',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[claim_payable_at]',
			        '_key' => 'claim_payable_at',
			        'label' => 'Claim Payable at',
			        'rules' => 'trim|required|max_length[150]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
		    ],


		    /**
		     * Transit Details
		     */
		    'transit' => [
		    	[
			        'field' => 'object[transit][from]',
			        '_key' => 'from',
			        'label' => 'From',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[transit][to]',
			        '_key' => 'to',
			        'label' => 'To',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[transit][mode]',
			        '_key' 	=> 'mode',
			        'label' => 'Mode of Transit',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($mode_of_transit_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $mode_of_transit_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[transit][invoice_no]',
			        '_key' => 'invoice_no',
			        'label' => 'Invoice No.',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[transit][invoice_date]',
			        '_key' => 'invoice_date',
			        'label' => 'Invoice Date',
			        'rules' => 'trim|required|valid_date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_type'             => 'date',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[transit][lc_no]',
			        '_key' => 'lc_no',
			        'label' => 'LC No.',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[transit][lc_date]',
			        '_key' => 'lc_date',
			        'label' => 'LC Date',
			        'rules' => 'trim|valid_date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_type'             => 'date',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[transit][bl_no]',
			        '_key' => 'bl_no',
			        'label' => 'B/L No./C/N No./AW/B No./R/R No. No.',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[transit][bl_date]',
			        '_key' => 'bl_date',
			        'label' => 'B/L No./C/N No./AW/B No./R/R No. Date',
			        'rules' => 'trim|valid_date',
			        '_extra_attributes' => 'data-provide="datepicker-inline"',
			        '_type'             => 'date',
			        '_required' => false
			    ],
			    [
			        'field' => 'object[transit][vessel]',
			        '_key' => 'vessel',
			        'label' => 'Vessel and / or Conveyance',
			        'rules' => 'trim|max_length[200]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => false
			    ]
		    ],

		    /**
		     * Sum Insured Details
		     */
		    'sum_insured' => [
		    	[
			        'field' => 'object[sum_insured][currency]',
			        '_key' => 'currency',
			        'label' => 'Invoice Currency',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($invoice_currency_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $invoice_currency_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][invoice_value]',
			        '_key' => 'invoice_value',
			        'label' => 'Invoice Value',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][tolerance_limit]',
			        '_key' => 'tolerance_limit',
			        'label' => 'Tolerance Limit (%)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][incremental_cost]',
			        '_key' => 'incremental_cost',
			        'label' => 'Incremental Costs(%)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][duty]',
			        '_key' => 'duty',
			        'label' => 'Duty Amount (%)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
			        '_type'     => 'text',
			        '_required' => true
			    ]
		    ],

		    /**
		     * Risks and It's Dependencies
		     */
		    'risk' => [
		    	[
			        'field' => 'object[risk][cover_type]',
			        '_key' 	=> 'cover_type',
			        'label' => 'Type of Insurance Cover',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($insurance_cover_type_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $insurance_cover_type_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[risk][warranties]',
			        '_key' => 'warranties',
			        'label' => 'Warranties',
			        'rules' => 'trim|required|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[risk][deductible_excess]',
			        '_key' => 'deductible_excess',
			        'label' => 'Deductible Excess',
			        'rules' => 'trim|required|numeric|in_list['. implode(',', array_keys($deductible_excess_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $deductible_excess_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[risk][clauses][]',
			        '_key' => 'clauses',
			        'label' => 'Clauses',
			        'rules' => 'trim|required|max_length[500]',
			        '_type'     => 'checkbox-group',
			        '_data' 	=> _OBJ_MARINE_clauses_list(FALSE),
			        '_checkbox_value' 	=> [],
			        '_required' 		=> true
			    ],
		    ],





		    /**
		     * Surveyor Details
		     */
		    'surveyor' => [
		    	[
			        'field' => 'object[surveyor][name]',
			        '_key' => 'name',
			        'label' => 'Surveyor Name',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_default' 	=> "As appointed by CO's Head Office Kathmandu, Nepal",
			        '_required' => true
			    ],
			    [
			        'field' => 'object[surveyor][contact_person]',
			        '_key' => 'contact_person',
			        'label' => 'Contact Person',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_default' 	=> "As arranged by CO's Head Office Kathmandu, Nepal",
			        '_required' => true
			    ],
			    [
			        'field' => 'object[surveyor][address]',
			        '_key' => 'address',
			        'label' => 'Address',
			        'rules' => 'trim|required|max_length[150]',
			        '_type'     => 'text',
			        '_default' 	=> "Kathmandu, Nepal",
			        '_required' => true
			    ]
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

if ( ! function_exists('_OBJ_MARINE_deductible_excess_dropdown'))
{
	/**
	 * Get Policy Object - MARINE - Object's deductible excess dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MARINE_deductible_excess_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'1' 	=> 'Subject to 1% excess on whole consignment',
			'0.5' 	=> 'Subject to 0.5% excess on whole consignment'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_mode_of_transit_dropdown'))
{
	/**
	 * Get Policy Object - MARINE - Object's Item category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MARINE_mode_of_transit_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'AIR' 	=> 'Air',
			'RAIL' 	=> 'Rail',
			'ROAD'	=> 'Road',
			'SEA' 	=> 'Sea',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_insurance_cover_type_dropdown'))
{
	/**
	 * Get Policy Object - MARINE - Type of Insurance Cover Required Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MARINE_insurance_cover_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'A' => 'All Risk',
			'B' => 'Basic Risk',
			'M' => 'Minimum Risk'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - MARINE Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @param string 	$mode 	What to Compute [all|except_duty|duty_only]
	 * @param bool 		$forex_convert 	Convert sum insured into NPR
	 * @return float
	 */
	function _OBJ_MARINE_compute_sum_insured_amount( $portfolio_id, $data, $mode = 'all', $forex_convert = TRUE )
	{
		/**
		 * Compute Sum Insured Amount
		 *
		 * 	A = Invoice Value
		 * 	B = Tolerance Limit (% of Invoice Value)
		 * 	C = A + B
		 * 	D = Incremental Cost (% of C)
		 * 	E = C + D
		 *  F = Duty ( % of E )
		 *
		 * 	SI = E + F
		 */

		$sum_insured_src 	= $data['sum_insured'];
		$currency 			= $sum_insured_src['currency'];

		$A 	= $sum_insured_src['invoice_value'];
		$B 	= ( $A * floatval($sum_insured_src['tolerance_limit']) ) / 100.00;
		$C 	= $A + $B;

		$D 	= ( $C * floatval($sum_insured_src['incremental_cost']) ) / 100.00;
		$E 	= $C + $D;

		$F 	= ( $E * floatval($sum_insured_src['duty']) ) / 100.00;


		/**
		 * We return the sum insured amount as:
		 *
		 * 	1. Complete Sum Insured Amount (all)
		 * 	2. Invoice value + tolerance_limit + Incremental cost (except_duty)
		 * 	3. Duty Amount (duty_only)
		 *
		 * 	2. & 3. are required while computinig premium
		 */
		switch ($mode)
		{
			case 'all':
				$SI = $E + $F;
				break;

			case 'except_duty':
				$SI = $E;
				break;

			case 'duty_only':
				$SI = $F;
				break;

			// Nothing Supplied, Return Complete Sum Insured Amount
			default:
				$SI = $E + $F;
				break;
		}

		if( $forex_convert )
		{
			/**
			 * Let's Convert it into NRS
			 */
			$date = date('Y-m-d');
			$SI = forex_conversion($date, $currency, $SI);
		}

		return $SI;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MARINE_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for MARINE Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_MARINE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{

		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );

		$default_discount_dropdown = _OBJ_MARINE_premium_default_discount_dropdown('label', FALSE);

		$validation_rules = [
			/**
			 * Premium Validation Rules - Template
			 */
			'premium' => [
                [
	                'field' => 'premium[default_rate]',
	                'label' => 'Specified premium rate (as per S.N....of Schedule 6)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'default_rate',
	                '_required' => true
	            ],
	            [
                    'field' => 'premium[default_discount]',
                    'label' => 'Discount(%)',
                    'rules' => 'trim|alpha|in_list['.implode(',', array_keys($default_discount_dropdown)).']',
                    '_key' 		=> 'default_discount',
                    '_type'     => 'dropdown',
                    '_data' 	=> IQB_BLANK_SELECT + $default_discount_dropdown,
                    '_required' => false
                ],
	            [
	                'field' => 'premium[container_discount]',
	                'label' => 'Container discount(%)',
	                'rules' => 'trim|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'container_discount',
	                '_default' 	=> '10',
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[additional_rate1]',
	                'label' => 'Additional premium rate for W & SRCC or SRCC(%)',
	                'rules' => 'trim|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'additional_rate1',
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[additional_rate2]',
	                'label' => 'Additional premium rate for other i.(%)',
	                'rules' => 'trim|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'additional_rate2',
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[additional_rate3]',
	                'label' => 'Additional premium rate for other ii.(%)',
	                'rules' => 'trim|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'additional_rate3',
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[large_sum_insured_discount]',
	                'label' => 'Large Sum Insured Discount(%)',
	                'rules' => 'trim|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'large_sum_insured_discount',
	                '_default' 	=> '20',
	                '_required' => false
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

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_MARINE_premium_goodies'))
{
	/**
	 * Get Policy Policy Transaction Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 *
	 * @return	array
	 */
	function _TXN_MARINE_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MARINE_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_premium_default_discount_dropdown'))
{
	/**
	 * Premium Helper - MARINE - Default Discount dropdown
	 *
	 * @param string $type 	dropdown type [rate list or label list]
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MARINE_premium_default_discount_dropdown( $type="both", $flag_blank_select = true )
	{
		$list = [
			'a' => ['label' => 'Air transit discount(20%)', 'rate' => '20'],
			'b' => ['label' => 'Inland transit within Nepal (limited distance) discount(30%)', 'rate' => '30'],
			'c' => ['label' => 'Inland transit within Nepal discount(25%)', 'rate' => '25'],
			'd' => ['label' => 'Inland transit discount(20%)', 'rate' => '20'],
		];

		$dropdown = [];

		if($type === "both")
		{
			return $list;
		}
		// Label Dropdown
		else if($type === 'label')
		{
			foreach($list as $key=>$detail)
			{
				$dropdown[$key] = $detail['label'];
			}
		}
		// Rate dropdown
		else
		{
			foreach($list as $key=>$detail)
			{
				$dropdown[$key] = $detail['rate'];
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

if ( ! function_exists('__save_premium_MARINE'))
{
	/**
	 * Update Policy Premium Information - Marine
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_MARINE($policy_record, $txn_record)
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
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MARINE_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					$premium_computation_table 	= [];


					/**
					 * Extract Information from Object
					 */
					$object_attributes   = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$SI 				 = floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


					// Get the post premium data
					$post_premium 				= $post_data['premium'];
					$default_rate 				= floatval($post_premium['default_rate']);
					$default_discount 			= $post_premium['default_discount'];
					$container_discount 		= floatval($post_premium['container_discount']);
					$additional_rate1 			= floatval($post_premium['additional_rate1']);
					$additional_rate2 			= floatval($post_premium['additional_rate2']);
					$additional_rate3 			= floatval($post_premium['additional_rate3']);
					$large_sum_insured_discount = floatval($post_premium['large_sum_insured_discount']);

					// A = SI X Default Rate %
					$A = ( $SI * $default_rate ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "A. Specified premium rate({$default_rate}%)",
						'value' => $A
					];


					// B = X% of A
					$B = 0.00;
					$default_discount_label = 'Discount on A (0%)';
					if($default_discount)
					{
						$default_discount_rate 	= _OBJ_MARINE_premium_default_discount_dropdown('rate', FALSE)[$default_discount];
						$default_discount_label = _OBJ_MARINE_premium_default_discount_dropdown('label', FALSE)[$default_discount];
						$B = ( $A * $default_discount_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "B. {$default_discount_label}",
						'value' => $B
					];

					// C = A - B
					$C = $A - $B;
					$cost_calculation_table[] = [
						'label' => "C. (A - B)",
						'value' => $C
					];

					// D = X% of C
					$D = ( $C * $container_discount ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "D. Container discount ({$container_discount}% of C)",
						'value' => $B
					];

					// E = C - D
					$E = $C - $D;
					$cost_calculation_table[] = [
						'label' => "E. (C - D)",
						'value' => $E
					];


					// F = X% of SI
					$F = ( $SI * $additional_rate1 ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "F. Additional premium for W& SRCC or SRCC ({$additional_rate1}%)",
						'value' => $F
					];

					// G = X% of SI
					$G = ( $SI * $additional_rate2 ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "G. Additional premium for Other i. ({$additional_rate2}%)",
						'value' => $G
					];

					// H = X% of SI
					$H = ( $SI * $additional_rate3 ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "H. Additional premium for Other ii. ({$additional_rate3}%)",
						'value' => $H
					];

					// I = E + F + G + H
					$I = $E + $F + $G + $H;
					$cost_calculation_table[] = [
						'label' => "I. Applicable premium rate (E+F+G+H)",
						'value' => $I
					];

					// Applicable Premium Rate (%)
					$APR = ( $I / $SI ) * 100.00;

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on NET Premium
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Business Discount/Direct Discount
						$J = ( $I * $pfs_record->direct_discount ) / 100.00 ;

						$cost_calculation_table[] = [
							'label' => "J. Direct business discount ({$pfs_record->direct_discount}% of I)",
							'value' => $J
						];

						// NULLIFY Commissionable premium, Agent Commission
						$commissionable_premium = NULL;
						$agent_commission = NULL;
					}
					else
					{
						$J = 0.00;
					}


					// K = I - J
					$K = $I - $J;


					// Large Sum Insured Discount
					// L = X% of K
					$L = ( $K * $large_sum_insured_discount ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "L. Large Sum Insured Discount ({$large_sum_insured_discount}%)",
						'value' => $L
					];

					// Net Premium = Applicable Premium
					$NET_PREMIUM = $K - $L;
					$cost_calculation_table[] = [
						'label' => "Premium",
						'value' => $NET_PREMIUM
					];


					/**
					 * Agent Commission if Applies?
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $NET_PREMIUM;
						$agent_commission 		= ( $NET_PREMIUM * $pfs_record->agent_commission ) / 100.00;
					}



					/**
					 * Compute VAT
					 */
					$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Transactional Data
					 *
					 * @TODO: What is Pool Premium Amount?
					 */
					$txn_data = [
						'amt_total_premium' 	=> $NET_PREMIUM,
						'amt_pool_premium' 		=> 0.00,
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
					$premium_computation_table = json_encode($post_premium);
					$txn_data['premium_computation_table'] = $premium_computation_table;


					/**
					 * Cost Calculation Table
					 */
					$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
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

// ------------------------------------------------------------------------
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MARINE_clauses_list'))
{
	/**
	 * Get Policy Object - MARINE - Clauses Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MARINE_clauses_list( $flag_blank_select = true )
	{
		$dropdown = [
			'1' => 'Institute Cargo Clauses A',
			'2' => 'Institute Cargo Clauses B',
			'3' => 'Institute Cargo Clauses C',
			'4' => 'Institute Cargo Clauses (Air)',
			'5' => 'Inland Transit (Rail or Road) Clause A',
			'6' => 'Inland Transit (Rail or Road) Clause B',
			'7' => 'Inland Transit (Rail or Road) Clause C (Fire Risk only)',
			'8' => 'Institute War Clauses (Cargo)',
			'9' => 'Notice of Cancellation and Automatic Termination Clause',
			'10' => 'Institute Strikes Clauses (Cargo)',
			'11' => 'Institute War Cancellation Clause (Cargo)',
			'12' => 'Institute War Clauses ( Air Cargo)',
			'13' => 'Institute Strikes Clauses (Air Cargo)',
			'14' => 'Strikes, Riots and Civil Commotions Clause',
			'15' => 'Institute Classification Clause',
			'16' => 'Pre-despatch Survey Clause',
			'17' => 'Extension of Cover Beyond Seven Days Clause',
			'18' => 'Theft, Pilferage and Non- delivery Clause',
			'19' => 'Non-delivery Clause',
			'20' => 'Water Damage Clause',
			'21' => 'Institute Replacement Clause',
			'22' => 'Institute Replacement Clause (Second Hand Machinery)',
			'23' => 'Duty Insurance Clause',
			'24' => 'Quarantine Insurance Clause',
			'25' => 'Termination of Transit Clause (Terrorissm)'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}



