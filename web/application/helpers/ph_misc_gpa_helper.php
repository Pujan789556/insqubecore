<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube MISCELLANEOUS Portfolio Helper Functions
 *
 * This file contains helper functions related to MISCELLANEOUS Portfolio
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		MISCELLANEOUS
 * @sub-portfolio 	Group Personnel Accident(GPA)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_GPA_row_snippet'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Row Snippet
	 *
	 * Row Partial View for MISCELLANEOUS Object
	 *
	 * @param object $record Policy Object (MISCELLANEOUS)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_MISC_GPA_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_gpa', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_GPA_select_text'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_MISC_GPA_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$item_count = count($attributes->items->sum_insured ?? []);

		$snippet = [
			'Insured Personnel Count: ' . '<strong>' . $item_count . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_GPA_validation_rules'))
{
	/**
	 * Get Policy Object - MISCELLANEOUS - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_MISC_GPA_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
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
			        'field' => 'object[items][name][]',
			        '_key' => 'name',
			        'label' => 'बीमितको नाम थर',
			        'rules' => 'trim|required|htmlspecialchars|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][position][]',
			        '_key' => 'position',
			        'label' => 'पद/बीमालेख धारकसंगको सम्बन्ध',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][job_nature][]',
			        '_key' => 'job_nature',
			        'label' => 'पेशाको खास प्रकृति',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][dob][]',
			        '_key' => 'dob',
			        'label' => 'जन्म मिति',
			        'rules' => 'trim|required|max_length[40]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][sum_insured][]',
			        '_key' => 'sum_insured',
			        'label' => 'बीमांक रकम (रु)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][benefit][]',
			        '_key' => 'benefit',
			        'label' => 'लाभको समूह',
			        'rules' => 'trim|required|max_length[40]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][weekly_income][]',
			        '_key' => 'weekly_income',
			        'label' => 'बीमितको साप्ताहिक आय',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][nominee][]',
			        '_key' => 'nominee',
			        'label' => 'इच्छाएको ब्यक्तिको नाम थर',
			        'rules' => 'trim|required|htmlspecialchars|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][nominee_relation][]',
			        '_key' => 'nominee_relation',
			        'label' => 'बीमित र इच्छाएको ब्यक्ति बीचको नाता',
			        'rules' => 'trim|required|htmlspecialchars|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][remarks][]',
			        '_key' => 'remarks',
			        'label' => 'कैफियत',
			        'rules' => 'trim|htmlspecialchars|max_length[500]',
			        '_type' => 'textarea',
			        'rows' 	=> 2,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
		    ]
		];


		// return formatted?
		$fromatted_v_rules = [];
		if($formatted === TRUE)
		{
			/**
			 * If excel file uploaded, we do not need any validation rules!
			 * But to run the function "$this->form_validation->run()", let's
			 * build a dummy validation rule
			 */
			if( $CI->input->post() && !empty($_FILES['document']['name']) )
			{
				unset($v_rules);
				$v_rules['dummy'] = [
					[
				        'field' => 'dummy',
				        '_key' => 'dummy',
				        'rules' => 'alpha',
				    ]
				];
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

if ( ! function_exists('_OBJ_MISC_GPA_item_headings_dropdown'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_GPA_item_headings_dropdown( )
	{
		$items = _OBJ_MISC_GPA_validation_rules(IQB_SUB_PORTFOLIO_MISC_GPA_ID)['items'];
	    $dropdown = [];
	    foreach($items as $item)
	    {
	    	$dropdown[$item['_key']] = $item['label'];
	    }

	    return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_GPA_item_excel_columns'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_GPA_item_excel_columns( )
	{
		return [
			'A' => 'name',
			'B' => 'position',
			'C' => 'job_nature',
			'D' => 'dob',
			'E' => 'sum_insured',
			'F' => 'benefit',
			'G' => 'weekly_income',
			'H' => 'nominee',
			'I' => 'nominee_relation',
			'J' => 'remarks'
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_GPA_pre_save_tasks'))
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
	function _OBJ_MISC_GPA_pre_save_tasks( array $data, $record = NULL )
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
				throw new Exception("Exception [Helper: ph_misc_gpa_helper][Method: _OBJ_MISC_GPA_pre_save_tasks()]: Invalid file uploaded. Please upload only excel file(.xls or .xlsx).");
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
		            [A] => Insured Name
		            [B] => Position
		            [C] => Job Nature
		            [D] => DOB
		            [E] => Sum Insured
		            [F] => Benefit
		            [G] => Weekly Income
		            [H] => Nominee
		            [I] => Nominee Relation
		            [J] => Remarks
		        )
		        */

		        // Remove Header Row
		        array_shift($excel_data);

		        /**
		         * Format data to save into JSON Object Items
		         */
		        $excel_columns = _OBJ_MISC_GPA_item_excel_columns();
		        $items = [];
		        foreach($excel_data as $row)
		        {
		        	/**
		        	 * At least you need to have "Name" and "Sum Insured" Column
		        	 */
		        	if( !empty($row['A']) && !empty($row['E']) )
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
		        	throw new Exception("Exception [Helper: ph_misc_gpa_helper][Method: _OBJ_MISC_GPA_pre_save_tasks()]: Excel file does not contain valid data.");
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

if ( ! function_exists('_OBJ_MISC_GPA_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object - MISCELLANEOUS Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer 	$portfolio_id  Portfolio ID
	 * @param array 	$data 	Object Data
	 * @param string 	$mode 	What to Compute [all|except_duty|duty_only]
	 * @param bool 		$forex_convert 	Convert sum insured into NPR
	 * @return float
	 */
	function _OBJ_MISC_GPA_compute_sum_insured_amount( $portfolio_id, $data )
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
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_GPA_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for MISCELLANEOUS Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_MISC_GPA_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
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
	                'label' => 'Default Premium Rate (Per Thousand in Rs)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_key' 		=> 'default_rate',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[pool_rate]',
	                'label' => 'Pool Premium Rate (Per Thousand in Rs)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_key' 		=> 'pool_rate',
	                '_required' => true
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

if ( ! function_exists('_TXN_MISC_GPA_premium_goodies'))
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
	function _TXN_MISC_GPA_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_GPA_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_GPA'))
{
	/**
	 * Update Policy Premium Information - MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_MISC_GPA($policy_record, $txn_record)
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
			$validation_rules = _TXN_MISC_GPA_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					 * 	A. Object Atrributes
					 * 	B. Sum Insured Amount
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$SI 				= floatval($policy_object->amt_sum_insured); 	// Sum Insured Amount


					/**
					 * Get post premium Data
					 * 	a. Default Rate (X Rs per Thousand)
					 * 	b. Pool Premium Rate (Y Rs per Thousand)
					 */
					$post_premium 				= $post_data['premium'];
					$default_rate 				= floatval($post_premium['default_rate']);
					$pool_rate 					= floatval($post_premium['pool_rate']);

					// SI in Thousands
					$SIK = $SI / 1000;

					// A =  SIK X Default Rate
					$A = $SIK * $default_rate;
					$cost_calculation_table[] = [
						'label' => "जोखिम समूह वाहेकको बीमाशुल्क",
						'value' => $A
					];

					// B = SIK X Pool Rate
					$B = $SIK * $pool_rate;
					$cost_calculation_table[] = [
						'label' => "जोखिम समूहको बीमाशुल्क",
						'value' => $B
					];
					$POOL_PREMIUM = $B;

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on Basic Premium
					 */
					$C = 0.00;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$C = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						// NULLIFY Commissionable premium, Agent Commission
						$commissionable_premium = NULL;
						$agent_commission = NULL;
					}
					else
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}

					$cost_calculation_table[] = [
						'label' => "प्रत्यक्ष छुट ({$pfs_record->direct_discount}%)",
						'value' => $C
					];


					// Net Premium
					$NET_PREMIUM = $A + $B - $C;
					$cost_calculation_table[] = [
						'label' => "कुल बीमाशुल्क",
						'value' => $NET_PREMIUM
					];


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
						'amt_pool_premium' 		=> $POOL_PREMIUM,
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


