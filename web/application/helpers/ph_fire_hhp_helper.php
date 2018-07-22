<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Fire Portfolio Helper Functions
 *
 * This file contains helper functions related to Fire Portfolio
 *
 * @package			InsQube
 * @subpackage		Helpers
 * @category		Helpers
 * @portfolio 		FIRE
 * @sub-portfolio 	HOUSEHOLDER
 * @author			IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_FIRE_HHP_row_snippet'))
{
	/**
	 * Get Policy Object - Fire - Row Snippet
	 *
	 * Row Partial View for Fire Object
	 *
	 * @param object $record Policy Object (Fire)
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_FIRE_HHP_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_fire_hhp', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_select_text'))
{
	/**
	 * Get Policy Object - FIRE - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 * @param bool $record 	Object Record
	 * @return	html
	 */
	function _OBJ_FIRE_HHP_select_text( $record )
	{
		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];
		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_validation_rules'))
{
	/**
	 * Get Policy Object - FIRE - Validation Rules
	 *
	 * Returns array of form validation rules for motor policy object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	bool
	 */
	function _OBJ_FIRE_HHP_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$category_dropdown 	= _OBJ_FIRE_HHP_item_category_dropdown( FALSE );
		$ownership_dropdown = _OBJ_FIRE_HHP_item_ownership_dropdown( FALSE );

		$conscat_dropdown 	= _OBJ_FIRE_HHP_item_building_category_dropdown( FALSE );
		$district_dropdown 	= district_dropdown( FALSE );


		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'document',
			        '_key' => 'document',
			        'label' => 'Upload Item List (.doc, .docx, .pdf, .jpg, .png, .xls or .xlsx)',
			        'rules' => '',
			        '_type'     => 'file',
			        '_required' => false
			    ],
		    ],


			/**
			 * Item List
			 */
			'items' => [
				[
			        'field' => 'object[items][category][]',
			        '_key' => 'category',
			        'label' => 'Item Category',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($category_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $category_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][description][]',
			        '_key' => 'description',
			        'label' => 'Item Description',
			        'rules' => 'trim|max_length[500]',
			        '_type'     => 'textarea',
			        'rows' 		=> 4,
			        '_show_label' 	=> false,
			        '_required' => false
			    ],
			    [
			        'field' => 'object[items][sum_insured][]',
			        '_key' => 'sum_insured',
			        'label' => 'Item Price(Sum Insured)',
			        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[items][ownership][]',
			        '_key' => 'ownership',
			        'label' => 'Item Ownership',
			        'rules' => 'trim|required|alpha|in_list['. implode(',', array_keys($ownership_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $ownership_dropdown,
			        '_show_label' 	=> false,
			        '_required' 	=> true
			    ]
		    ],

		    /**
		     * Land Owner Details (Building)
		     */
		    'land_building_owner' => [
		    	[
			        'field' => 'object[land_building][owner_name][]',
			        '_key' => 'owner_name',
			        'label' => 'Owner Name(s)',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][owner_address][]',
			        '_key' => 'owner_address',
			        'label' => 'Owner Address',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'textarea',
			        'rows' 		=> 4,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][owner_contacts][]',
			        '_key' => 'owner_contacts',
			        'label' => 'Owner Contacts(Mobile/Phone)',
			        'rules' => 'trim|max_length[200]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],


			    /**
			     * Land Details (Building)
			     */
		    	[
			        'field' => 'object[land_building][plot_no][]',
			        '_key' => 'plot_no',
			        'label' => 'Land Plot No.',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][house_no][]',
			        '_key' => 'house_no',
			        'label' => 'House No.',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][tole][]',
			        '_key' => 'tole',
			        'label' => 'Tole/Street Address',
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][district][]',
			        '_key' => 'district',
			        'label' => 'District',
			        'rules' => 'trim|numeric|max_length[2]|in_list['. implode(',', array_keys($district_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $district_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][vdc][]',
			        '_key' => 'vdc',
			        'label' => 'VDC/Municipality',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][ward_no][]',
			        '_key' => 'ward_no',
			        'label' => 'Ward No.',
			        'rules' => 'trim|integer|max_length[2]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][storey_no][]',
			        '_key' => 'storey_no',
			        'label' => 'No. of Stories',
			        'rules' => 'trim|numeric|max_length[5]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][category][]',
			        '_key' => 'category',
			        'label' => 'Construction Category',
			        'rules' => 'trim|integer|exact_length[1]|in_list['. implode(',', array_keys($conscat_dropdown)) .']',
			        '_type'     => 'dropdown',
			        '_data' 	=> IQB_BLANK_SELECT + $conscat_dropdown,
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][used_for][]',
			        '_key' => 'used_for',
			        'label' => 'Used For',
			        'rules' => 'trim|max_length[50]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
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

