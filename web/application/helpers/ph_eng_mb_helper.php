<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube MACHINE BREAKDOWN - Engineering Portfolio Helper Functions
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


// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_ENG_MB_row_snippet'))
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
	function _OBJ_ENG_MB_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_eng_mb', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_MB_select_text'))
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
	function _OBJ_ENG_MB_select_text( $record )
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

if ( ! function_exists('_OBJ_ENG_MB_validation_rules'))
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
	function _OBJ_ENG_MB_validation_rules( $portfolio_id, $formatted = FALSE )
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
			        'field' => 'object[risk_locaiton]',
			        '_key' => 'risk_locaiton',
			        'label' => 'Location of Risk',
			        'rules' => 'trim|required|max_length[250]',
			        'rows' 		=> 4,
			        '_type'     => 'textarea',
			        '_required' => true
			    ],
			    [
			        'field' => 'object[excess]',
			        '_key' => 'excess',
			        'label' => 'Excess / Deductible',
			        'rules' => 'trim|required|max_length[500]',
			        '_type'     => 'text',
			        '_default' => '__% of claim amount subject to minimum of Rs. ______ on each and every item.',
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
			        'field' => 'object[items][description][]',
			        '_key' => 'description',
			        'label' => 'Description',
			        'rules' => 'trim|required|max_length[500]',
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

if ( ! function_exists('_OBJ_ENG_MB_pre_save_tasks'))
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
	function _OBJ_ENG_MB_pre_save_tasks( array $data, $record = NULL )
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
				throw new Exception("Exception [Helper: ph_eng_bl_helper][Method: _OBJ_ENG_MB_pre_save_tasks()]: Invalid file uploaded. Please upload only excel file(.xls or .xlsx).");
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
		            [A] => Description
		            [B] => Sum Insured
		        )
		        */

		        // Remove Header Row
		        array_shift($excel_data);

		        /**
		         * Format data to save into JSON Object Items
		         */
		        $excel_columns = [ 'A' => 'description', 'B' => 'sum_insured' ];
		        $items = [];
		        foreach($excel_data as $row)
		        {
		        	/**
		        	 * At least you need to have description and sum_insured amount filled
		        	 */
		        	if( !empty($row['A']) && !empty($row['B']) )
		        	{
		        		foreach($excel_columns as $col_index => $item_key)
			        	{
			        		$col_value = $row[$col_index] ?? NULL;

			        		// If Sum Insured Column, Get Clean DECIMAL Value.
			        		if($col_index === 'B')
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
		        	throw new Exception("Exception [Helper: ph_eng_bl_helper][Method: _OBJ_ENG_MB_pre_save_tasks()]: Excel file does not contain valid data.");
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
		$data = _OBJ_ENG_MB_format_items($data);


		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_MB_format_items'))
{
	/**
	 * Format Fire Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_ENG_MB_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules = _OBJ_ENG_MB_validation_rules(IQB_SUB_PORTFOLIO_ENG_MB_ID)['items'];

		$items_formatted = [];
		$count = count($items['description']);

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

if ( ! function_exists('_OBJ_ENG_MB_compute_sum_insured_amount'))
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
	function _OBJ_ENG_MB_compute_sum_insured_amount( $portfolio_id, $data )
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

if ( ! function_exists('_TXN_ENG_MB_premium_validation_rules'))
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
	function _TXN_ENG_MB_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
	{
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

if ( ! function_exists('_TXN_ENG_MB_premium_goodies'))
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
	function _TXN_ENG_MB_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_ENG_MB_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_ENG_MB'))
{
	/**
	 * Update Policy Premium Information - ENGINEERING - MACHINE BREAKDOWN
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_ENG_MB($policy_record, $endorsement_record)
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

			$object_attributes  = json_decode($policy_object->attributes);

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_ENG_MB_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					 * Sum Insured
					 */
					$SI = floatval($policy_object->amt_sum_insured);


					/**
					 * Get post premium Data
					 * 	a. Default Rate
					 * 	b. Pool Premium Flag
					 * 	c. Other common data
					 */
					$post_premium 				= $post_data['premium'];
					$default_rate 				= floatval($post_premium['default_rate']);
					$flag_pool_risk 			= $post_premium['flag_pool_risk'] ?? 0;


					// A = SI X Default Rate %
					$A = ( $SI * $default_rate ) / 100.00;
					$cost_calculation_table[] = [
						'label' => "Gross Premium",
						'value' => $A
					];



					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 * Agent Commission or Direct Discount
					 * applies on NET Premium
					 */
					$B 						= 0.00;
					$commissionable_premium = NULL;
					$agent_commission 		= NULL;
					$direct_discount 		= NULL;
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						$dd_formatted = number_format($pfs_record->direct_discount, 2);
						$cost_calculation_table[] = [
							'label' => "Direct discount ({$dd_formatted}%)",
							'value' => $direct_discount
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}

					// NET BASIC PREMIUM
					$NET_BASIC_PREMIUM = $A - $direct_discount;

					/**
					 * Pool Premium
					 */
					$POOL_PREMIUM = 0.00;
					if($flag_pool_risk)
					{
						// Pool Premium = x% of SI
						$pool_rate = floatval($pfs_record->pool_premium);
						$POOL_PREMIUM = ( $SI * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "Pool Premium",
						'value' => $POOL_PREMIUM
					];

					$cost_calculation_table[] = [
						'label' => "Total Premium",
						'value' => $NET_BASIC_PREMIUM + $POOL_PREMIUM
					];


					// -----------------------------------------------------------------------------


					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'gross_amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
						'gross_amt_commissionable'	=> $commissionable_premium,
						'gross_amt_agent_commission'  => $agent_commission,
						'gross_amt_direct_discount' 	=> $direct_discount,
						'gross_amt_pool_premium' 		=> $POOL_PREMIUM,
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
// PORTFOLIO SPECIFIC HELPER FUNCTIONS
// ------------------------------------------------------------------------



