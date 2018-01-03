<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube ELECTRONIC EQUIPMENT INSURANCE - Engineering Portfolio Helper Functions
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


if ( ! function_exists('_OBJ_ENG_EEI_row_snippet'))
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
	function _OBJ_ENG_EEI_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_eng_eei', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_ENG_EEI_select_text'))
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
	function _OBJ_ENG_EEI_select_text( $record )
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

if ( ! function_exists('_OBJ_ENG_EEI_validation_rules'))
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
	function _OBJ_ENG_EEI_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();

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
		    'item' => [
			    [
			        'field' => 'object[item][description]',
			        '_key' => 'description',
			        'label' => 'Item Summary',
			        'rules' => 'trim|required|htmlspecialchars|max_length[500]',
			        '_type' => 'textarea',
			        'rows' 	=> 4,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[item][sum_insured]',
			        '_key' => 'sum_insured',
			        'label' => 'Sum Insured(Rs)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type' => 'text',
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ],
			    [
			        'field' => 'object[item][excess]',
			        '_key' => 'excess',
			        'label' => 'Excess',
			        'rules' => 'trim|htmlspecialchars|max_length[300]',
			        '_type' => 'textarea',
			        'rows' 	=> 4,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ]
		    ],

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

if ( ! function_exists('_OBJ_ENG_EEI_pre_save_tasks'))
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
	function _OBJ_ENG_EEI_pre_save_tasks( array $data, $record )
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
                'max_size' 		=> '2048'
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

if ( ! function_exists('_OBJ_ENG_EEI_compute_sum_insured_amount'))
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
	function _OBJ_ENG_EEI_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * We have single item, so it's SI is the object's total SI
		 */
		$amt_sum_insured = $data['item']['sum_insured'] ?? 0.00;

		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_ENG_EEI_premium_validation_rules'))
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
	function _TXN_ENG_EEI_premium_validation_rules($policy_record, $pfs_record, $policy_object, $for_form_processing = FALSE )
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
			'basic' => basic_premium_validation_rules( $policy_record->portfolio_id, $pfs_record ),

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => installment_validation_rules( $policy_record->portfolio_id, $pfs_record )
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

if ( ! function_exists('_TXN_ENG_EEI_premium_goodies'))
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
	function _TXN_ENG_EEI_premium_goodies($policy_record, $policy_object)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_ENG_EEI_premium_validation_rules( $policy_record, $pfs_record, $policy_object );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_ENG_EEI'))
{
	/**
	 * Update Policy Premium Information - ENGINEERING - CONTRACTOR PLANT & MACHINARY
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_ENG_EEI($policy_record, $txn_record)
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
			$validation_rules = _TXN_ENG_EEI_premium_validation_rules($policy_record, $pfs_record, $policy_object, TRUE );
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
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						// Direct Discount
						$B = ( $A * $pfs_record->direct_discount ) / 100.00 ;

						$cost_calculation_table[] = [
							'label' => "Direct discount ({$pfs_record->direct_discount}%)",
							'value' => $B
						];
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$commissionable_premium = $A;
						$agent_commission 		= ( $A * $pfs_record->agent_commission ) / 100.00;
					}



					// C = A - B
					$C = $A - $B;


					/**
					 * Pool Premium
					 */
					$POOL_PREMIUM = 0.00;
					if($flag_pool_risk)
					{
						// Pool Premium = x% of Default Premium (A-B)
						$pool_rate = floatval($pfs_record->pool_premium);
						$POOL_PREMIUM = ( $C * $pool_rate ) / 100.00;
					}
					$cost_calculation_table[] = [
						'label' => "Pool Premium",
						'value' => $POOL_PREMIUM
					];

					$NET_PREMIUM = $C + $POOL_PREMIUM;
					$cost_calculation_table[] = [
						'label' => "Net Premium",
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



