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
 * @sub-portfolio 	HEALTH INSURANCE (HI)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_HI_row_snippet'))
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
	function _OBJ_MISC_HI_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_hi', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_select_text'))
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
	function _OBJ_MISC_HI_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$item_count = count($attributes->items->sum_insured ?? []);

		$snippet = [
			'Insured Staff Count: ' . '<strong>' . $item_count . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_validation_rules'))
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
	function _OBJ_MISC_HI_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'document',
			        '_key' => 'document',
			        'label' => 'Upload Staff List File (.xls or .xlsx)',
			        'rules' => '',
			        '_type'     => 'file',
			        '_required' => false
			    ],
		    ],
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

if ( ! function_exists('_OBJ_MISC_HI_item_headings_dropdown'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_HI_item_headings_dropdown( )
	{
		return [
			'sn' 			=> 'S.N.',
			'name' 			=> 'Employee Name',
			'designation' 	=> 'Designation',
			'age' 			=> 'Age',
			'sex' 			=> 'Sex',
			'family_details' 	=> 'Family Members',
			'sum_insured' 		=> 'Sum Insured (Rs)',
			'premium' 			=> 'Premium (Rs)',
			'medical_group_nr' 	=> 'Medical Group Number'
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_item_excel_columns'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_HI_item_excel_columns( )
	{
		return [
			'A' => 'sn',
			'B' => 'name',
			'C' => 'designation',
			'D' => 'age',
			'E' => 'sex',
			'F' => 'family_details',
			'G' => 'sum_insured',
			'H' => 'premium',
			'I' => 'medical_group_nr'
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_pre_save_tasks'))
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
	function _OBJ_MISC_HI_pre_save_tasks( array $data, $record = NULL )
	{
		/**
		 * Task : Upload Excel File of Item List, and save
		 * 		  the name back to Object attributes to access it later
		 *
		 */
		$attributes 	= json_decode($record->attributes ?? NULL);
		$old_document 	= $attributes->document ?? NULL;


		$options = [
			'config' => [
				'encrypt_name' 	=> TRUE,
                'upload_path' 	=> Objects::$upload_path,
                'allowed_types' => 'xls|xlsx',
                'max_size' 		=> '4098'
			],
			'form_field' => 'document',

			'create_thumb' => FALSE,

			// Delete Old file
			'old_files' => $old_document ? [$old_document] : [],
			'delete_old' => TRUE
		];
		$upload_result = upload_insqube_media($options);

		$status 		= $upload_result['status'];
		$message 		= $upload_result['message'];
		$files 			= $upload_result['files'];

		if( $status === 'success' )
        {
        	$document = $files[0];
        	$data['object']['document'] = $document;


        	/**
        	 * Build Staff List (Items)
        	 *
        	 * !!! This is required to compute Premium Amount, Sum Insured Amount
        	 */
        	$excel_file = Objects::$upload_path . $document;
        	$items 		= _OBJ_MISC_HI_excel_to_items( $excel_file );
        	$data['object']['items'] = $items;
        }

        /**
         * No File Selected in Edit Mode, Use the old one
         */
        else if( $status === 'no_file_selected' &&  isset($record->id) )
        {
			// Old Document as it is
			$data['object']['document'] = $old_document;
        }
        else{
        	throw new Exception("Exception [Helper: ph_eng_eei_helper][Method: _OBJ_ENG_EEI_pre_save_tasks()]: " . $message );
        }

		return $data;

	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_excel_to_items'))
{
	/**
	 * Object Pre Save Sub-Tasks
	 *
	 * Get Item List from Excel File into Object Attributes
	 *
	 * @param string $filename 		Excel File
	 * @return array
	 */
	function _OBJ_MISC_HI_excel_to_items( $filename )
	{
		$items = [];
		if( $filename  )
		{
			/**
			 * Get excel data into array
			 */
			$excel_data = excel_to_array($filename);

			/**
			 * Excel Data Structure Must follow this structure
			 *
			[1] => Array
	        (
	            [A] => S.N.
				[B] => Employee Name
				[C] => Designation
				[D] => Age
				[E] => Sex
				[F] => Family Members and Relation with Employee
				[G] => Sum Insured (Rs)
				[H] => Premium (Rs)
				[I] => Medical Group Number
	        )
	        */

	        // Remove Header Row
	        array_shift($excel_data);

	        /**
	         * Format data to save into JSON Object Items
	         */
	        $excel_columns = _OBJ_MISC_HI_item_excel_columns();
	        foreach($excel_data as $row)
	        {
	        	/**
	        	 * At least you need to have "Name" and "Sum Insured", Premium Column
	        	 */
	        	if( !empty($row['B']) && !empty($row['G']) && !empty($row['H']) )
	        	{
	        		foreach($excel_columns as $col_index => $item_key)
		        	{
		        		$col_value = $row[$col_index] ?? NULL;

		        		// If Sum Insured, Premium Column, Get Clean DECIMAL Value.
		        		if($col_index === 'G' || $col_index === 'H')
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
	        	throw new Exception("Exception [Helper: ph_misc_hi_helper][Method: _OBJ_MISC_HI_excel_to_items()]: Excel file does not contain valid data.");
	        }
		}
		return $items;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_HI_compute_sum_insured_amount'))
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
	function _OBJ_MISC_HI_compute_sum_insured_amount( $portfolio_id, $data )
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

if ( ! function_exists('_OBJ_MISC_HI_compute_total_premium_amount'))
{
	/**
	 * Compute total premium from Item List
	 *
	 * @param array 	$items 	Item List
	 * @return float
	 */
	function _OBJ_MISC_HI_compute_total_premium_amount( $items )
	{
		/**
		 * Sum up all the item's sum insured amount to get the total Sum Insured
		 * Amount
		 */
		$items_premium 	= $items->premium ?? [];
		$total_premium 	= 0.00;

		foreach($items_premium as $premium)
		{
			// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
			$premium 	= (float) filter_var($premium, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$total_premium +=  $premium;
		}
		return $total_premium;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_HI_premium_validation_rules'))
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
	function _TXN_MISC_HI_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [
			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => _POLICY_TRANSACTION_premium_basic_v_rules( $policy_record->portfolio_id, $pfs_record ),

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

if ( ! function_exists('_TXN_MISC_HI_premium_goodies'))
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
	function _TXN_MISC_HI_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_HI_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_HI'))
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
	function __save_premium_MISC_HI($policy_record, $txn_record)
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
			$validation_rules = _TXN_MISC_HI_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					 * 	B. Gross Premium from Item List
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
					$GROSS_PREMIUM 		= _OBJ_MISC_HI_compute_total_premium_amount($object_attributes->items);
					$POOL_PREMIUM 		= 0.00;

					$cost_calculation_table[] = [
						'label' => "बीमाशुल्क",
						'value' => $GROSS_PREMIUM
					];


					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on Basic Premium
					 */
					$DD 					= 0.00;
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$DD = ( $GROSS_PREMIUM * $pfs_record->direct_discount ) / 100.00 ;
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $GROSS_PREMIUM;
						$agent_commission 		= ( $GROSS_PREMIUM * $pfs_record->agent_commission ) / 100.00;
					}

					$cost_calculation_table[] = [
						'label' => "प्रत्यक्ष छुट ({$pfs_record->direct_discount}%)",
						'value' => $DD
					];


					// Net Premium
					$NET_PREMIUM = $GROSS_PREMIUM - $DD;
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
					$txn_data['premium_computation_table'] = NULL;


					/**
					 * Cost Calculation Table
					 */
					$txn_data['cost_calculation_table'] = json_encode($cost_calculation_table);
					return $CI->policy_transaction_model->save($txn_record->id, $txn_data);


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



