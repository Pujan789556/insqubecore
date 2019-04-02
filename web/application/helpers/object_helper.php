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
        $object_columns = ['id', 'portfolio_id', 'attributes', 'amt_sum_insured', 'si_breakdown', 'flag_locked'];
        $object = new StdClass();
        foreach($object_columns as $column )
        {
            $object->{$column} = $policy_record->{'object_' . $column};
        }
        // Customer ID
        $object->customer_id = $policy_record->customer_id;
        return $object;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_object_from_object_audit'))
{
    /**
     * Get Policy Object from Object Audit
     *
     * This function is required to get the object info for premium computation on
     * endorsements.
     *
     * @param object $policy_record Policy Record
     * @param json|NULL $audit_object Object Audit
     * @return object   Policy Object
     */
    function get_object_from_object_audit( $policy_record, $audit_object )
    {
        $object         = NULL;
        $audit_record   = $audit_object ? json_decode($audit_object) : NULL;
        if($audit_record)
        {
            // Get the New data as  Policy Object
            $object = $audit_record->new;

            // Add other meta columns
            // Policy Record contains the following columns by prefixing "object_"
            $object_columns = ['id', 'portfolio_id', 'flag_locked'];
            foreach($object_columns as $column )
            {
                $object->{$column} = $policy_record->{'object_' . $column};
            }
            // Customer ID
            $object->customer_id = $policy_record->customer_id;
        }
        // else
        // {
        //     throw new Exception("Exception [Helper: object_helper][Method: get_object_from_object_audit()]: No modified policy object found!");
        // }

        return $object;
    }
}


