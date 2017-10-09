<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Policy Object Helper Functions
 *
 * This file contains helper functions related to policy
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------
// GENERAL OBJECT HELPERS
// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_row_snippet'))
{
	/**
	 * Get Policy Object  - Row Snippet
	 *
	 * Row Partial View for An Object
	 *
	 * @param object $record Policy Object
	 * @param bool $_flag__show_widget_row 	is this Widget Row? or Regular List Row?
	 * @return	html
	 */
	function _OBJ_row_snippet( $record, $_flag__show_widget_row = FALSE )
	{
		$snippet = '';

		// Load Portfolio Helper
		load_portfolio_helper($record->portfolio_id);

		/**
		 * MOTOR
		 * -----
		 */
		if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$snippet = _OBJ_MOTOR_row_snippet($record, $_flag__show_widget_row);
		}

		/**
		 * FIRE
		 * -----
		 */
		else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$snippet = _OBJ_FIRE_row_snippet($record, $_flag__show_widget_row);
		}

		/**
		 * MARINE
		 * -----
		 */
		else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$snippet = _OBJ_MARINE_row_snippet($record, $_flag__show_widget_row);
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $snippet = _OBJ_ENG_BL_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $snippet = _OBJ_ENG_CAR_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $snippet = _OBJ_ENG_CPM_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $snippet = _OBJ_ENG_EEI_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $snippet = _OBJ_ENG_EAR_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $snippet = _OBJ_ENG_MB_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * Throw Exception
         */
        else
		{
			throw new Exception("Exception [Helper: object_helper][Method: _OBJ_row_snippet()]: No row snippet method defined for supplied portfolio.");
		}

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_select_text'))
{
	/**
	 * Get Policy Object  - Selection Text or Summary Text
	 *
	 * Useful while add/edit-ing a policy or whenever we need to
	 * show the object summary from object attribute
	 *
	 *
	 * @param object $record 	Object Record
	 * @return	string
	 */
	function _OBJ_select_text( $record )
	{
		$snippet = '';

		// Load Portfolio Helper
		load_portfolio_helper($record->portfolio_id);

		/**
		 * MOTOR
		 * -----
		 */
		if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$snippet = _OBJ_MOTOR_select_text($record);
		}

		/**
		 * FIRE
		 * -----
		 */
		else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$snippet = _OBJ_FIRE_select_text($record);
		}

		/**
		 * MARINE
		 * -------
		 */
		else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$snippet = _OBJ_MARINE_select_text($record);
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $snippet = _OBJ_ENG_BL_select_text($record);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $snippet = _OBJ_ENG_CAR_select_text($record);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $snippet = _OBJ_ENG_CPM_select_text($record);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $snippet = _OBJ_ENG_EEI_select_text($record);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $snippet = _OBJ_ENG_EAR_select_text($record);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $snippet = _OBJ_ENG_MB_select_text($record);
        }

        /**
         * Throw Exception
         */
        else
		{
			throw new Exception("Exception [Helper: object_helper][Method: _OBJ_select_text()]: No select text method defined for supplied portfolio.");
		}

		return $snippet;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_validation_rules'))
{
	/**
	 * Get Policy Object  - Validation Rules
	 *
	 * Row Partial View for An Object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param bool $formatted  Should Return the Formatted Validation Rule ( if multi senction rules )
	 * @return	array
	 */
	function _OBJ_validation_rules( $portfolio_id, $formatted = FALSE )
	{
		$v_rules = [];

		// Load Portfolio Helper
		load_portfolio_helper($portfolio_id);

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same validation rules
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$v_rules = _OBJ_MOTOR_validation_rules( $portfolio_id, $formatted );
		}


		/**
		 * FIRE
		 * -----
		 * For all type of FIRE portfolios, we have same validation rules
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$v_rules = _OBJ_FIRE_validation_rules( $portfolio_id, $formatted );
		}

		/**
		 * MARINE
		 * -----
		 * For all type of MARINE portfolios, we have same validation rules
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$v_rules = _OBJ_MARINE_validation_rules( $portfolio_id, $formatted );
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise Validation Rules
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $v_rules = _OBJ_ENG_BL_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $v_rules = _OBJ_ENG_CAR_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise Validation Rules
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $v_rules = _OBJ_ENG_CPM_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $v_rules = _OBJ_ENG_EEI_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $v_rules = _OBJ_ENG_EAR_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $v_rules = _OBJ_ENG_MB_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * Throw Exception
         */
		else
		{
			throw new Exception("Exception [Helper: object_helper][Method: _OBJ_validation_rules()]: No validation method defined for supplied portfolio.");
		}

		return $v_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_attribute_form'))
{
	/**
	 * Get Policy Object  - Attribute Form
	 *
	 * Row Partial Form View for An Object
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _OBJ_attribute_form( $portfolio_id )
	{
		$attribute_form = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same object form
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$attribute_form = 'objects/forms/_form_object_motor';
		}

		/**
		 * FIRE
		 * -----
		 * For all type of fire portfolios, we have same object form
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$attribute_form = 'objects/forms/_form_object_fire';
		}

		/**
		 * MARINE
		 * -----
		 * For all type of marine portfolios, we have same object form
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$attribute_form = 'objects/forms/_form_object_marine';
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_bl';
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_car';
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_cpm';
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_eei';
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_ear';
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $attribute_form = 'objects/forms/_form_object_eng_mb';
        }

        /**
         * Throw Exception
         */
		else
		{
			throw new Exception("Exception [Helper: object_helper][Method: _OBJ_attribute_form()]: No attribute form defined for supplied portfolio.");
		}

		return $attribute_form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_policy_package_dropdown'))
{
	/**
	 * Get Policy Packages for Portfolio
	 *
	 * Dropdown list of packages of specified portfolio
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @return	string
	 */
	function _OBJ_policy_package_dropdown( $portfolio_id, $flag_blank_select = true )
	{
		$portfolio_id 	= (int)$portfolio_id;
		$dropdown 		= [];

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$dropdown = _OBJ_MOTOR_policy_package_dropdown($flag_blank_select);
		}

		/**
         * Not applicable policy package dropdown
         *
         * Portfolio That Do not  apply policy packages:
         *
         * 	1. FIRE (all)
         * 	2. MARINE (all)
         * 	3. ENGINEERING (all)
         *
         */
		else
		{
			$dropdown = _OBJ_NA_policy_package_dropdown($flag_blank_select);
		}

		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_NA_policy_package_dropdown'))
{
	/**
	 * Get Not-Applicable Policy Packages for Portfolios
	 * which do not require any packages
	 *
	 *
	 * @param bool $flag_blank_select 	Whether to append blank select
	 * @return	bool
	 */
	function _OBJ_NA_policy_package_dropdown( $flag_blank_select = true)
	{
		$dropdown = [
			IQB_POLICY_PACKAGE_NOT_APPLICABLE  	=> 'Not Applicable'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_pre_save_tasks'))
{
	/**
	 * Object Pre Save Tasks
	 *
	 * Perform tasks that are required before saving a policy objects.
	 * Return the processed data for further computation or saving in DB
	 *
	 * @param int $portfolio_id
	 * @param array $data 		Post Data
	 * @param object $record 	Object Record (for edit mode)
	 * @return array
	 */
	function _OBJ_pre_save_tasks( int $portfolio_id, array $data, $record = NULL )
	{
		$portfolio_id 		= (int)$portfolio_id;
		$method 	= '';

		/**
		 * Find Portfolio Specific Method
		 */
		switch ($portfolio_id)
		{
			/**
	         * ENGINEERING - BOILER EXPLOSION
	         * ------------------------------
	         */
			case IQB_SUB_PORTFOLIO_ENG_BL_ID:
				$method = '_OBJ_ENG_BL_pre_save_tasks';
				break;

			/**
	         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
	         * ---------------------------------------------
	         */
			case IQB_SUB_PORTFOLIO_ENG_EEI_ID:
				$method = '_OBJ_ENG_EEI_pre_save_tasks';
				break;

			/**
	         * ENGINEERING - MACHINE BREAKDOWN
	         * ---------------------------------------------
	         */
			case IQB_SUB_PORTFOLIO_ENG_MB_ID:
				$method = '_OBJ_ENG_MB_pre_save_tasks';
				break;

			default:
				# code...
				break;
		}

		/**
		 * Call portfolio specific pre save tasks method (if any)
		 */
		if( $method )
		{
			// Load Portfolio Helper
			load_portfolio_helper($portfolio_id);

			$data = call_user_func_array( $method, array($data, $record) );
		}

		/**
		 * ELSE Simply return the original data "AS IT IS"
		 */
		return $data;

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_compute_sum_insured_amount'))
{
	/**
	 * Get Sum Insured Amount of Policy Object
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_compute_sum_insured_amount( $portfolio_id, $data )
	{
		$amt_sum_insured =  0.00;

		// Load Portfolio Helper
		load_portfolio_helper($portfolio_id);

		/**
		 * MOTOR
		 * -----
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$amt_sum_insured = _OBJ_MOTOR_compute_sum_insured_amount($portfolio_id, $data);
		}

		/**
		 * FIRE
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$amt_sum_insured = _OBJ_FIRE_compute_sum_insured_amount($portfolio_id, $data);
		}

		/**
		 * MARINE
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$amt_sum_insured = _OBJ_MARINE_compute_sum_insured_amount($portfolio_id, $data);
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $amt_sum_insured = _OBJ_ENG_BL_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $amt_sum_insured = _OBJ_ENG_CAR_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $amt_sum_insured = _OBJ_ENG_CPM_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $amt_sum_insured = _OBJ_ENG_EEI_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $amt_sum_insured = _OBJ_ENG_EAR_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $amt_sum_insured = _OBJ_ENG_MB_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * Throw Exception
         */
		else
		{
			throw new Exception("Exception [Helper: object_helper][Method: _OBJ_compute_sum_insured_amount()]: No sum insured amount computing method defined for supplied portfolio.");
		}

		return $amt_sum_insured;
	}
}

// ------------------------------------------------------------------------


