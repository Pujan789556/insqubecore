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
		$district_dropdown 	= district_dropdown( 'both', FALSE );

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
			        'rules' => 'trim|max_length[100]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][ward_no][]',
			        '_key' => 'ward_no',
			        'label' => 'Ward No.',
			        'rules' => 'trim|max_length[20]',
			        '_type'     => 'text',
			        '_show_label' 	=> false,
			        '_required' => true
			    ],
			    [
			        'field' => 'object[land_building][storey_no][]',
			        '_key' => 'storey_no',
			        'label' => 'No. of Stories',
			        'rules' => 'trim|max_length[10]',
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
			'1' => 'पहिलो श्रेणी',
			'2' => 'दोस्रो श्रेणी',
			'3'	=> 'तेस्रो श्रेणी',
			'4' => 'चौथो श्रेणी',
			'5' => 'खुल्ला'
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
			'BWALL' => 'सिमाना पर्खाल',
			'BLDNG' => 'भवन',
			'GOODS'	=> 'मालसामान',
			'MCNRY' => 'मशीनरी',
			'OTH' 	=> 'अन्य'
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
			'O' => 'निजी स्वामित्व',
			'R' => 'भाडामा'
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
                'upload_path' 	=> Objects::$data_upload_path,
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
		else
		{
			/**
			 * Format Items
			 */
			$data = _OBJ_FIRE_FIRE_format_items($data);
		}

		return $data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_FIRE_FIRE_format_items'))
{
	/**
	 * Format Fire Object Items
	 *
	 * @param array $data 		Post Data
	 * @return array
	 */
	function _OBJ_FIRE_FIRE_format_items( array $data )
	{
		$items 		= $data['object']['items'];
		$item_rules = _OBJ_FIRE_FIRE_manual_item_v_rules();

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

		$amt_sum_insured = 0.00;

		/**
		 * If "item_attached" is Set, we have one placeholder to compute sum_insured value,
		 * else we compute from all items
		 *
		 */
		if ( $CI->input->post('object[item_attached]') == 'Y' )
		{
			$amt_sum_insured = floatval($data['sum_insured']);
		}
		else
		{
			$items = $data['items'];
			foreach($items as $single)
			{
				$si_per_item = $single['sum_insured'];
				// Clean all formatting ( as data can come from excel sheet with comma on thousands eg. 10,00,000.00 )
				$si_per_item 	= (float) filter_var($si_per_item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$amt_sum_insured +=  $si_per_item;
			}


		}

		// NO SI Breakdown for this Portfolio
		return ['amt_sum_insured' => $amt_sum_insured];
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

		// Basic/Common Validation Rules
		$basic_rules = _ENDORSEMENT_premium_basic_v_rules( $policy_record->portfolio_id, $pfs_record );

		// Installment Rules
		$installment_rules = _POLICY_INSTALLMENT_validation_rules( $policy_record->portfolio_id, $pfs_record );


		// Get validation rules based on the type (Manual Item entry or File Upload)
		$object_attributes 	= json_decode($policy_object->attributes ?? NULL);

		if( $object_attributes->item_attached === 'Y')
		{
			$rules = _TXN_FIRE_FIRE_premium_v_rules_file($basic_rules, $installment_rules, $portfolio_risks, $for_form_processing );
		}
		else
		{
			$rules = _TXN_FIRE_FIRE_premium_v_rules_manual($basic_rules, $installment_rules, $policy_object, $portfolio_risks, $for_form_processing );
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
	 * @param array $installment_rules 	Installment Validation Rules
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_FIRE_FIRE_premium_v_rules_file($basic_rules, $installment_rules, $portfolio_risks, $for_form_processing = FALSE )
	{
		$v_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => $basic_rules,

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => $installment_rules,


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
	                'field' => 'premium[file][sdd_amount_regular]',
	                'label' => 'SDD (Regular Premium) (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'sdd_amount_regular',
	                '_placeholder' => 'SDD - REGULAR (Rs.)',
	                '_show_label' => false,
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[file][sdd_amount_pool]',
	                'label' => 'SDD (Pool Premium) (Rs.)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
	                '_type'     => 'text',
	                '_key' 		=> 'sdd_amount_pool',
	                '_placeholder' => 'SDD - POOL (Rs.)',
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
			foreach($portfolio_risks as $risk_code=>$risk_name)
			{
				foreach ($premium_file_risks_elements as $elem)
                {
                	$elem['field'] .= "[{$risk_code}]";
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
	 * @param array $installment_rules 	Installment Validation Rules
	 * @param object $policy_object		Policy Object Record
	 * @param array $portfolio_risks	Portfolio Risks
	 * @param bool $for_processing		For Form Processing
	 * @return array
	 */
	function _TXN_FIRE_FIRE_premium_v_rules_manual($basic_rules, $installment_rules, $policy_object, $portfolio_risks, $for_form_processing = FALSE )
	{

		$v_rules = [

			/**
			 * Common to All Package Type
			 * ----------------------------
			 * Sampusti Bibaran and Remarks are common to all type of policy package.
			 */
			'basic' => $basic_rules,

			/**
			 * Installment Validation Rules (Common to all portfolios)
			 */
			'installments' => $installment_rules,


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
	                'label' => 'NWL (Night Working Load %)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'nwl_rate',
	                '_placeholder' => 'NWL Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][ffa_rate]',
	                'label' => 'FFA (Fire Fighting Appliance %)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'ffa_rate',
	                '_placeholder' => 'FFA Rate(%)',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual][sdd_rate]',
	                'label' => 'SDD (Stock Declaration Discount %)',
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
	                'label' => 'Risk Name',
	                'rules' => 'trim|alpha_numeric|max_length[20]',
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
			],

			/**
			 * Manual Discount
			 *
			 * After premium computation, this is the manual discount given.
			 * This mainly applies on Underconstruction Building.
			 */
			'manual_discount' => [
                [
	                'field' => 'premium[manual_discount][title]',
	                'label' => 'Discount Title',
	                'rules' => 'trim|required|max_length[100]',
	                '_type'     => 'text',
	                '_key' 		=> 'title',
	                '_placeholder' => 'Discount Title (Basic)',
	                '_help_text' => 'e.g. Underconstruction Discount',
	                '_default' => 'N/A',
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual_discount][basic_rate]',
	                'label' => 'Discount on Basic Premium (%)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'basic_rate',
	                '_placeholder' => 'Discount %',
	                '_default' => 0,
	                '_required' => true
	            ],
	            [
	                'field' => 'premium[manual_discount][pool_rate]',
	                'label' => 'Discount on Pool Premium (%)',
	                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]',
	                '_type'     => 'text',
	                '_key' 		=> 'pool_rate',
	                '_placeholder' => 'Discount %',
	                '_default' => 0,
	                '_required' => true
	            ],
			],
		];

		/**
		 * Build Form Validation Rules for Form Processing
		 */
		if( $for_form_processing )
		{
			$rules = array_merge( $v_rules['basic'], $v_rules['premium_manual_additional_rates'], $v_rules['manual_discount']);

			/**
			 * Premium Validation Rules
			 */
			$premium_manual_risks_elements 	= $v_rules['premium_manual_risks'];
			$object_attributes 	= $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
			$items 				= $object_attributes->items;
			$item_count 		= count($items);

			// Per Item We have validation rules
			$i = 0;
			foreach($items as $item_record)
			{
				// Loop through each portfolio risks
				foreach($portfolio_risks as $risk_code=>$risk_name)
				{
					foreach ($premium_manual_risks_elements as $elem)
                    {
                    	$elem['field'] .= "[{$risk_code}][{$i}]";
                    	$rules[] = $elem;
                    }
				}
				$i++;
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
	 * Fire Portfolio : Save a Endorsement Record For Given Policy
	 *
	 *	!!! Important: Fresh/Renewal Only
	 *
	 * @param object $policy_record  	Policy Record
	 * @param object $endorsement_record 	 	Endorsement Record
	 * @return json
	 */
	function __save_premium_FIRE_FIRE($policy_record, $endorsement_record)
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

			/**
			 * Portfolio Setting Record
			 */
			$pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);


			/**
			 * !!! MANUAL PREMIUM COMPUTATION ENDORSEMENT !!!
			 *
			 * Manual Endorsement should be done on
			 * 	- Premium Upgrade
			 * 	- Premium Refund
			 */
			if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
			{
				try{

					return _ENDORSEMENT__save_premium_manual($policy_record, $endorsement_record, $CI->input->post());

				} catch (Exception $e){

					return $CI->template->json([
						'status' 	=> 'error',
						'message' 	=> $e->getMessage()
					], 404);
				}
			}

			/**
			 * Portfolio Risks
			 */
			$portfolio_risks = $CI->portfolio_model->dropdown_risks($policy_record->portfolio_id);
			if(!$portfolio_risks)
			{
				return $CI->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Portfolio Risks Missing (Fire Policy)!',
					'message' 	=> 'Please setup portifolio risks from Setup.<br/>Contact Administrator for further support.'
				], 404);
			}

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
					$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

					/**
					 * Portfolio Risks Rows
					 */
					$portfolio_risks = $CI->portfolio_model->portfolio_risks($policy_record->portfolio_id);

					/**
					 * Initialization of Computational Variables
					 */
					$GROSS_REGULAR_PREMIUM 	= 0.00; // Gross Premium (Without Pool Premium)
					$GROSS_POOL_PREMIUM  	= 0.00; // Pool Premium

					$NET_REGULAR_PREMIUM 	= 0.00; // Net Premium (Without Pool Premium)
					$NET_POOL_PREMIUM  		= 0.00; // Net Pool Premium

					// Additional discount/charge rates & amounts
					$NWL_RATE 		= 0.00;
					$FFA_RATE 		= 0.00;
					$SDD_RATE 		= 0.00;
					$NWL_AMOUNT 	= 0.00;
					$FFA_AMOUNT 	= 0.00;
					$SDD_AMOUNT_REGULAR 	= 0.00;
					$SDD_AMOUNT_POOL 		= 0.00;
					$DIRECT_DISCOUNT 		= 0.00;

					$__flag_minimum_summary_table = FALSE;

					// Risk Distribution Table
					$risk_table = [];


					/**
					 * -------------------------------------------------------------------------------------
					 * CASE A : FIRE ITEMS AS FILE UPLOAD
					 * -------------------------------------------------------------------------------------
					 */
					if( $object_attributes->item_attached === 'Y' )
					{
						// Get additional discount/charge rate & amount
						$NWL_RATE 		= floatval($premium_data['file']['nwl_rate']);
						$FFA_RATE 		= floatval($premium_data['file']['ffa_rate']);
						$SDD_RATE 		= floatval($premium_data['file']['sdd_rate']);
						$NWL_AMOUNT 	= floatval($premium_data['file']['nwl_amount']);
						$FFA_AMOUNT 	= floatval($premium_data['file']['ffa_amount']);
						$SDD_AMOUNT_REGULAR 	= floatval($premium_data['file']['sdd_amount_regular']);
						$SDD_AMOUNT_POOL 		= floatval($premium_data['file']['sdd_amount_pool']);

						// GROSS - Regular & Pool
						foreach($portfolio_risks as $pr)
						{
							$premium_per_risk = floatval($premium_data['file']['premium'][$pr->code]);


							if( $pr->type == IQB_RISK_TYPE_BASIC )
							{
								$GROSS_REGULAR_PREMIUM += $premium_per_risk;
							}
							else
							{
								$GROSS_POOL_PREMIUM += $premium_per_risk;
							}


							// Update Risk Table
							$risk_table[] 		= [$pr->name, $premium_per_risk];
						}

						// NET - Regular & Pool
						$NET_REGULAR_PREMIUM 	= $GROSS_REGULAR_PREMIUM + $NWL_AMOUNT - $FFA_AMOUNT - $SDD_AMOUNT_REGULAR;
						$NET_POOL_PREMIUM 		= $GROSS_POOL_PREMIUM  - $SDD_AMOUNT_POOL;
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
						$item_count         = count($items);

						// Risk table tmp
						$__RISKS_TABLE = [];

						$i = 0;
						foreach($items as $item_record)
						{
							$item_sum_insured 	= $item_record->sum_insured;

							foreach($portfolio_risks as $pr)
							{
								// Rate is Per Thousand of Sum Insured
								$premium_per_risk 	= 0.00;
								$rate 				= floatval($premium_data['manual']['rate'][$pr->code][$i]);

								// Compute only if rate is supplied
								if($rate)
								{
									$premium_per_risk = $item_sum_insured * $rate / 1000.00;

									// GROSS - Regular & Pool
									if( $pr->type == IQB_RISK_TYPE_BASIC )
									{
										$GROSS_REGULAR_PREMIUM += $premium_per_risk;
									}
									else
									{
										$GROSS_POOL_PREMIUM += $premium_per_risk;
									}

									// Additional Charges/Discount (Per Risk Per Item)
									$nwl_apply = $premium_data['manual']['nwl_apply'][$pr->code][$i] ?? NULL;
									$ffa_apply = $premium_data['manual']['ffa_apply'][$pr->code][$i] ?? NULL;
									$sdd_apply = $premium_data['manual']['sdd_apply'][$pr->code][$i] ?? NULL;

									/**
									 * NWL Compute
									 */
									if($nwl_apply)
									{
										$nwl_per_risk 		= $premium_per_risk * $NWL_RATE / 100.00;
										$premium_per_risk 	+= $nwl_per_risk;
										$NWL_AMOUNT 		+= $nwl_per_risk;
									}

									/**
									 * FFA Compute
									 */
									if($ffa_apply)
									{
										$ffa_per_risk 		= $premium_per_risk * $FFA_RATE / 100.00;
										$premium_per_risk 	-= $ffa_per_risk;
										$FFA_AMOUNT 		+= $ffa_per_risk;
									}

									/**
									 * SDD Compute
									 */
									if($sdd_apply)
									{
										$sdd_per_risk 		= $premium_per_risk * $SDD_RATE / 100.00;
										$premium_per_risk 	-= $sdd_per_risk;

										if( $pr->type == IQB_RISK_TYPE_BASIC )
										{
											$SDD_AMOUNT_REGULAR += $sdd_per_risk;
										}
										else
										{
											$SDD_AMOUNT_POOL += $sdd_per_risk;
										}
									}


									/**
									 * Apply Direct Discount
									 */
									if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_DIRECT && $pr->type == IQB_RISK_TYPE_BASIC )
									{
										// @TODO : Do we need to check __flag_minimum_summary_table fro direct discount

										$dd_per_item 		= ( $premium_per_risk * $pfs_record->direct_discount ) / 100.00;
										$premium_per_risk 	-= $dd_per_item;

										$DIRECT_DISCOUNT 	+= $dd_per_item;
									}


									// Risk-wise Breakdown - summing all items on risk-basis
									$__RISKS_TABLE[$pr->code]['premium'] = $__RISKS_TABLE[$pr->code]['premium'] ?? 0.00;
									$__RISKS_TABLE[$pr->code]['premium'] = $__RISKS_TABLE[$pr->code]['premium'] + $premium_per_risk;
									$__RISKS_TABLE[$pr->code]['risk_config'] = $pr;

								}
							}

							$i++;
						}

						/**
						 * Compute NET Regular, Pool Premium, Risk Table
						 */
						foreach($__RISKS_TABLE as $risk_code=>$tmp_data)
						{
							$premium_per_risk 	= $tmp_data['premium'];
							$risk_config 		= $tmp_data['risk_config'];
							$risk_name = $risk_config->name_np;
							if( _ENDORSEMENT_is_first( $endorsement_record->txn_type) && $premium_per_risk < $risk_config->default_min_premium )
							{
								$premium_per_risk = $risk_config->default_min_premium;
								$risk_name .= ' (minimum)';

								$__flag_minimum_summary_table = TRUE;
							}

							// NET - Regular & Pool
							if( $risk_config->type == IQB_RISK_TYPE_BASIC )
							{
								$NET_REGULAR_PREMIUM += $premium_per_risk;
							}
							else
							{
								$NET_POOL_PREMIUM += $premium_per_risk;
							}

							// Update risk table
							$risk_table[] 		= [$risk_name, '-', $premium_per_risk];
						}
					}


					/**
					 * Other computational data
					 */
					$premium_computation_table 	= [];
					$COMMISSIONABLE_PREMIUM 	= NULL;
					$AGENT_COMMISSION 			= NULL;


					/**
					 * Agent Commission?
					 * ------------------------------------
					 *
					 */
					if( $policy_record->flag_dc == IQB_POLICY_FLAG_DC_AGENT_COMMISSION )
					{
						$COMMISSIONABLE_PREMIUM = $NET_REGULAR_PREMIUM;
						$AGENT_COMMISSION = ( $COMMISSIONABLE_PREMIUM * $pfs_record->agent_commission ) / 100.00;
					}


					/**
					 * Let's Compute the Total Premium
					 */
					$BASIC_PREMIUM 	= $NET_REGULAR_PREMIUM;


					/**
					 * Cost calculation Table
					 *
					 * It holds three section:
					 * 	a. Summary Table
					 * 	b. Risk wise summary Table
					 * 	c. Property wise summary Table
					 */
					if( !$__flag_minimum_summary_table )
					{
						$summary_table = [
							[
								'label' => "GROSS PREMIUM",
								'value' => $GROSS_REGULAR_PREMIUM
							],
							[
								'label' => "GROSS POOL PREMIUM",
								'value' => $GROSS_POOL_PREMIUM
							],
							[
								'label' => "NWL - Fire Only ({$NWL_RATE}%)",
								'value' => $NWL_AMOUNT
							],
							[
								'label' => "FFA - Fire Only ({$FFA_RATE}%)",
								'value' => $FFA_AMOUNT
							],
							[
								'label' => "SDD - Regular ({$SDD_RATE}%)",
								'value' => $SDD_AMOUNT_REGULAR
							],
							[
								'label' => "SDD - Pool ({$SDD_RATE}%)",
								'value' => $SDD_AMOUNT_POOL
							]
						];

						if($DIRECT_DISCOUNT)
						{
							$summary_table[] = [
								'label' => "DIRECT DISCOUNT ({$pfs_record->direct_discount}%)",
								'value' => $DIRECT_DISCOUNT
							];
						}


						/**
						 * Manual Discount
						 */
						$manual_discount = $premium_data['manual_discount'];
						$md_title 	= $premium_data['manual_discount']['title'];
						$basic_rate = $premium_data['manual_discount']['basic_rate'];
						$pool_rate 	= $premium_data['manual_discount']['pool_rate'];

						if( $basic_rate > 0 || $pool_rate > 0 )
						{

							/**
							 * Discount on Basic Premium
							 */
							$md_basic = $BASIC_PREMIUM * $basic_rate / 100.00;
							$BASIC_PREMIUM -= $md_basic;

							if($md_basic)
							{
								$summary_table[] = [
									'label' => "{$md_title} ( Basic -{$basic_rate}%)",
									'value' => $md_basic
								];
							}

							/**
							 * Discount on Pool Premium
							 */
							$md_pool = $NET_POOL_PREMIUM * $pool_rate / 100.00;
							$NET_POOL_PREMIUM -= $md_pool;

							if($md_pool)
							{
								$summary_table[] = [
									'label' => "{$md_title} ( Pool -{$pool_rate}%)",
									'value' => $md_pool
								];
							}

						}
					}
					else
					{
						// Summary Table
						$summary_table = [
							[
								'label' => 'BASIC PREMIUM',
								'value' => $BASIC_PREMIUM
							],
							[
								'label' => 'POOL PREMIUM',
								'value' => $NET_POOL_PREMIUM
							]
						];
					}


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
					 * 	Risk Details - Computed Above
					 * 	------------------
					 * 	| Risk | Premium |
					 * 	------------------
					 * 	|	   |		 |
					 * 	------------------
					 */
					$property_table = [];

					// Property table for manual item entry
					if( $object_attributes->item_attached === 'N' )
					{
						$i = 0;
						foreach($items as $item_record)
						{
							$item_sum_insured 		= $item_record->sum_insured;
							$property_category 		= _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item_record->category ];
							$single_property_row 	= [ $property_category, $item_record->sum_insured ];

							$per_property_premium 		= 0.00;
							$per_property_base_premium 	= 0.00;
							$per_property_pool_premium 	= 0.00;

							foreach($portfolio_risks as $pr)
							{
								$rate = floatval($premium_data['manual']['rate'][$pr->code][$i]);

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

							$per_property_premium 	= $per_property_base_premium  + $per_property_pool_premium;
							$single_property_row[] 	= $per_property_premium;
							$property_table[] 		= $single_property_row;
						}
						$i++;
					}

					$cost_calculation_table = [
						'summary_table' 	=> $summary_table,
						'property_table' 	=> $property_table,
						'risk_table'		=> $risk_table
					];


					// -----------------------------------------------------------------------------

					/**
					 * Prepare Premium Data
					 */
					$premium_data = [
						'gross_amt_basic_premium' 	=> $BASIC_PREMIUM,
						'gross_amt_commissionable'	=> $COMMISSIONABLE_PREMIUM,
						'gross_amt_agent_commission'  => $AGENT_COMMISSION,
						'gross_amt_direct_discount' 	=> $DIRECT_DISCOUNT,
						'gross_amt_pool_premium' 		=> $NET_POOL_PREMIUM,
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