if ( ! function_exists('_OBJ_FIRE_HHP_item_building_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item Building Construction category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_HHP_item_building_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'1' => 'First',
			'2' => 'Second',
			'3'	=> 'Third',
			'4' => 'Fourth',
			'5' => 'Open Space'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_item_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_HHP_item_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'BWALL' => 'Boundary Wall',
			'BLDNG' => 'Building',
			'GOODS'	=> 'Goods/Stock',
			'MCNRY' => 'Machinary',
			'OTH' 	=> 'Others'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_item_ownership_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_HHP_item_ownership_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'O' => 'Owned',
			'R' => 'Rented'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Perform tasks that are required before saving a policy objects.
	 * Return the processed data for further computation or saving in DB
	 *
	 * If items are saved as attachment, we upload the excel file.
	 *
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_FIRE_HHP_pre_save_tasks( array $data, $record )
	{
		$CI =& get_instance();

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
                'allowed_types' => 'xls|xlsx|doc|docx|jpg|jpeg|png|pdf',
                'max_size' 		=> '4096'
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
        else if( $status === 'no_file_selected' )
        {
			// Old Document as it is
			$data['object']['document'] = $old_document;
        }
        else{
        	throw new Exception("Exception [Helper: ph_fire_hhp_helper][Method: _OBJ_FIRE_HHP_pre_save_tasks()]: " . $message );
        }

        /**
		 * Format Items
		 */
		$data = _OBJ_FIRE_HHP_format_items($data);

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_HHP_format_items'))
{
	/**
	 * Format Fire Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_FIRE_HHP_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules = _OBJ_FIRE_HHP_validation_rules(IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID)['items'];

		$items_formatted = [];
		$count = count($items['category']);

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

if ( ! function_exists('_OBJ_FIRE_HHP_compute_sum_insured_amount'))
{
	/**
	 * Compute Sum Insured Amount of Policy Object - FIRE Portfolio
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_FIRE_HHP_compute_sum_insured_amount( $portfolio_id, $data )
	{
		/**
		 * Compute Sum of all items' sum_insured
		 */
		$amt_sum_insured = 0;
		$items = $data['items'];
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

if ( ! function_exists('_TXN_FIRE_HHP_premium_validation_rules'))
{
	/**
	 * Get Policy TXN Validation Rules for Premium Add/Update for FIRE Portfolio
	 *
	 * @param object $policy_record 	Policy Record
	 * @param object $pfs_record		Portfolio Setting Record
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @param string $return 			Return all rules or policy package specific
	 * @return array
	 */
	function _TXN_FIRE_HHP_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{
		$v_rules = [
			/**
			 * Premium - Risk and Rate
			 */
			'premium' => [
                [
	                'field' => 'premium[risk]',
	                'label' => 'Rate',
	                'rules' => 'trim|required|integer|max_length[8]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[rate]',
	                'label' => 'Rate (Rs. Per Thousand)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
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
			$rules = $v_rules['basic'];

			/**
			 * Append Premium Risk validation rules
			 */
			$premium_elements 	= $v_rules['premium'];

			// Loop through each portfolio risks
			foreach($portfolio_risks as $risk_id=>$risk_name)
			{
				foreach ($premium_elements as $elem)
                {
                	$elem['field'] .= "[{$risk_id}]";
                	$rules[] = $elem;
                }
			}
			return $rules;
		}

		return $v_rules;

	}
}


// --------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_HHP_premium_goodies'))
{
	/**
	 * Get Policy Endorsement Goodies
	 *
	 * Get the following goodies
	 * 		1. Validation Rules
	 *
	 * @param object $policy_record Policy Record
	 * @param object $policy_object Policy Object Record
	 * @param object $portfolio_risks Portfolio Risks
	 *
	 * @return	array
	 */
	function _TXN_FIRE_HHP_premium_goodies($policy_record, $policy_object, $portfolio_risks)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_FIRE_HHP_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_FIRE_HHP'))
{
	/**
	 * Fire Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_FIRE_HHP($policy_record, $endorsement_record)
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
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_FIRE_HHP_premium_validation_rules($policy_record, $pfs_record, $old_object, $portfolio_risks, TRUE );
            $CI->form_validation->set_rules($validation_rules);

            // echo '<pre>';print_r($validation_rules);exit;

			if($CI->form_validation->run() === TRUE )
        	{

				// Premium Data
				$post_data 		= $CI->input->post();
				$premium_data 	= $post_data['premium'];

				/**
				 * Do we have a valid method?
				 */
				try{

					/**
					 * Compute Premium From Post Data
					 * ------------------------------
					 * 	From the Portfolio Risks - We compute two type of premiums
					 * 	a. Pool Premium
					 *  b. Base Premium
					 */


					/**
					 * Portfolio Risks Rows
					 */
					$portfolio_risks = $CI->portfolio_model->portfolio_risks($policy_record->portfolio_id);


					/**
					 * Get the NET Sum Insured Amount
					 */
					$SI = _OBJ_si_net($old_object, $new_object);

					/**
					 * Initialization of Computational Variables
					 */
					$GROSS_PREMIUM 	= 0.00; // Gross Premium (Without Pool Premium)
					$POOL_PREMIUM  	= 0.00; // Pool Premium



					/**
					 * -------------------------------------------------------------------------------------
					 * COMPUTE GROSS & POOL PREMIUM
					 * -------------------------------------------------------------------------------------
					 */
					// Compute Gross and Pool Premium
					foreach($portfolio_risks as $pr)
					{
						// Rate in Per Thousand
						$rate = floatval($premium_data['rate'][$pr->id]);

						$premium = $SI * $rate / 1000.00;

						// Assign to Pool or Base based on Risk Type
						if( $pr->type == IQB_RISK_TYPE_BASIC )
						{
							$GROSS_PREMIUM += $premium;
						}
						else
						{
							$POOL_PREMIUM += $premium;
						}
					}



					/**
					 * Other computational data
					 */
					$premium_computation_table 	= [];
					$COMMISSIONABLE_PREMIUM 	= NULL;
					$AGENT_COMMISSION 			= NULL;
					$DIRECT_DISCOUNT 			= NULL;

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$DIRECT_DISCOUNT = ( $GROSS_PREMIUM * $pfs_record->direct_discount ) / 100.00 ;
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$COMMISSIONABLE_PREMIUM = $GROSS_PREMIUM;
						$AGENT_COMMISSION = ( $COMMISSIONABLE_PREMIUM * $pfs_record->agent_commission ) / 100.00;
					}


					/**
					 * Let's Compute the Total Basic Premium
					 */
					$NET_BASIC_PREMIUM 	= $GROSS_PREMIUM - $DIRECT_DISCOUNT ;


					/**
					 * Below Defautl Basic/Pool Premium Value? - For FRESH/RENEWAL ONLY
					 */
					$__flag_defualt_summary_table = FALSE;
					if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) )
					{
						$txn_data_defaults = [
							'amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
							'amt_pool_premium' 		=> $POOL_PREMIUM,
						];
						$defaults = [
							'basic' => floatval($pfs_record->amt_default_basic_premium),
							'pool' 	=> floatval($pfs_record->amt_default_pool_premium),
						];
						$txn_data_defaults = _ENDORSEMENT__tariff_premium_defaults( $txn_data_defaults, $defaults, TRUE);


						if(
							$txn_data_defaults['amt_basic_premium'] != $NET_BASIC_PREMIUM
							||
							$txn_data_defaults['amt_pool_premium'] != $POOL_PREMIUM )
						{
							$__flag_defualt_summary_table = TRUE;


							$txt_basic_premium = $txn_data_defaults['amt_basic_premium'] != $NET_BASIC_PREMIUM
													? 'BASIC PREMIUM (minimum)' : 'BASIC PREMIUM';
							$txt_pool_premium = $txn_data_defaults['amt_pool_premium'] != $POOL_PREMIUM
													? 'POOL PREMIUM (minimum)' : 'POOL PREMIUM';


							// Summary Table
							$summary_table = [
								[
									'label' => $txt_basic_premium,
									'value' => $txn_data_defaults['amt_basic_premium']
								],
								[
									'label' => $txt_pool_premium,
									'value' => $txn_data_defaults['amt_pool_premium']
								]
							];
						}

						// Update basic, pool for further computation
						$NET_BASIC_PREMIUM 	= $txn_data_defaults['amt_basic_premium'];
						$POOL_PREMIUM 		= $txn_data_defaults['amt_pool_premium'];
					}



					/**
					 * Cost calculation Table
					 *
					 * It holds three section:
					 * 	a. Summary Table
					 * 	b. Risk wise summary Table
					 * 	c. Property wise summary Table
					 */
					if( !$__flag_defualt_summary_table )
					{
						$summary_table = [
							[
								'label' => "BASIC PREMIUM",
								'value' => $GROSS_PREMIUM
							]
						];

						if($DIRECT_DISCOUNT)
						{
							$dd_formatted = number_format($pfs_record->direct_discount, 2);
							$summary_table[] = [
								'label' => "DIRECT DISCOUNT ({$dd_formatted}%)",
								'value' => $DIRECT_DISCOUNT
							];
						}

						$summary_table[] = [
							'label' => "POOL PREMIUM",
							'value' => $POOL_PREMIUM
						];
					}


					/**
					 * Cost Calculation Table - Schedule Data
					 *
					 * 	Risk Table
					 * 	------------------
					 * 	| Risk | Premium |
					 * 	------------------
					 * 	|	   |		 |
					 * 	------------------
					 */
					$risk_table = [];

					// Only Risk Table
					// Compute Gross and Pool Premium
					foreach($portfolio_risks as $pr)
					{
						// Rate in Per Thousand
						$rate = floatval($premium_data['rate'][$pr->id]);

						$per_risk_premium = $SI * $rate / 1000.00;
						$per_risk_base_premium 	= 0.00;
						$per_risk_pool_premium 	= 0.00;

						// Assign to Pool or Base based on Risk Type
						if( $pr->type == IQB_RISK_TYPE_BASIC )
						{
							$per_risk_base_premium = $per_risk_premium;
						}
						else
						{
							$per_risk_pool_premium = $per_risk_premium;
						}

						/**
						 * Direct Discount Applies?
						 */
						if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
						{
							$direct_discount 		= ( $per_risk_base_premium * $pfs_record->direct_discount ) / 100.00 ;
							$per_risk_base_premium 	-= $direct_discount;
						}
						$per_risk_premium 	= $per_risk_base_premium  + $per_risk_pool_premium;
						$risk_table[] 		= [$pr->name, $per_risk_premium];
					}

					$cost_calculation_table = json_encode([
						'summary_table' 	=> $summary_table,
						'risk_table'		=> $risk_table
					]);



					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'amt_basic_premium' 	=> $NET_BASIC_PREMIUM,
						'amt_commissionable'	=> $COMMISSIONABLE_PREMIUM,
						'amt_agent_commission'  => $AGENT_COMMISSION,
						'amt_direct_discount' 	=> $DIRECT_DISCOUNT,
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
					 * Premium Computation Table
					 * -------------------------
					 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
					 */
					$premium_computation_table = json_encode($post_data['premium']);


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