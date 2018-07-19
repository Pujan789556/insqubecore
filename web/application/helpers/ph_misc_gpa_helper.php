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
			        'field' => 'object[benefit]',
			        '_key' => 'benefit',
			        'label' => 'बीमा लाभ',
			        'rules' => 'trim|required|max_length[2000]',
			        '_type' => 'textarea',
			        'rows'  => 5,
			        '_required' 	=> true
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
			        'field' => 'object[items][name][]',
			        '_key' => 'name',
			        'label' => 'बीमितको नाम थर',
			        'rules' => 'trim|required|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][position][]',
			        '_key' => 'position',
			        'label' => 'पद/बीमालेख धारकसंगको सम्बन्ध',
			        'rules' => 'trim|required|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][job_nature][]',
			        '_key' => 'job_nature',
			        'label' => 'पेशाको खास प्रकृति',
			        'rules' => 'trim|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[items][dob][]',
			        '_key' => 'dob',
			        'label' => 'जन्म मिति',
			        'rules' => 'trim|max_length[40]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
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
			        'field' => 'object[items][weekly_income][]',
			        '_key' => 'weekly_income',
			        'label' => 'बीमितको साप्ताहिक आय',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[items][nominee][]',
			        '_key' => 'nominee',
			        'label' => 'इच्छाएको ब्यक्तिको नाम थर',
			        'rules' => 'trim|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[items][nominee_relation][]',
			        '_key' => 'nominee_relation',
			        'label' => 'बीमित र इच्छाएको ब्यक्ति बीचको नाता',
			        'rules' => 'trim|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> false
			    ],
			    [
			        'field' => 'object[items][remarks][]',
			        '_key' => 'remarks',
			        'label' => 'कैफियत',
			        'rules' => 'trim|max_length[500]',
			        '_type' => 'textarea',
			        'rows' 	=> 2,
			        '_show_label' 	=> false,
			        '_required' 	=> false
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

		/**
		 * Format Items
		 */
		$data = _OBJ_MISC_GPA_format_items($data);
		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_GPA_format_items'))
{
	/**
	 * Format Fire Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_MISC_GPA_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules = _OBJ_MISC_GPA_validation_rules(IQB_SUB_PORTFOLIO_MISC_GPA_ID)['items'];

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

if ( ! function_exists('_TXN_MISC_GPA_premium_goodies'))
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
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_MISC_GPA($policy_record, $endorsement_record)
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
			 *
			 * In case of endorsements, we will be needing both current policy object and edited object information
			 * to compute premium.
			 */
			$old_object = get_object_from_policy_record($policy_record);
			$new_object = NULL;
			if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
			{
				try {
					$new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
				} catch (Exception $e) {

					return $CI->template->json([
	                    'status'        => 'error',
	                    'title' 		=> 'Exception Occured',
	                    'message' 	=> $e->getMessage()
	                ], 404);
				}
			}

			// Newest object attributes should be used.
			$object_attributes  = json_decode($new_object->attributes ?? $old_object->attributes);

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_MISC_GPA_premium_validation_rules($policy_record, $pfs_record, $old_object, TRUE );
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
					 * NET Sum Insured
					 */
					$SI = _OBJ_si_net($old_object, $new_object);


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
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "प्रत्यक्ष छुट ({$dd_formatted}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}

					// Net Premium
					$NET_BASIC_PREMIUM = $A  - $direct_discount;
					$cost_calculation_table[] = [
						'label' => "कुल बीमाशुल्क",
						'value' => $NET_BASIC_PREMIUM + $POOL_PREMIUM
					];


					/**
					 * Premium Computation and Cost Calculation Table
					 */
					$premium_computation_table 	= json_encode($post_premium);
					$cost_calculation_table 	= json_encode($cost_calculation_table);


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

					/**
					 * Perform Computation Basis for Endorsement
					 */
					if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
					{
						$premium_data = _ENDORSEMENT_apply_computation_basis($policy_record, $endorsement_record, $pfs_record, $premium_data );
					}


					if( $endorsement_record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
					{
						// We do not do anything here, because, VAT was applied only on Stamp Duty
						// For other portfolio, it must be set as -ve value

						/**
						 * !!! NO POOL PREMIUM !!!
						 *
						 * Pool premium is not refunded to customer.
						 * NULLify Pool Premium
						 */
						$premium_data['amt_pool_premium'] = 0.00;

						/**
						 * !!! VAT RETURN !!!
						 *
						 * We must also refund the VAT for as we refund the premium.
						 *
						 */
					}

					/**
					 * Compute VAT
					 *
					 * NOTE: On premium refund, we should also be refunding VAT
					 */
					$taxable_amount = $premium_data['amt_basic_premium'] + $premium_data['amt_pool_premium'] + $post_data['amt_stamp_duty'];
					$CI->load->helper('account');
					$amount_vat = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Other Data
					 */
					$gross_amt_sum_insured 	= $new_object->amt_sum_insured ?? $old_object->amt_sum_insured;
					$net_amt_sum_insured 	= $SI;
					$txn_data = array_merge($premium_data, [
						'gross_amt_sum_insured' => $gross_amt_sum_insured,
						'net_amt_sum_insured' 	=> $net_amt_sum_insured,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $amount_vat,

						'premium_computation_table' => $premium_computation_table,	// JSON encoded
						'cost_calculation_table' 	=> $cost_calculation_table		// JSON encoded
					]);

					return $CI->endorsement_model->save($endorsement_record->id, $txn_data);

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



