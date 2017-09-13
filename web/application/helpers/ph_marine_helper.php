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


if ( ! function_exists('_PO_MARINE_compute_short_term_premium'))
{
	/**
	 * MARINE PORTFOLIO: Compute Short Term Policy Premium
	 *
	 * @param object $policy_record Policy Record
	 * @param object $pfs_record Portfolio Settings Record
	 * @param array $cost_table Cost Table computed by Specific cost table function
	 * @return	array
	 */
	function _PO_MARINE_compute_short_term_premium( $policy_record, $pfs_record, $cost_table )
	{
		echo '@TODO: _PO_MARINE_compute_short_term_premium'; exit;
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