// ------------------------------------------------------------------------
// GENERAL OBJECT HELPERS
// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_si_net'))
{
    /**
     * Get NET SI of an object
     *
     * Compute the NET Sum Insured in reference to its previous object information.
     * If, there is no prevous audit object, the gross is net.
     *
     * @param object $old_object Policy Object - OLD Version (Current Version for Fresh/Renewal)
     * @param object $new_object Policy Object - NEW Version (Audit Object for Endorsements)
     * @return  html
     */
    function _OBJ_si_net( $old_object, $new_object = NULL )
    {
        if($new_object)
        {
            $net_si = (float)$new_object->amt_sum_insured - (float)$old_object->amt_sum_insured;
        }
        else
        {
            $net_si = (float)$old_object->amt_sum_insured;
        }

        return $net_si;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_si_breakdown_net'))
{
    /**
     * Get NET SI breakdown of an object
     *
     * Compute the NET Sum Insured of each breakdown title in reference to its previous object information.
     * If, there is no prevous audit object, the gross is net.
     *
     * @param object $old_object Policy Object - OLD Version (Current Version for Fresh/Renewal)
     * @param object $new_object Policy Object - NEW Version (Audit Object for Endorsements)
     * @return  html
     */
    function _OBJ_si_breakdown_net( $old_object, $new_object = NULL )
    {
        $new_si_breakdown = json_decode($new_object->si_breakdown ?? NULL, TRUE); // Array
        $old_si_breakdown = json_decode($old_object->si_breakdown ?? NULL, TRUE); // Array

        $net_si_breakdown = [];

        /**
         * Let's Compute the Net SI on each title
         */
        if( $new_si_breakdown )
        {
            foreach($new_si_breakdown as $key=>$si )
            {
                $net_si_breakdown[$key] = floatval($si) - floatval($old_si_breakdown[$key]);
            }
        }
        else
        {
            $net_si_breakdown = $old_si_breakdown;
        }
        return $net_si_breakdown;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ__has_si_changed'))
{
    /**
     * Check if Object's SI changed during Endorsement
     *
     * @param object $old_object Policy Object - OLD Version (Current Version for Fresh/Renewal)
     * @param object $new_object Policy Object - NEW Version (Audit Object for Endorsements)
     * @return  html
     */
    function _OBJ__has_si_changed( $old_object, $new_object = NULL )
    {
        $flag = FALSE;

        if($new_object)
        {
            $changed_si = abs( (float)$new_object->amt_sum_insured - (float)$old_object->amt_sum_insured );

            $flag = $changed_si > 0 ? TRUE : FALSE;
        }

        return $flag;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ__get_latest'))
{
    /**
     * Policy Object Record - Latest for Premium Computation
     *
     * Case 1: Fresh/Renewal Endorsement
     *      Current Policy Object is used
     *
     * Case 2: Other Endorsement
     *      Use Audit Object information if Current Object is Edited
     *      Else Current Policy Object is used
     *
     * In case of endorsements, we will be needing both current policy object and edited object information
     * to compute premium.
     *
     * @param int   $object_id
     * @param int   $txn_type  Endorsement Type
     * @param JSON  $json_audit_object 'audit_object' column value from 'audit_endorsements' table
     * @return object
     */
    function _OBJ__get_latest( $object_id, $txn_type, $json_audit_object = NULL )
    {
        $CI =& get_instance();
        $CI->load->model('object_model');

        // Get the Object Record
        $object_record = $CI->object_model->get($object_id);

        if( _ENDORSEMENT_is_first( $txn_type) )
        {
            return $object_record;
        }

        /**
         * If we have object audit, get the object information from there
         */
        if($json_audit_object)
        {
            $audit_object = _OBJ__get_from_audit($json_audit_object, 'new');
            $audit_fields = Object_model::$endorsement_fields;

            // Update the current object record to have these audited data
            foreach(Object_model::$endorsement_fields as $col)
            {
                $object_record->{$col} = $audit_object->{$col};
            }
        }

        return $object_record;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ__get_from_audit'))
{
    /**
     * Get object from audit data
     *
     * Based on which, return old, new or both objects from audit data
     *
     * @param JSON $json_audit_object 'audit_object' column value from 'audit_endorsements' table
     * @param type|string $which
     * @return mixed
     */
    function _OBJ__get_from_audit( $json_audit_object, $which = 'new' )
    {
        $object         = NULL;
        $audit_record   = $json_audit_object ? json_decode($json_audit_object) : NULL;
        if($audit_record)
        {
            if($which == 'new')
            {
                $object = $audit_record->new;
            }
            elseif($which == 'old')
            {
                $object = $audit_record->old;
            }
            else
            {
                $object = $audit_record;
            }
        }
        return $object;
    }
}

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
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $snippet = _OBJ_PROPERTY_row_snippet($record, $_flag__show_widget_row);
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

if ( ! function_exists('OBJECT__popup_view'))
{
    /**
     * Get the Object Popup View
     *
     * @param integer $portfolio_id  Portfolio ID
     * @return  string
     */
    function OBJECT__popup_view( $portfolio_id )
    {
        $attribute_form = '';

        /**
         * AGRICULTURE - CROP SUB-PORTFOLIO
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $attribute_form = 'objects/snippets/_popup_agr_crop';
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $attribute_form = 'objects/snippets/_popup_agr_cattle';
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $attribute_form = 'objects/snippets/_popup_agr_poultry';
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $attribute_form = 'objects/snippets/_popup_agr_fish';
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $attribute_form = 'objects/snippets/_popup_agr_bee';
        }

        /**
         * MOTOR
         * -----
         * For all type of motor portfolios, we have same object form
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
        {
            $attribute_form = 'objects/snippets/_popup_motor';
        }

        /**
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $attribute_form = 'objects/snippets/_popup_property';
        }

        /**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $attribute_form = 'objects/snippets/_popup_misc_brg';
        }

        /**
         * MARINE
         * -----
         * For all type of marine portfolios, we have same object form
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
        {
            $attribute_form = 'objects/snippets/_popup_marine';
        }

        /**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_bl';
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_car';
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_cpm';
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise object form
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_eei';
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_ear';
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $attribute_form = 'objects/snippets/_popup_eng_mb';
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_bb';
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_gpa';
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_pa';
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_pl';
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_ct';
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_cs';
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_cc';
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_epa';
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_tmi';
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_fg';
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $attribute_form = 'objects/snippets/_popup_misc_hi';
        }

        /**
         * Throw Exception
         */
        else
        {
            throw new Exception("Exception [Helper: object_helper][Method: OBJECT__popup_view()]: No popup view defined for supplied portfolio.");
        }

        return $attribute_form;
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
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $snippet = _OBJ_PROPERTY_select_text($record);
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
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $v_rules = _OBJ_PROPERTY_validation_rules( $portfolio_id, $formatted );
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
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $attribute_form = 'objects/forms/_form_object_property';
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
             * AGRO - CROP
             * -------------
             */
            case IQB_SUB_PORTFOLIO_AGR_CROP_ID:
                $method = '_OBJ_AGR_CROP_pre_save_tasks';
                break;

            /**
             * AGRO - CATTLE
             * -------------
             */
            case IQB_SUB_PORTFOLIO_AGR_CATTLE_ID:
                $method = '_OBJ_AGR_CATTLE_pre_save_tasks';
                break;

            /**
             * AGRO - POULTRY
             * -------------
             */
            case IQB_SUB_PORTFOLIO_AGR_POULTRY_ID:
                $method = '_OBJ_AGR_POULTRY_pre_save_tasks';
                break;

            /**
             * AGRO - FISH
             * -------------
             */
            case IQB_SUB_PORTFOLIO_AGR_FISH_ID:
                $method = '_OBJ_AGR_FISH_pre_save_tasks';
                break;

            /**
             * AGRO - BEE
             * -------------
             */
            case IQB_SUB_PORTFOLIO_AGR_BEE_ID:
                $method = '_OBJ_AGR_BEE_pre_save_tasks';
                break;

            /**
             * PROPERTY - ALL PORTFOLIOS
             * -------------------------
             */
            case IQB_SUB_PORTFOLIO_PROPERTY_HOUSE_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_GENERAL_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_SHORT_TERM_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_AGREED_VALUED_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_FLOATING_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_DECLARATION_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_FLOATING_DECLARATION_ID:
            case IQB_SUB_PORTFOLIO_PROPERTY_REINSTATE_ID:
                $method = '_OBJ_PROPERTY_pre_save_tasks';
                break;


			/**
	         * ENGINEERING - BOILER EXPLOSION
	         * ------------------------------
	         */
			case IQB_SUB_PORTFOLIO_ENG_BL_ID:
				$method = '_OBJ_ENG_BL_pre_save_tasks';
				break;

            /**
             * ENGINEERING - CONTRACTOR ALL RISK (CAR)
             * ---------------------------------------
             */
            case IQB_SUB_PORTFOLIO_ENG_CAR_ID:
                $method = '_OBJ_ENG_CAR_pre_save_tasks';
                break;

            /**
             * ENGINEERING - ERECTION ALL RISK (EAR)
             * ---------------------------------------
             */
            case IQB_SUB_PORTFOLIO_ENG_EAR_ID:
                $method = '_OBJ_ENG_EAR_pre_save_tasks';
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

            /**
             * MISCELLANEOUS - BRG (Jewelry, Housebreaking, Cash in Safe)
             * ----------------------------------------------------------
             */
            case IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID:
            case IQB_SUB_PORTFOLIO_MISC_BRGHB_ID:
            case IQB_SUB_PORTFOLIO_MISC_BRGCS_ID:
                $method = '_OBJ_MISC_BRG_pre_save_tasks';
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
	 * Get Sum Insured Amount & SI Breakdown of Policy Object
	 *
	 * Compute sum insured amount based on object's portfolio and return.
	 *
	 * @param integer $portfolio_id  Portfolio ID
	 * @param array $data 	Object Data
	 * @return float
	 */
	function _OBJ_compute_sum_insured_amount( $portfolio_id, $data )
    {
        $si_data =  ['amt_sum_insured' => 0.00];

        // Load Portfolio Helper
        load_portfolio_helper($portfolio_id);

        /**
         * AGRICULTURE - CROP SUB-PORTFOLIOS
         * ---------------------------------
         */
        if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
        {
            $si_data = _OBJ_AGR_CROP_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - CATTLE SUB-PORTFOLIOS
         * ---------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
        {
            $si_data = _OBJ_AGR_CATTLE_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - POULTRY SUB-PORTFOLIO
         * -----------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
        {
            $si_data = _OBJ_AGR_POULTRY_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
         * ----------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
        {
            $si_data = _OBJ_AGR_FISH_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
         * -------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
        {
            $si_data = _OBJ_AGR_BEE_compute_sum_insured_amount($portfolio_id, $data);
        }


        /**
         * MOTOR
         * -----
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
        {
            $si_data = _OBJ_MOTOR_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * PROPERTY - ALL PORTFOLIOS
         * -------------------------
         */
        else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
        {
            $si_data = _OBJ_PROPERTY_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $si_data = _OBJ_MISC_BRG_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MARINE
         * -----
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
        {
            $si_data = _OBJ_MARINE_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $si_data = _OBJ_ENG_BL_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $si_data = _OBJ_ENG_CAR_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $si_data = _OBJ_ENG_CPM_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise computation
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $si_data = _OBJ_ENG_EEI_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
            $si_data = _OBJ_ENG_EAR_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $si_data = _OBJ_ENG_MB_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $si_data = _OBJ_MISC_BB_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $si_data = _OBJ_MISC_GPA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $si_data = _OBJ_MISC_PA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $si_data = _OBJ_MISC_PL_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $si_data = _OBJ_MISC_CT_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $si_data = _OBJ_MISC_CS_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $si_data = _OBJ_MISC_CC_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $si_data = _OBJ_MISC_EPA_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $si_data = _OBJ_MISC_TMI_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $si_data = _OBJ_MISC_FG_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $si_data = _OBJ_MISC_HI_compute_sum_insured_amount($portfolio_id, $data);
        }

        /**
         * Throw Exception
         */
        else
        {
            throw new Exception("Exception [Helper: object_helper][Method: _OBJ_compute_sum_insured_amount()]: No sum insured amount computing method defined for supplied portfolio.");
        }

        return $si_data;
    }
}

// ------------------------------------------------------------------------


