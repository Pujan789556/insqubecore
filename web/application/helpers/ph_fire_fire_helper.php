<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Fire Portfolio Helper Functions
 *
 * This file contains helper functions related to Fire Portfolio
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------
// PORTFOLIO OBJECT HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('_OBJ_FIRE_FIRE_row_snippet'))
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
	function _OBJ_FIRE_FIRE_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$CI =& get_instance();
		return $CI->load->view('objects/snippets/_row_fire_fire', ['record' => $record, '_flag__show_widget_row' => $_flag__show_widget_row], TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_select_text'))
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
	function _OBJ_FIRE_FIRE_select_text( $record )
	{
		$snippet = [
			'Sum Insured(NRS): ' . '<strong>' . $record->amt_sum_insured . '</strong>'
		];
		$snippet = implode('<br/>', $snippet);

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_validation_rules'))
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
	function _OBJ_FIRE_FIRE_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$CI =& get_instance();


		$conscat_dropdown 	= _OBJ_FIRE_FIRE_item_building_category_dropdown( FALSE );
		$district_dropdown 	= district_dropdown( FALSE );

		$v_rules = [
			/**
			 * Basic Data
			 */
			'basic' =>[
				[
			        'field' => 'object[item_attached]',
			        '_key' => 'item_attached',
			        'label' => 'Source Of Item List',
			        'rules' => 'trim|required|alpha|exact_length[1]|in_list[N,Y]',
			        '_data' 	=> ['N' => 'Manual entry', 'Y' => 'Excel data file'],
			        '_type'     => 'radio',
			        '_show_label' => true,
			        '_required' => true
			    ]
		    ],

		    /**
			 * Basic Data
			 */
			'items_file' =>[
				[
			        'field' => 'object[sum_insured]',
			        '_key' => 'sum_insured',
			        'label' => 'Total Sum Insured (Rs.)',
			        'rules' => 'trim|prep_decimal|decimal|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> true,
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
			 * Item List
			 */
			'items_manual' => _OBJ_FIRE_FIRE_manual_item_v_rules(),

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
			/**
			 * If excel file uploaded or item_attached as Y (edit), we do not need 'items_manual' validation rules
			 */
			// $post = object[item_attached]
			if( $CI->input->post() && $CI->input->post('object[item_attached]') == 'Y' )
			{
				// Set validation rules as non-required
				$v_rules['items_manual'] = _OBJ_FIRE_FIRE_manual_item_v_rules(TRUE);

				// Set total sum insured field as compulsory
				$v_rules['items_file'][0]['rules'] = 'trim|required|prep_decimal|decimal|max_length[20]';
			}

			foreach ($v_rules as $key=>$section)
			{
				$fromatted_v_rules = array_merge($fromatted_v_rules, $section);
			}

			// echo '<pre>'; print_r($fromatted_v_rules);exit;

			return $fromatted_v_rules;
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_manual_item_v_rules'))
{
	/**
	 * Get manual item validation rules
	 *
	 * If file is uploaded, then all field are non-required
	 *
	 * @param bool $file_uploaded 	Whether file is uploaded
	 * @return	array
	 */
	function _OBJ_FIRE_FIRE_manual_item_v_rules( $file_uploaded = false )
	{
		$category_dropdown 	= _OBJ_FIRE_FIRE_item_category_dropdown( FALSE );
		$ownership_dropdown = _OBJ_FIRE_FIRE_item_ownership_dropdown( FALSE );
		$required = $file_uploaded ? '' : 'required|';

		$rules = [
			[
		        'field' => 'object[items][category][]',
		        '_key' => 'category',
		        'label' => 'Item Category',
		        'rules' => 'trim|'.$required.'alpha|in_list['. implode(',', array_keys($category_dropdown)) .']',
		        '_type'     => 'dropdown',
		        '_data' 	=> IQB_BLANK_SELECT + $category_dropdown,
		        '_show_label' 	=> false,
		        '_required' => true
		    ],
		    [
		        'field' => 'object[items][description][]',
		        '_key' => 'description',
		        'label' => 'Item Description',
		        'rules' => 'trim|htmlspecialchars|max_length[500]',
		        '_type'     => 'textarea',
		        'rows' 		=> 4,
		        '_show_label' 	=> false,
		        '_required' => false
		    ],
		    [
		        'field' => 'object[items][sum_insured][]',
		        '_key' => 'sum_insured',
		        'label' => 'Item Price(Sum Insured)',
		        'rules' => 'trim|'.$required.'prep_decimal|decimal|max_length[20]',
		        '_type'     => 'text',
		        '_show_label' 	=> false,
		        '_required' => true
		    ],
		    [
		        'field' => 'object[items][ownership][]',
		        '_key' => 'ownership',
		        'label' => 'Item Ownership',
		        'rules' => 'trim|'.$required.'alpha|in_list['. implode(',', array_keys($ownership_dropdown)) .']',
		        '_type'     => 'dropdown',
		        '_data' 	=> IQB_BLANK_SELECT + $ownership_dropdown,
		        '_show_label' 	=> false,
		        '_required' 	=> true
		    ]
	    ];

	    return $rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_item_building_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item Building Construction category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_FIRE_item_building_category_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_FIRE_FIRE_item_category_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item category dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_FIRE_item_category_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_FIRE_FIRE_item_ownership_dropdown'))
{
	/**
	 * Get Policy Object - FIRE - Object's Item ownership dropdown
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	array
	 */
	function _OBJ_FIRE_FIRE_item_ownership_dropdown( $flag_blank_select = true )
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

if ( ! function_exists('_OBJ_FIRE_FIRE_pre_save_tasks'))
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
	function _OBJ_FIRE_FIRE_pre_save_tasks( array $data, $record )
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

		/**
		 * One must upload if "item_attached" flag is set on "Add" mode
		 */
		if( $CI->input->post('object[item_attached]') == 'Y' )
		{
			// Unset items attributues
			unset($data['object']['items']);


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
	        else
	        {
	        	/**
	        	 * You must upload a file in "Add" mode if "item_attached" is set.
	        	 */
	        	throw new Exception("Exception [Helper: ph_FIRE_FIRE_helper][Method: _OBJ_FIRE_FIRE_pre_save_tasks()]: " . $message );
	        }
		}

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_compute_sum_insured_amount'))
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
	function _OBJ_FIRE_FIRE_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$CI =& get_instance();

		/**
		 * If "item_attached" is Set, we have one placeholder to compute sum_insured value,
		 * else we compute from all items
		 *
		 */
		if ( $CI->input->post('object[item_attached]') == 'Y' )
		{
			$amt_sum_insured = floatval($data['sum_insured']);
			return $amt_sum_insured;
		}


		/**
		 * A single Fire Policy may hold multiple Items
		 */
		$sum_inusred_arr = $data['items']['sum_insured'];
		$amt_sum_insured = 0;
		foreach($sum_inusred_arr as $si)
		{
			$amt_sum_insured += $si;
		}
		return $amt_sum_insured;
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_FIRE_premium_validation_rules'))
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
	function _TXN_FIRE_FIRE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $policy_record->portfolio_id );

		$basic_rules = [
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
		];

		// Get validation rules based on the type (Manual Item entry or File Upload)
		$object_attributes 	= json_decode($policy_object->attributes ?? NULL);
		// echo '<pre>'; print_r($object_attributes);exit;

		if( $object_attributes->item_attached === 'Y')
		{
			$rules = _TXN_FIRE_FIRE_premium_v_rules_file($basic_rules, $portfolio_risks, $for_form_processing );
		}
		else
		{
			$rules = _TXN_FIRE_FIRE_premium_v_rules_manual($basic_rules, $policy_object, $portfolio_risks, $for_form_processing );
		}

		return $rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_FIRE_premium_v_rules_file'))
{
	/**
	 * Premium Validation Rules for File Upload
	 *
	 * @param array $basic_rules 		Basic Validation Rules
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_FIRE_FIRE_premium_v_rules_file($basic_rules, $portfolio_risks, $for_form_processing = FALSE )
	{
		$v_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => $basic_rules,


			// ---------------------------------------------------------------------
			// FILE UPLOAD PREMIUM VALIDATION RULES
			// ---------------------------------------------------------------------


			/**
			 * Discounts/Additional Chanrge Rates
			 * 	NWL(+) : Night Working Load (% of Fire Risk Premium) Premium
			 * 	FFA(-) : Fire Fighting Appliance (% of Fire Risk Premium) Discount
			 * 	SDD(-) : Stock Declaration Discount (% of total premium of Stock/Goods premium)
			 */
			'premium_file_additional_rates' => [
                [
	                'field' => 'premium[file][nwl_rate]',
	                'label' => 'NWL (Night Working Load)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'nwl_rate',
	                '_placeholder' => 'NWL Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][ffa_rate]',
	                'label' => 'FFA (Fire Fighting Appliance)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'ffa_rate',
	                '_placeholder' => 'FFA Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][sdd_rate]',
	                'label' => 'SDD (Stock Declaration Discount)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'sdd_rate',
	                '_placeholder' => 'SDD Rate (%)',
	                '_required' => true
	            ]
			],

			/**
			 * Discounts/Additional Chanrge Amount
			 */
			'premium_file_additional_amount' => [
                [
	                'field' => 'premium[file][nwl_amount]',
	                'label' => 'NWL (Night Working Load - Fire Only) (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'nwl_amount',
	                '_placeholder' => 'NWL (Rs.)',
	                '_show_label' => false,
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][ffa_amount]',
	                'label' => 'FFA (Fire Fighting Appliance - Fire Only) (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'ffa_amount',
	                '_placeholder' => 'FFA (Rs.)',
	                '_show_label' => false,
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][sdd_amount]',
	                'label' => 'SDD (Stock Declaration Discount) (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'sdd_amount',
	                '_placeholder' => 'SDD (Rs.)',
	                '_show_label' => false,
	                '_required' => true
	            ]
			],

			/**
			 * Premium File Risks
			 */
			'premium_file_risks' => [
                [
	                'field' => 'premium[file][risk]',
	                'label' => 'Risk',
	                'rules' => 'trim|required|integer|max_length[8]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][premium]',
	                'label' => 'Premium (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'premium',
	                '_required' => true
	            ]
			],
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$rules = array_merge( $v_rules['basic'], $v_rules['premium_file_additional_rates'], $v_rules['premium_file_additional_amount']);

			/**
			 * Append Risk validation rules
			 */
			$premium_file_risks_elements = $v_rules['premium_file_risks'];

			// Loop through each portfolio risks
			foreach($portfolio_risks as $risk_id=>$risk_name)
			{
				foreach ($premium_file_risks_elements as $elem)
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

// ------------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_FIRE_premium_v_rules_manual'))
{
	/**
	 * Premium Validation Rules for Manual Item Entry
	 *
	 * @param array $basic_rules 		Basic Validation Rules
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_FIRE_FIRE_premium_v_rules_manual($basic_rules, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{

		$v_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => $basic_rules,


			// ---------------------------------------------------------------------
			// MANUAL ITEM ENTRY PREMIUM VALIDATION RULES
			// ---------------------------------------------------------------------

			/**
			 * Discounts/Additional Chanrge Rates
			 * 	NWL(+) : Night Working Load (% of Fire Risk Premium) Premium
			 * 	FFA(-) : Fire Fighting Appliance (% of Fire Risk Premium) Discount
			 * 	SDD(-) : Stock Declaration Discount (% of total premium of Stock/Goods premium)
			 */
			'premium_manual_additional_rates' => [
                [
	                'field' => 'premium[manual][nwl_rate]',
	                'label' => 'NWL (Night Working Load)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'nwl_rate',
	                '_placeholder' => 'NWL Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][ffa_rate]',
	                'label' => 'FFA (Fire Fighting Appliance)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'ffa_rate',
	                '_placeholder' => 'FFA Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][sdd_rate]',
	                'label' => 'SDD (Stock Declaration Discount)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'sdd_rate',
	                '_placeholder' => 'SDD Rate (%)',
	                '_required' => true
	            ]
			],

			/**
			 * Premium Validation Rules - Template
			 */
			'premium_manual_risks' => [
                [
	                'field' => 'premium[manual][risk]',
	                'label' => 'Rate',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'hidden',
	                '_key' 		=> 'risk',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][rate]',
	                'label' => 'Rate',
	                'rules' => 'trim|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'rate',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][nwl_apply]',
	                'label' => 'Apply NWL',
	                'rules' => 'trim|integer|exact_length[1]|in_list[1]',
	                '_type'     => 'checkbox',
	                '_key' 		=> 'nwl_apply',
	                '_checkbox_value' => '1',
	                '_show_label' => false,
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[manual][ffa_apply]',
	                'label' => 'Apply FFA',
	                'rules' => 'trim|integer|exact_length[1]|in_list[1]',
	                '_type'     => 'checkbox',
	                '_key' 		=> 'ffa_apply',
	                '_checkbox_value' => '1',
	                '_show_label' => false,
	                '_required' => false
	            ],
	            [
	                'field' => 'premium[manual][sdd_apply]',
	                'label' => 'Apply SDD',
	                'rules' => 'trim|integer|exact_length[1]|in_list[1]',
	                '_type'     => 'checkbox',
	                '_key' 		=> 'sdd_apply',
	                '_checkbox_value' => '1',
	                '_show_label' => false,
	                '_required' => false
	            ],
			]
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$rules = array_merge( $v_rules['basic'], $v_rules['premium_manual_additional_rates']);

			/**
			 * Premium Validation Rules
			 */
			$premium_manual_risks_elements 	= $v_rules['premium_manual_risks'];
			$object_attributes 	= $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
			$items 				= $object_attributes->items;
			$item_count 		= count($items->category);

			// Per Item We have validation rules
			for($i=0; $i < $item_count; $i++ )
			{
				// Loop through each portfolio risks
				foreach($portfolio_risks as $risk_id=>$risk_name)
				{
					foreach ($premium_manual_risks_elements as $elem)
                    {
                    	$elem['field'] .= "[{$risk_id}][{$i}]";
                    	$rules[] = $elem;
                    }
				}
			}
			return $rules;
		}
		return $v_rules;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_TXN_FIRE_FIRE_premium_goodies'))
{
	/**
	 * Get Policy Policy Transaction Goodies
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
	function _TXN_FIRE_FIRE_premium_goodies($policy_record, $policy_object, $portfolio_risks)
	{
		$CI =& get_instance();

		// Portfolio Setting Record
		$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);

		// Let's Get the Validation Rules
		$validation_rules = _TXN_FIRE_FIRE_premium_validation_rules( $policy_record, $pfs_record, $policy_object, $portfolio_risks );


		// Return the goodies
		return  [
			'status' 			=> 'success',
			'validation_rules' 	=> $validation_rules,
			'tariff_record' 	=> NULL
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('__save_premium_FIRE_FIRE'))
{
	/**
	 * Fire Portfolio : Save a Policy Transaction Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $txn_record 	 	Policy Transaction Record
	 * @return json
	 */
	function __save_premium_FIRE_FIRE($policy_record, $txn_record)
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
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);

			/**
			 * Validation Rules for Form Processing
			 */
			$validation_rules = _TXN_FIRE_FIRE_premium_validation_rules($policy_record, $pfs_record, $policy_object, $portfolio_risks, TRUE );
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
					 * Fire Items with Sum Insured
					 */
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

					/**
					 * Initialization of Computational Variables
					 */
					$GROSS_PREMIUM 	= 0.00; // Gross Premium (Without Pool Premium)
					$POOL_PREMIUM  	= 0.00; // Pool Premium

					// Additional discount/charge rates & amounts
					$NWL_RATE 		= 0.00;
					$FFA_RATE 		= 0.00;
					$SDD_RATE 		= 0.00;
					$NWL_AMOUNT 	= 0.00;
					$FFA_AMOUNT 	= 0.00;
					$SDD_AMOUNT 	= 0.00;

					/**
					 * -------------------------------------------------------------------------------------
					 * CASE A : FIRE ITEMS AS FILE UPLOAD
					 * -------------------------------------------------------------------------------------
					 */
					if( $object_attributes->item_attached === 'Y' )
					{
						// Get additional discount/charge rate & amount
						$NWL_RATE 		= $premium_data['file']['nwl_rate'];
						$FFA_RATE 		= $premium_data['file']['ffa_rate'];
						$SDD_RATE 		= $premium_data['file']['sdd_rate'];
						$NWL_AMOUNT 	= $premium_data['file']['nwl_amount'];
						$FFA_AMOUNT 	= $premium_data['file']['ffa_amount'];
						$SDD_AMOUNT 	= $premium_data['file']['sdd_amount'];

						// Compute Gross and Pool Premium
						foreach($portfolio_risks as $pr)
						{
							$premium = floatval($premium_data['file']['premium'][$pr->id]);

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
					}

					/**
					 * -------------------------------------------------------------------------------------
					 * CASE B : FIRE ITEMS AS MANUAL ENTRY
					 * -------------------------------------------------------------------------------------
					 */
					else
					{
						// Get additional discount/charge rate & amount
						$NWL_RATE 		= $premium_data['manual']['nwl_rate'];
						$FFA_RATE 		= $premium_data['manual']['ffa_rate'];
						$SDD_RATE 		= $premium_data['manual']['sdd_rate'];


						// Get Fire Items
						$items              = $object_attributes->items;
						$item_count         = count($items->category);

						for($i=0; $i < $item_count; $i++ )
						{
							$item_sum_insured 	= $items->sum_insured[$i];
							foreach($portfolio_risks as $pr)
							{
								// Rate is Per Thousand of Sum Insured
								$rate = floatval($premium_data['manual']['rate'][$pr->id][$i]);

								// Compute only if rate is supplied
								if($rate)
								{
									$premium = $item_sum_insured * $rate / 1000.00;

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$GROSS_PREMIUM += $premium;
									}
									else
									{
										$POOL_PREMIUM += $premium;
									}

									// Additional Charges/Discount (Per Risk Per Item)
									$nwl_apply = $premium_data['manual']['nwl_apply'][$pr->id][$i] ?? NULL;
									$ffa_apply = $premium_data['manual']['ffa_apply'][$pr->id][$i] ?? NULL;
									$sdd_apply = $premium_data['manual']['sdd_apply'][$pr->id][$i] ?? NULL;

									$NWL_AMOUNT 	+= $nwl_apply ? ( $premium * $NWL_RATE / 100.00 ) : 0;
									$FFA_AMOUNT 	+= $ffa_apply ? ( $premium * $FFA_RATE / 100.00 ) : 0;
									$SDD_AMOUNT 	+= $sdd_apply ? ( $premium * $SDD_RATE / 100.00 ) : 0;
								}
							}
						}
					}


					/**
					 * Other computational data
					 */
					$premium_computation_table 	= [];
					$COMMISSIONABLE_PREMIUM 	= NULL;
					$AGENT_COMMISSION 			= NULL;
					$DIRECT_DISCOUNT 			= 0.00;

					/**
					 * Direct Discount or Agent Commission?
					 * ------------------------------------
					 *
					 * Note: Direct Discount applies only on Base Premium
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
					{
						$DIRECT_DISCOUNT = ( $GROSS_PREMIUM * $pfs_record->direct_discount ) / 100.00;
					}
					else if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$COMMISSIONABLE_PREMIUM = $GROSS_PREMIUM;
						$AGENT_COMMISSION = ( $COMMISSIONABLE_PREMIUM * $pfs_record->agent_commission ) / 100.00;
					}


					/**
					 * Let's Compute the Total Premium
					 */
					$NET_PREMIUM 	= $GROSS_PREMIUM - $DIRECT_DISCOUNT + $NWL_AMOUNT - $FFA_AMOUNT - $SDD_AMOUNT + $POOL_PREMIUM ;
					$taxable_amount = $NET_PREMIUM + $post_data['amt_stamp_duty'];

					/**
					 * Compute VAT
					 */
					$CI->load->helper('account');
					$AMOUNT_VAT = ac_compute_tax(IQB_AC_DNT_ID_VAT, $taxable_amount);


					/**
					 * Prepare Transactional Data
					 */
					$txn_data = [
						'amt_sum_insured' 		=> $policy_object->amt_sum_insured,
						'amt_total_premium' 	=> $NET_PREMIUM,
						'amt_pool_premium' 		=> $POOL_PREMIUM,
						'amt_commissionable'	=> $COMMISSIONABLE_PREMIUM,
						'amt_agent_commission'  => $AGENT_COMMISSION,
						'amt_stamp_duty' 		=> $post_data['amt_stamp_duty'],
						'amt_vat' 				=> $AMOUNT_VAT,
						'txn_details' 			=> $post_data['txn_details'],
						'remarks' 				=> $post_data['remarks'],
					];


					/**
					 * Premium Computation Table
					 * -------------------------
					 * This should hold the variable structure exactly so as to populate on _form_premium_FIRE.php
					 */
					$premium_computation_table = json_encode($post_data['premium']);
					$txn_data['premium_computation_table'] = $premium_computation_table;



					/**
					 * Cost calculation Table
					 *
					 * It holds three section:
					 * 	a. Summary Table
					 * 	b. Risk wise summary Table
					 * 	c. Property wise summary Table
					 */

					$summary_table = [
						[
							'label' => "GROSS PREMIUM",
							'value' => $GROSS_PREMIUM
						]
					];

					if($DIRECT_DISCOUNT)
					{
						$summary_table[] = [
							'label' => "DIRECT DISCOUNT ({$pfs_record->direct_discount}%)",
							'value' => $DIRECT_DISCOUNT
						];
					}

					$summary_table = array_merge($summary_table, [
						[
							'label' => "NWL - Fire Only ({$NWL_RATE}%)",
							'value' => $NWL_AMOUNT
						],
						[
							'label' => "FFA - Fire Only ({$FFA_RATE}%)",
							'value' => $FFA_AMOUNT
						],
						[
							'label' => "SDD - Fire Only ({$SDD_RATE}%)",
							'value' => $SDD_AMOUNT
						],
						[
							'label' => "POOL PREMIUM",
							'value' => $POOL_PREMIUM
						],
						[
							'label' => "NET PREMIUM",
							'value' => $NET_PREMIUM
						]
					]);



					/**
					 * Cost Calculation Table - Schedule Data
					 *
					 * 	Property Details
					 * 	------------------------------------
					 * 	| Property | Sum Insured | Premium |
					 * 	------------------------------------
					 *  |		   | 			 |		   |
					 * 	------------------------------------
					 *
					 * 	Risk Details
					 * 	------------------
					 * 	| Risk | Premium |
					 * 	------------------
					 * 	|	   |		 |
					 * 	------------------
					 */
					$property_table = [];
					$risk_table = [];

					if( $object_attributes->item_attached === 'Y' )
					{
						// Only Risk Table
						// Compute Gross and Pool Premium
						foreach($portfolio_risks as $pr)
						{
							$per_risk_premium = floatval($premium_data['file']['premium'][$pr->id]);
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
					}
					else
					{

						for($i=0; $i < $item_count; $i++ )
						{
							$item_sum_insured 		= $items->sum_insured[$i];
							$property_category 		= _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ];
							$single_property_row 	= [ $property_category, $items->sum_insured[$i] ];

							$per_property_premium 		= 0.00;
							$per_property_base_premium 	= 0.00;
							$per_property_pool_premium 	= 0.00;

							foreach($portfolio_risks as $pr)
							{
								$rate = floatval($premium_data['manual']['rate'][$pr->id][$i]);

								// Compute only if rate is supplied
								if($rate)
								{
									// Rate is Per Thousand
									$premium = $item_sum_insured * $rate / 1000.00;

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$per_property_base_premium += $premium;
									}
									else
									{
										$per_property_pool_premium += $premium;
									}
								}
							}

							/**
							 * Direct Discount Applies?
							 */
							if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
							{
								$direct_discount 			= ( $per_property_base_premium * $pfs_record->direct_discount ) / 100.00 ;
								$per_property_base_premium -= $direct_discount;
							}

							$per_property_premium 	= $per_property_base_premium  + $per_property_pool_premium;
							$single_property_row[] 	= $per_property_premium;
							$property_table[] 		= $single_property_row;
						}

						// --------------------------------------------------------------------------------------------


						foreach($portfolio_risks as $pr)
						{
							$per_risk_premium 		= 0.00;
							$per_risk_base_premium 	= 0.00;
							$per_risk_pool_premium 	= 0.00;

							for($i=0; $i < $item_count; $i++ )
							{
								$item_sum_insured 	= $items->sum_insured[$i];
								$rate = floatval($premium_data['manual']['rate'][$pr->id][$i]);

								// Compute only if rate is supplied
								if($rate)
								{
									// Rate is Per Thousand
									$premium = $item_sum_insured * $rate / 1000.00;

									// Assign to Pool or Base based on Risk Type
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$per_risk_base_premium += $premium;
									}
									else
									{
										$per_risk_pool_premium += $premium;
									}
								}
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


							/**
							 * Include the risk only with premium
							 */
							if( $per_risk_premium )
							{
								$risk_table[] 		= [$pr->name, $per_risk_premium];
							}
						}
					}

					$cost_calculation_table = json_encode([
						'summary_table' 	=> $summary_table,
						'property_table' 	=> $property_table,
						'risk_table'		=> $risk_table
					]);

					$txn_data['cost_calculation_table'] = $cost_calculation_table;

					$done = $CI->policy_txn_model->save($txn_record->id, $txn_data);

					return $done;

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