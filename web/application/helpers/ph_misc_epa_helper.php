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
 * @sub-portfolio 	Expedition Personnel Accident(EPA)
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_EPA_row_snippet'))
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
	function _OBJ_MISC_EPA_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_misc_epa', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_select_text'))
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
	function _OBJ_MISC_EPA_select_text( $record )
	{
		$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
		$item_count = count($attributes->items->sum_insured ?? []);

		$snippet = [
			'Expedition Personnel Count: ' . '<strong>' . $item_count . '</strong>',
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];

		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_validation_rules'))
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
	function _OBJ_MISC_EPA_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

		// Validation Rules on Form Post Change on interdependent components
		$post 	= $CI->input->post();
		$object = $post['object'] ?? NULL;

		// Dropdowns
		$trek_category_dropdown 	= _OBJ_MISC_EPA_trek_category_dropdown( FALSE );
		$staff_trek_type_dropdown 	= _OBJ_MISC_EPA_staff_trek_type_dropdown( FALSE );
		$staff_type_dropdown 		= _OBJ_MISC_EPA_staff_type_dropdown( FALSE );


		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[trek_route]',
			        '_key' => 'trek_route',
			        'label' => 'Trek Route',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[peak_no]',
			        '_key' => 'peak_no',
			        'label' => 'Number of Peak',
			        'rules' => 'trim|required|integer|max_length[3]',
			        '_type' => 'text',
			        '_required' 	=> true
			    ],
				[
			        'field' => 'object[trek_category]',
			        '_key' => 'trek_category',
			        'label' => 'यात्राको प्रकार',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($trek_category_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $trek_category_dropdown,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[amt_rescue]',
			        '_key' => 'amt_rescue',
			        'label' => 'उद्घार रकम ($)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
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
			        'field' => 'object[items][staff_trek_type][]',
			        '_key' => 'staff_trek_type',
			        'label' => 'यात्राको प्रकार',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($staff_trek_type_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $staff_trek_type_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][staff_type][]',
			        '_key' => 'staff_type',
			        'label' => 'कर्मचारीको प्रकार',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($staff_type_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $staff_type_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
		    	[
			        'field' => 'object[items][name][]',
			        '_key' => 'name',
			        'label' => 'बीमित कर्मचारीको नाम थर',
			        'rules' => 'trim|required|htmlspecialchars|max_length[150]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][position][]',
			        '_key' => 'position',
			        'label' => 'पद',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][document_id][]',
			        '_key' => 'document_id',
			        'label' => 'नागरिकता/गाइड लाइसेन्स',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[items][address][]',
			        '_key' => 'address',
			        'label' => 'ठेगाना',
			        'rules' => 'trim|required|htmlspecialchars|max_length[200]',
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
			        'field' => 'object[items][medical][]',
			        '_key' => 'medical',
			        'label' => 'अधिकतम मेडिकल खर्च (रु)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
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
			 * If excel file uploaded, we do not need any item validation rules!
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

if ( ! function_exists('_OBJ_MISC_EPA_trek_category_dropdown'))
{
	/**
	 * Get Portfolio Trekking Category Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_EPA_trek_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'T' => 'Trekking',
			'E' => 'Expedition',
			'B' => 'Both'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_staff_trek_type_dropdown'))
{
	/**
	 * Get Staff Trekking Type Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_EPA_staff_trek_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'T' => 'Trekking',
			'E' => 'Expedition'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_staff_type_dropdown'))
{
	/**
	 * Get Staff Type Dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_MISC_EPA_staff_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'N' => 'Named',
			'U' => 'Un-Named'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_MISC_EPA_item_headings_dropdown'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_EPA_item_headings_dropdown( )
	{
		$items = _OBJ_MISC_EPA_validation_rules(IQB_SUB_PORTFOLIO_MISC_EPA_ID)['items'];
	    $dropdown = [];
	    foreach($items as $item)
	    {
	    	$dropdown[$item['_key']] = $item['label'];
	    }

	    return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_item_excel_columns'))
{
	/**
	 * Get Item Heading List
	 *
	 * These are the headings used in insured personnels details
	 *
	 * @return array
	 */
	function _OBJ_MISC_EPA_item_excel_columns( )
	{
		return [
			'A' => 'staff_trek_type',
			'B' => 'staff_type',
			'C' => 'name',
			'D' => 'position',
			'E' => 'document_id',
			'F' => 'sum_insured',
			'G' => 'medical'
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_pre_save_tasks'))
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
	function _OBJ_MISC_EPA_pre_save_tasks( array $data, $record = NULL )
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
				throw new Exception("Exception [Helper: ph_misc_epa_helper][Method: _OBJ_MISC_EPA_pre_save_tasks()]: Invalid file uploaded. Please upload only excel file(.xls or .xlsx).");
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
		            [A] => Trek Type
		            [B] => Staff Type
		            [C] => Staff Name
		            [D] => Position
		            [E] => Citizenship/Guide License
		            [F] => Sum Insured
		            [G] => Medical
		        )
		        */

		        // Remove Header Row
		        array_shift($excel_data);

		        /**
		         * Format data to save into JSON Object Items
		         */
		        $excel_columns = _OBJ_MISC_EPA_item_excel_columns();
		        $items = [];

		        $staff_trek_type_dropdown 	= _OBJ_MISC_EPA_staff_trek_type_dropdown( FALSE );
				$staff_type_dropdown 		= _OBJ_MISC_EPA_staff_type_dropdown( FALSE );

		        foreach($excel_data as $row)
		        {

		        	/**
		        	 * Valid Staff Type and Trek Type?
		        	 */
		        	if( !in_array($row['A'], $staff_trek_type_dropdown) || !in_array($row['B'], $staff_type_dropdown))
		        	{
		        		throw new Exception("Exception [Helper: ph_misc_epa_helper][Method: _OBJ_MISC_EPA_pre_save_tasks()]: Excel file does not contain valid data - Staff Trek Type and/or Staff Type.");
		        	}


		        	/**
		        	 * At least you need to have "Name" and "Sum Insured" Column
		        	 */
		        	if( !empty($row['A']) && !empty($row['B']) && !empty($row['F']) )
		        	{
		        		foreach($excel_columns as $col_index => $item_key)
			        	{
			        		$col_value = $row[$col_index] ?? NULL;

			        		// If Sum Insured, Medical Column, Get Clean DECIMAL Value.
			        		if($col_index === 'F' || $col_index === 'G')
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
		        	throw new Exception("Exception [Helper: ph_misc_epa_helper][Method: _OBJ_MISC_EPA_pre_save_tasks()]: Excel file does not contain valid data - No Valid Excel Row.");
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

if ( ! function_exists('_OBJ_MISC_EPA_compute_sum_insured_amount'))
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
	function _OBJ_MISC_EPA_compute_sum_insured_amount( $portfolio_id, $data )
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


		/**
		 * SI Breakdown
		 * ---------------------
		 *
		 * 	- SI BASECAMP ABOVE
		 * 	- SI BASECAMP NAMED
		 * 	- SI BASECAMP UNNAMED
		 * 	- SI MEDICAL
		 * 	- SI RESCUE
		 */
		$si_bc_above 	= _OBJ_MISC_EPA_sum_insured_amount_by_type('E', $data['items']);
		$si_bc_named 	= _OBJ_MISC_EPA_sum_insured_amount_by_type('TN', $data['items']);
		$si_bc_unnamed 	= _OBJ_MISC_EPA_sum_insured_amount_by_type('TU', $data['items']);
		$si_medical 	= _OBJ_MISC_EPA_sum_insured_amount_by_type('M', $data['items']);
		$si_rescue 		= floatval( $data['amt_rescue'] ?? 0 );

		$si_breakdown = json_encode([
			'si_bc_above' 	=> $si_bc_above,
			'si_bc_named' 	=> $si_bc_named,
			'si_bc_unnamed' => $si_bc_unnamed,
			'si_medical' 	=> $si_medical,
			'si_rescue' 	=> $si_rescue
		]);

		return ['amt_sum_insured' => $amt_sum_insured, 'si_breakdown' => $si_breakdown];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_MISC_EPA_sum_insured_amount_by_type'))
{
	/**
	 * Get Sum Insured Amount by Types (Base Camp Above, Named Staff, Un-Named Staff, Medical SI)
	 *
	 * 	E 	: Expedition (Above Base Camp Staff)
	 * 	TN 	: Trekking Named
	 * 	TU 	: Trekking Un-Named
	 * 	M 	: Medical
	 *
	 * @param string $type [E|TN|TU]
	 * @param array $items Post Data of Items
	 * @return float
	 */
	function _OBJ_MISC_EPA_sum_insured_amount_by_type( $type, $items )
	{
		$item_count 		= count($items['sum_insured']?? []);
		$items_sum_insured 	= $items['sum_insured'];
		$staff_trek_types 	= $items['staff_trek_type'];
		$staff_types 		= $items['staff_type'];
		$amt_sum_insured 	= 0.00;

		for($i = 0; $i < $item_count; $i++ )
		{
			$trek_type_staff 	= $staff_trek_types[$i];
			$type_staff 		=  $staff_types[$i];

			/**
			 * Above Base Camp
			 */
			if($type === 'E' && $trek_type_staff == 'E')
			{
				$amt_sum_insured += floatval($items_sum_insured[$i]);
			}

			/**
			 * Trekking Base Camp - Named
			 */
			else if($type === 'TN' && $trek_type_staff == 'T' && $type_staff == 'N')
			{
				$amt_sum_insured += floatval($items_sum_insured[$i]);
			}

			/**
			 * Trekking Base Camp - Un-Named
			 */
			else if($type === 'TU' && $trek_type_staff == 'T' && $type_staff == 'U')
			{
				$amt_sum_insured += floatval($items_sum_insured[$i]);
			}

			/**
			 * Medical
			 */
			else if($type === 'M')
			{
				$amt_sum_insured += floatval($items['medical'][$i]);
			}
		}
		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_MISC_EPA_premium_validation_rules'))
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
	function _TXN_MISC_EPA_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
		$validation_rules = [
			/**
			 * Premium Validation Rules - Template
			 */
			'premium' => [
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

if ( ! function_exists('_TXN_MISC_EPA_premium_goodies'))
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
	function _TXN_MISC_EPA_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Tariff Configuration for this Portfolio
		$CI->load->model('tariff_misc_epa_model');
		$tariff_record = $CI->tariff_misc_epa_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Valid Tariff?
		$__flag_valid_tariff = TRUE;
		if( !$tariff_record )
		{
			$message 	= 'Tariff Configuration for this Portfolio is not found.';
			$title 		= 'Tariff Not Found!';
			$__flag_valid_tariff = FALSE;
		}
		else if( $tariff_record->active == IQB_STATUS_INACTIVE )
		{
			$message 	= 'Tariff Configuration for this Portfolio is <strong>Inactive</strong>.';
			$title 		= 'Tariff Not Active!';
			$__flag_valid_tariff = FALSE;
		}

		if( !$__flag_valid_tariff )
		{
			$message .= '<br/><br/>Portfolio: <strong>Expedition Personnel Accident</strong> <br/>' .
						'Sub-Portfolio: <strong>' . $policy_record->portfolio_name . '</strong> <br/>' .
						'<br/>Please contact <strong>IT Department</strong> for further assistance.';

			return ['status' => 'error', 'message' => $message, 'title' => $title];
		}


		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


		// Let's Get the Validation Rules
		$validation_rules = _TXN_MISC_EPA_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> $tariff_record
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_MISC_EPA'))
{
	/**
	 * Expedition Personnel Accident Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_MISC_EPA($policy_record, $endorsement_record)
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
			$validation_rules = _TXN_MISC_EPA_premium_validation_rules($policy_record, $pfs_record, $old_object, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data = $CI->input->post();

				try{

					// Load Forex Helper
					$CI->load->helper('forex');

					/**
					 * Tariff Data
					 */
					$CI->load->model('tariff_misc_epa_model');
					$tariff_record 	= $CI->tariff_misc_epa_model->get_by_fy_portfolio( $policy_record->fiscal_yr_id, $policy_record->portfolio_id);
					$tariff   		= json_decode($tariff_record->tariff ?? NULL);

					/**
					 * Get post premium Data
					 * 	a. Default Rate
					 * 	b. Pool Premium Flag
					 * 	c. Other common data
					 */
					$post_premium  	= $post_data['premium'] ?? [];
					$flag_pool_risk = $post_premium['flag_pool_risk'] ?? 0;


					/**
					 * NET Sum Insured & Its Breakdown
					 *
					 * 	a. Above Base Camp Staff Sum Insured
					 * 	b. Base Camp Staff - Named Sum Insured
					 * 	c. Base Camp Staff - Un-Named Sum Insured
					 * 	d. Medical Sum Insured
					 * 	e. Resuce Sum Insured
					 */
					$SI 			= _OBJ_si_net($old_object, $new_object);
					$SI_BREAKDOWN 	= _OBJ_si_breakdown_net($old_object, $new_object);

					$SI_BC_ABOVE 	= $SI_BREAKDOWN['si_bc_above'];
					$SI_BC_NAMED 	= $SI_BREAKDOWN['si_bc_named'];
					$SI_BC_UNNAMED 	= $SI_BREAKDOWN['si_bc_unnamed'];
					$SI_MEDICAL 	= $SI_BREAKDOWN['si_medical'];
					$SI_RESCUE 		= forex_conversion(date('Y-m-d'), 'USD', floatval($SI_BREAKDOWN['si_rescue']));


					/**
					 * Compute Premium
					 * -----------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Above Base Camp Staff Premium
					 * 	b. Base Camp Named Staff Premium
					 * 	c. Base Camp Un-Named Staff Premium
					 * 	d. Medical Premium
					 * 	e. Rescue Premium
					 */
					$PRERMIUM_BC_ABOVE 		= ( $SI_BC_ABOVE * $tariff->bc_above ) / 100.00;
					$PRERMIUM_BC_NAMED 		= ( $SI_BC_NAMED * $tariff->bc_named ) / 100.00;
					$PRERMIUM_BC_UNNAMED 	= ( $SI_BC_UNNAMED * $tariff->bc_unnamed ) / 100.00;
					$PRERMIUM_MEDICAL 		= ( $SI_MEDICAL * $tariff->medical ) / 100.00;
					$PRERMIUM_RESCUE 		= ( $SI_RESCUE * $tariff->rescue ) / 100.00;



					$cost_calculation_table[] = [
						'label' => "Above Base Camp Staff Premium (Amount Rs. {$SI_BC_ABOVE}, Rate {$tariff->bc_above}% )",
						'value' => $PRERMIUM_BC_ABOVE
					];

					$cost_calculation_table[] = [
						'label' => "Base Camp Staff (Named) Premium (Amount Rs. {$SI_BC_NAMED}, Rate {$tariff->bc_named}% )",
						'value' => $PRERMIUM_BC_NAMED
					];

					$cost_calculation_table[] = [
						'label' => "Base Camp Staff (Un-Named) Premium (Amount Rs. {$SI_BC_UNNAMED}, Rate {$tariff->bc_unnamed}% )",
						'value' => $PRERMIUM_BC_UNNAMED
					];

					$cost_calculation_table[] = [
						'label' => "Medical Premium (Amount Rs. {$SI_MEDICAL}, Rate {$tariff->medical}% )",
						'value' => $PRERMIUM_MEDICAL
					];

					$cost_calculation_table[] = [
						'label' => "Rescue Premium (Amount Rs. {$SI_RESCUE}, Rate {$tariff->rescue}% )",
						'value' => $PRERMIUM_RESCUE
					];

					// Total
					$PREMIUM_1 = $PRERMIUM_BC_ABOVE + $PRERMIUM_BC_NAMED + $PRERMIUM_BC_UNNAMED + $PRERMIUM_MEDICAL + $PRERMIUM_RESCUE;


					$cost_calculation_table[] = [
						'label' => "Sub-Total",
						'value' => $PREMIUM_1
					];

					/**
					 * Additional Peaks Premium
					 */
					$ADDITIONAL_PEAK_PREMIUM 	= 0.00;
					$additional_peak_rate 		= 0.00;
					$additional_peak_no 		= $object_attributes->peak_no - 1;
					if($additional_peak_no >= 1)
					{
						// Per additional peak 25% of GROSS PREMIUM
						$additional_peak_rate 		= $additional_peak_no * 25;
						$ADDITIONAL_PEAK_PREMIUM 	= ( $PREMIUM_1 * $additional_peak_rate ) / 100.00;
					}

					$cost_calculation_table[] = [
						'label' => "Additional Peak Premium (Additional Peak: {$additional_peak_no}, Rate {$additional_peak_rate}% )",
						'value' => $ADDITIONAL_PEAK_PREMIUM
					];

					$PREMIUM_TOTAL = $PREMIUM_1 + $ADDITIONAL_PEAK_PREMIUM;
					$cost_calculation_table[] = [
						'label' => "Basic Premium",
						'value' => $PREMIUM_TOTAL
					];




					/**
					 * Direct Discount or Agent Commission?, Pool Premium
					 * --------------------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$POOL_PREMIUM 			= 0.00;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$direct_discount = ( $PREMIUM_TOTAL * $pfs_record->direct_discount ) / 100.00 ;
						$PREMIUM_TOTAL -= $direct_discount;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "Direct discount ({$dd_formatted}%)",
							'value' => $direct_discount
						];

					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $PREMIUM_TOTAL;
						$agent_commission 		= ( $PREMIUM_TOTAL * $pfs_record->agent_commission ) / 100.00;
					}


					/**
					 * Pool Premium
					 */
					$POOL_PREMIUM = 0.00;
					if($flag_pool_risk)
					{
						// Pool Premium = x% of Default Premium (A-B)
						$pool_rate = floatval($pfs_record->pool_premium);
						$POOL_PREMIUM = ( $SI * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "Pool Premium",
						'value' => $POOL_PREMIUM
					];


					// NET PREMIUM
					$cost_calculation_table[] = [
						'label' => "Total Premium",
						'value' => $PREMIUM_TOTAL + $POOL_PREMIUM
					];



					/**
					 * Build Schedule's Cost Tabale
					 */
					$schedule_cost_table = [
						[
							'label' => "Basic Premium",
							'value' => $PREMIUM_1
						],
						[
							'label' => "Additional Premium",
							'value' => $ADDITIONAL_PEAK_PREMIUM
						],
						[
							'label' => "Direct discount",
							'value' => $direct_discount
						],
						[
							'label' => "Pool Premium",
							'value' => $POOL_PREMIUM
						],
						[
							'label' => "Total Premium",
							'value' => $PREMIUM_TOTAL + $POOL_PREMIUM
						]
					];


					/**
					 * Cost Calculation Table
					 */
					$cost_calculation_table = json_encode([
						'cost_calculation_table' 	=> $cost_calculation_table,
						'schedule_cost_table' 		=> $schedule_cost_table
					]);

					/**
					 * Premium Computation Table
					 */
					$premium_computation_table 	= json_encode($post_premium);

					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $PREMIUM_TOTAL,
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
						// Transaction Date must be set as today
						$endorsement_record->txn_date = date('Y-m-d');
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
						'txn_date' 				=> date('Y-m-d'),

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



