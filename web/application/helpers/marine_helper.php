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
			        'label' => 'Tolerance Limit',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][incremental_cost]',
			        '_key' => 'incremental_cost',
			        'label' => 'Incremental Costs(% of Invoice Value)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[sum_insured][duty]',
			        '_key' => 'duty',
			        'label' => 'Duty Amount (payable at arrival)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
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
			        'field' => 'object[risk][clauses]',
			        '_key' => 'clauses',
			        'label' => 'Clauses',
			        'rules' => 'trim|required|max_length[500]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
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
			        '_required' => true
			    ],
			    [
			        'field' => 'object[surveyor][contact_person]',
			        '_key' => 'contact_person',
			        'label' => 'Contact Person',
			        'rules' => 'trim|required|max_length[100]',
			        '_type'     => 'text',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[surveyor][address]',
			        '_key' => 'address',
			        'label' => 'Address',
			        'rules' => 'trim|required|max_length[150]',
			        '_type'     => 'text',
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
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_MARINE_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * @TODO: Finalize the Formula
		 *
		 * Current Assumptions are:
		 * 	- Incremental Cost = (Invoice Value + Tolerance Limit) * X%
		 * 	- Duty Amount in Same Currency too
		 *
		 * Sum Insured = ( (Invoice Value + Tolerance Limit) * (100 + incremental_cost) / 100 ) + Duty Amount
		 */

		$sum_insured_src = $data['sum_insured'];

		$currency 			= $sum_insured_src['currency'];
		$invoice_value 		= $sum_insured_src['invoice_value'];
		$tolerance_limit 	= ( $invoice_value * $sum_insured_src['tolerance_limit'] ) / 100.00;
		$incremental_cost 	= ( $invoice_value +  $tolerance_limit ) * $sum_insured_src['incremental_cost'] / 100.00;
		$duty 				= $sum_insured_src['duty'];

		/**
		 * Let's Compute the Sum Insured Amount in Selected Currency
		 */
		$amt_sum_insured 	= $invoice_value + $tolerance_limit + $incremental_cost + $duty;


		/**
		 * Let's Convert it into NRS
		 */
		$date = date('Y-m-d');
		$amt_sum_insured = forex_conversion($date, $currency, $amt_sum_insured);

		return $amt_sum_insured;
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
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_MARINE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{

		echo '@TODO: _TXN_MARINE_premium_validation_rules'; exit;

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
	                'rules' => 'trim|prep_decimal|decimal|max_length[20]',
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

