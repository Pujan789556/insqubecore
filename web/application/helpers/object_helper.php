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
// POLICY RELATED OBJECT HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('get_object_from_policy_record'))
{
    /**
     * Get Policy Object
     *
     * @param object $policy_record Policy Record
     * @return object   Policy Object
     */
    function get_object_from_policy_record( $policy_record )
    {
        // Policy Record contains the following columns by prefixing "object_"
        $object_columns = ['id', 'portfolio_id', 'customer_id', 'attributes', 'amt_sum_insured', 'flag_locked'];
        $object = new StdClass();
        foreach($object_columns as $column )
        {
            $object->{$column} = $policy_record->{'object_' . $column};
        }
        return $object;
    }
}



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
		$snippet      = '';
        $portfolio_id = (int)$record->portfolio_id;

		// Load Portfolio Helper
		load_portfolio_helper($portfolio_id);

		/**
         * AGRICULTURE - CROP SUB-PORTFOLIO
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $snippet = _OBJ_AGR_CROP_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
         * ---------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $snippet = _OBJ_AGR_CATTLE_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $snippet = _OBJ_AGR_POULTRY_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $snippet = _OBJ_AGR_FISH_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $snippet = _OBJ_AGR_BEE_row_snippet($record, $_flag__show_widget_row);
        }

		/**
		 * MOTOR
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$snippet = _OBJ_MOTOR_row_snippet($record, $_flag__show_widget_row);
		}

		/**
		 * FIRE
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$snippet = _OBJ_FIRE_row_snippet($record, $_flag__show_widget_row);
		}

        /**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $snippet = _OBJ_MISC_BRG_row_snippet($record, $_flag__show_widget_row);
        }

		/**
		 * MARINE
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$snippet = _OBJ_MARINE_row_snippet($record, $_flag__show_widget_row);
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $snippet = _OBJ_ENG_BL_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $snippet = _OBJ_ENG_CAR_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $snippet = _OBJ_ENG_CPM_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $snippet = _OBJ_ENG_EEI_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $snippet = _OBJ_ENG_EAR_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $snippet = _OBJ_ENG_MB_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $snippet = _OBJ_MISC_BB_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $snippet = _OBJ_MISC_GPA_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $snippet = _OBJ_MISC_PA_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $snippet = _OBJ_MISC_PL_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $snippet = _OBJ_MISC_CT_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $snippet = _OBJ_MISC_CS_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $snippet = _OBJ_MISC_CC_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $snippet = _OBJ_MISC_EPA_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $snippet = _OBJ_MISC_TMI_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $snippet = _OBJ_MISC_FG_row_snippet($record, $_flag__show_widget_row);
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $snippet = _OBJ_MISC_HI_row_snippet($record, $_flag__show_widget_row);
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
		$snippet      = '';
        $portfolio_id = (int)$record->portfolio_id;

		// Load Portfolio Helper
		load_portfolio_helper($portfolio_id);

		/**
         * AGRICULTURE - CROP SUB-PORTFOLIOS
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $snippet = _OBJ_AGR_CROP_select_text($record);
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
         * ---------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $snippet = _OBJ_AGR_CATTLE_select_text($record);
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $snippet = _OBJ_AGR_POULTRY_select_text($record);
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $snippet = _OBJ_AGR_FISH_select_text($record);
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $snippet = _OBJ_AGR_BEE_select_text($record);
        }

		/**
		 * MOTOR
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$snippet = _OBJ_MOTOR_select_text($record);
		}

		/**
		 * FIRE
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
		{
			$snippet = _OBJ_FIRE_select_text($record);
		}

        /**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $snippet = _OBJ_MISC_BRG_select_text($record);
        }

		/**
		 * MARINE
		 * -------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$snippet = _OBJ_MARINE_select_text($record);
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $snippet = _OBJ_ENG_BL_select_text($record);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $snippet = _OBJ_ENG_CAR_select_text($record);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $snippet = _OBJ_ENG_CPM_select_text($record);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $snippet = _OBJ_ENG_EEI_select_text($record);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $snippet = _OBJ_ENG_EAR_select_text($record);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $snippet = _OBJ_ENG_MB_select_text($record);
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $snippet = _OBJ_MISC_BB_select_text($record);
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $snippet = _OBJ_MISC_GPA_select_text($record);
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $snippet = _OBJ_MISC_PA_select_text($record);
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $snippet = _OBJ_MISC_PL_select_text($record);
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $snippet = _OBJ_MISC_CT_select_text($record);
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $snippet = _OBJ_MISC_CS_select_text($record);
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $snippet = _OBJ_MISC_CC_select_text($record);
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $snippet = _OBJ_MISC_EPA_select_text($record);
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $snippet = _OBJ_MISC_TMI_select_text($record);
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $snippet = _OBJ_MISC_FG_select_text($record);
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $snippet = _OBJ_MISC_HI_select_text($record);
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
         * AGRICULTURE - CROP SUB-PORTFOLIOS
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $v_rules = _OBJ_AGR_CROP_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
         * ---------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $v_rules = _OBJ_AGR_CATTLE_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $v_rules = _OBJ_AGR_POULTRY_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $v_rules = _OBJ_AGR_FISH_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $v_rules = _OBJ_AGR_BEE_validation_rules( $portfolio_id, $formatted );
        }

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same validation rules
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
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
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $v_rules = _OBJ_MISC_BRG_validation_rules( $portfolio_id, $formatted );
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
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $v_rules = _OBJ_MISC_BB_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $v_rules = _OBJ_MISC_GPA_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $v_rules = _OBJ_MISC_PA_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $v_rules = _OBJ_MISC_PL_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $v_rules = _OBJ_MISC_CT_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $v_rules = _OBJ_MISC_CS_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $v_rules = _OBJ_MISC_CC_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $v_rules = _OBJ_MISC_EPA_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $v_rules = _OBJ_MISC_TMI_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $v_rules = _OBJ_MISC_FG_validation_rules( $portfolio_id, $formatted );
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $v_rules = _OBJ_MISC_HI_validation_rules( $portfolio_id, $formatted );
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
         * AGRICULTURE - CROP SUB-PORTFOLIO
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $attribute_form = 'objects/forms/_form_object_agr_crop';
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $attribute_form = 'objects/forms/_form_object_agr_cattle';
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $attribute_form = 'objects/forms/_form_object_agr_poultry';
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $attribute_form = 'objects/forms/_form_object_agr_fish';
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $attribute_form = 'objects/forms/_form_object_agr_bee';
        }

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same object form
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
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
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $attribute_form = 'objects/forms/_form_object_misc_brg';
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
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_bb';
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_gpa';
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_pa';
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_pl';
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_ct';
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_cs';
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_cc';
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_epa';
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_tmi';
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_fg';
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $attribute_form = 'objects/forms/_form_object_misc_hi';
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

            /**
             * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
             * ---------------------------------------------
             */
            case IQB_SUB_PORTFOLIO_MISC_GPA_ID:
                $method = '_OBJ_MISC_GPA_pre_save_tasks';
                break;

            /**
             * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
             * ---------------------------------------------
             */
            case IQB_SUB_PORTFOLIO_MISC_EPA_ID:
                $method = '_OBJ_MISC_EPA_pre_save_tasks';
                break;

            /**
             * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
             * ----------------------------------------
             */
            case IQB_SUB_PORTFOLIO_MISC_FG_ID:
                $method = '_OBJ_MISC_FG_pre_save_tasks';
                break;


            /**
             * MISCELLANEOUS - HEALTH INSURANCE (HI)
             * ----------------------------------------
             */
            case IQB_SUB_PORTFOLIO_MISC_HI_ID:
                $method = '_OBJ_MISC_HI_pre_save_tasks';
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
         * AGRICULTURE - CROP SUB-PORTFOLIOS
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $amt_sum_insured = _OBJ_AGR_CROP_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
         * ---------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $amt_sum_insured = _OBJ_AGR_CATTLE_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $amt_sum_insured = _OBJ_AGR_POULTRY_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $amt_sum_insured = _OBJ_AGR_FISH_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $amt_sum_insured = _OBJ_AGR_BEE_compute_sum_insured_amount($portfolio_id, $data);
        }


		/**
		 * MOTOR
		 * -----
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
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
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $amt_sum_insured = _OBJ_MISC_BRG_compute_sum_insured_amount($portfolio_id, $data);
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
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $amt_sum_insured = _OBJ_MISC_BB_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $amt_sum_insured = _OBJ_MISC_GPA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $amt_sum_insured = _OBJ_MISC_PA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $amt_sum_insured = _OBJ_MISC_PL_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $amt_sum_insured = _OBJ_MISC_CT_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $amt_sum_insured = _OBJ_MISC_CS_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $amt_sum_insured = _OBJ_MISC_CC_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $amt_sum_insured = _OBJ_MISC_EPA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $amt_sum_insured = _OBJ_MISC_TMI_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $amt_sum_insured = _OBJ_MISC_FG_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $amt_sum_insured = _OBJ_MISC_HI_compute_sum_insured_amount($portfolio_id, $data);
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


