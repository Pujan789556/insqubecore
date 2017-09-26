<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Boiler Explosion - Engineering Portfolio Helper Functions
 *
 * This file contains helper functions related to Engineering Portfolio
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		Engineering
 * @sub-portfolio 	Boiler Explosion
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


if ( ! function_exists('_PO_ENG_BL_compute_short_term_premium'))
{
	/**
	 * ENGINEERING PORTFOLIO: Compute Short Term Policy Premium
	 *
	 * @param object $policy_record Policy Record
	 * @param object $pfs_record Portfolio Settings Record
	 * @param array $cost_table Cost Table computed by Specific cost table function
	 * @return	array
	 */
	function _PO_ENG_BL_compute_short_term_premium( $policy_record, $pfs_record, $cost_table )
	{
		echo '@TODO: _PO_ENG_BL_compute_short_term_premium'; exit;
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


if ( ! function_exists('_OBJ_ENG_BL_row_snippet'))
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
	function _OBJ_ENG_BL_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_eng_bl', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_BL_select_text'))
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
	function _OBJ_ENG_BL_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$transit 		= $attributes->transit ?? NULL;

		$snippet = [
			'<strong>' . $attributes->risk_locaiton . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_BL_validation_rules'))
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
	function _OBJ_ENG_BL_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;
		$tbl_liabilities_dropdown =_OBJ_ENG_BL_thirdparty_liability_dropdown(FALSE);

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
			        'field' => 'document',
			        '_key' => 'document',
			        'label' => 'Upload Item List File (.xls or .xlsx)',
			        'rules' => '',
			        '_type'     => 'file',
			        '_required' => false
			    ],
		    ],


		    /**
		     * Item Details
		     */
		    'items' => [
		    	[
			        'field' => 'object[items][sn][]',
			        '_key' => 'sn',
			        'label' => 'S.N.',
			        'rules' => 'trim|required|integer|max_length[5]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][description][]',
			        '_key' => 'description',
			        'label' => 'Description',
			        'rules' => 'trim|required|htmlspecialchars|max_length[250]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][regd_no][]',
			        '_key' => 'regd_no',
			        'label' => 'Registration No.',
			        'rules' => 'trim|required|htmlspecialchars|max_length[100]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][make_year][]',
			        '_key' => 'make_year',
			        'label' => 'Make Year',
			        'rules' => 'trim|required|integer|max_length[4]',
			        '_type' => 'text',
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
			/**
			 * If excel file uploaded, we do not need 'items' validation rules
			 */
			if( $CI->input->post() && !empty($_FILES['document']['name']) )
			{
				unset($v_rules['items']);
			}

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

if ( ! function_exists('_OBJ_ENG_BL_thirdparty_liability_dropdown'))
{
	/**
	 * Third party liabilities dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_ENG_BL_thirdparty_liability_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_ENG_BL_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Perform tasks that are required before saving a policy objects.
	 * Return the processed data for further computation or saving in DB
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_ENG_BL_pre_save_tasks( array $data )
	{
		/**
		 * Task : Read Excel File from Upload and Render into data list
		 *
		 * !!! NOTE - We are not going to save this document as it is
		 * 			  only used to extract the item list
		 */

		$CI =& get_instance();


		$filename = $_FILES['document']['name'];
		if( $filename  )
		{
			if( !is_valid_file_extension($filename, ['xls', 'xlsx']) )
			{
				throw new Exception("Exception [Helper: ph_eng_bl_helper][Method: _OBJ_ENG_BL_pre_save_tasks()]: Invalid file uploaded. Please upload only excel file(.xls or .xlsx).");
			}
			else
			{
				$tmp_file = $_FILES['document']['tmp_name'];

				/**
				 * Get excel data into array
				 */
				$excel_data = excel_to_array($tmp_file);

				/**
				 * Excel Data Structure Must follow this structure
				 *
				[1] => Array
		        (
		            [A] => S.N
		            [B] => Description
		            [C] => Regd. No
		            [D] => Year of Make
		            [E] => Sum Insured
		        )
		        */

		        // Remove Header Row
		        array_shift($excel_data);

		        /**
		         * Format data to save into JSON Object Items
		         */
		        $excel_columns = [ 'A'=>'sn', 'B' => 'description', 'C' => 'regd_no', 'D' => 'make_year', 'E' => 'sum_insured' ];
		        $items = [];
		        foreach($excel_data as $row)
		        {
		        	/**
		        	 * At least you need to have description and sum_insured amount filled
		        	 */
		        	if( !empty($row['B']) && !empty($row['E']) )
		        	{
		        		foreach($excel_columns as $col_index => $item_key)
			        	{
			        		$col_value = $row[$col_index] ?? NULL;

			        		// If Sum Insured Column, Get Clean DECIMAL Value.
			        		if($col_index === 'E')
			        		{
			        			// Remove all formatting except fractional part
								$col_value 	= (float) filter_var($col_value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			        		}
			        		$items[$item_key][] = $col_value;
			        	}
		        	}
		        }

		        if( !$items )
		        {
		        	throw new Exception("Exception [Helper: ph_eng_bl_helper][Method: _OBJ_ENG_BL_pre_save_tasks()]: Excel file does not contain valid data.");
		        }

		        /**
		         * Add Items into the data
		         */
		        $data['object']['items'] = $items;
			}
		}
		return $data;
	}
}





// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_BL_compute_sum_insured_amount'))
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
	function _OBJ_ENG_BL_compute_sum_insured_amount( $portfolio_id, $data )
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

if ( ! function_exists('_OBJ_ENG_BL_compute_tpl_amount'))
{
	/**
	 * Get Third Party Liability of Policy Object - ENGINEERING Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param array 	$data 	TPL data array
	 * @return float
	 */
	function _OBJ_ENG_BL_compute_tpl_amount( $data )
	{
		$total_tpl_amount 	= 0.00;

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

if ( ! function_exists('_TXN_ENG_BL_premium_validation_rules'))
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
	function _TXN_ENG_BL_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );


		$validation_rules = [
			/**
			 * Premium Validation Rules - Template
			 */
			'premium' => [
                [
	                'field' => 'premium[default_rate]',
	                'label' => 'Default Premium Rate',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'default_rate',
	                '_required' => true
	            ],
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
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------



