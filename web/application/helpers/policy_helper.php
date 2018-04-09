<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Policy Helper Functions
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
// POLICY HELPER FUNCTIONS
// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_status_dropdown'))
{
	/**
	 * Get Policy Status Dropdown
	 *
	 * @return	bool
	 */
	function _POLICY_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [

			IQB_POLICY_STATUS_DRAFT 		=> 'Draft',
			IQB_POLICY_STATUS_VERIFIED 		=> 'Verified',
			IQB_POLICY_STATUS_ACTIVE 		=> 'Active',
			IQB_POLICY_STATUS_CANCELED 		=> 'Canceled',
			IQB_POLICY_STATUS_EXPIRED 		=> 'Expired'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_status_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function _POLICY_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = _POLICY_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_STATUS_VERIFIED, IQB_POLICY_STATUS_ACTIVE]) )
			{
				// Green
				$css_class = 'text-green';
			}
			else if( $key === IQB_POLICY_STATUS_EXPIRED )
			{
				// Gray
				$css_class = 'text-gray';
			}
			else if( $key === IQB_POLICY_STATUS_CANCELED )
			{
				// Red
				$css_class = 'text-red';
			}
			else
			{
				// Red
				$css_class = 'text-orange';
			}

			$text = '<strong class="'.$css_class.'">'.$text.'</strong>';
		}
		return $text;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_flag_dc_dropdown'))
{
	/**
	 * Get Policy's flag_dc dropdown
	 * The flag values indicate whether the policy has any of the following:
	 * 	- Corporate/direct discount
	 * 	- Agent commission
	 * 	- None
	 *
	 * @return	bool
	 */
	function _POLICY_flag_dc_dropdown( $flag_blank_select = true )
	{
		$dropdown = [

			IQB_POLICY_FLAG_DC_AGENT_COMMISSION => 'Agent Commission',
			IQB_POLICY_FLAG_DC_DIRECT 			=> 'Direct/Corporate Discount',
			IQB_POLICY_FLAG_DC_NONE 			=> 'None'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_is_editable'))
{
	/**
	 * Is Policy Editable?
	 *
	 * Check if the given policy is editable.
	 * We need this helper function as it is used multiple controllers & models
	 *
	 * @param char $status 	Policy Status
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function _POLICY_is_editable( $status, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 		= FALSE;

		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft
		 *
		 * Editable Permissions Are
		 * 		edit.draft.policy
		 */

		// Editable Permissions ?
		if( $status ===  IQB_POLICY_STATUS_DRAFT )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policies', 'edit.draft.policy') )
			)
			{
				$__flag_authorized = TRUE;
			}
		}

		// Terminate on Exit?
		if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
		{
			$CI->dx_auth->deny_access();
			exit(1);
		}

		return $__flag_authorized;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__partial_view__cost_calculation_table'))
{
	/**
	 * Get Cost Calculation Table Parital View
	 *
	 * @param integer $portfolio_id Portfolio ID
	 * @param string $view_for View For [regular|print]
	 * @return	string
	 */
	function _POLICY__partial_view__cost_calculation_table( $portfolio_id, $view_for = 'regular' )
	{
		$partial_view 	= '';
		$view_prefix 	= $view_for === 'print' ? '_print' : '';

		/**
         * AGRICULTURE - ALL SUB-PORTFOLIOs
         * ---------------------------------
         */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR)) )
		{
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_AGR";
		}

		/**
		 * MOTOR PORTFOLIOS- ALL SUB-PORTFOLIOs
         * ------------------------------------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MOTOR";
		}

		/**
         * FIRE - FIRE
         * -------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_FIRE_FIRE";
        }

        /**
         * FIRE - HOUSEHOLDER
         * -------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_FIRE_HHP";
        }

        /**
         * FIRE - LOSS OF PROFIT
         * ----------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_FIRE_LOP";
        }

		/**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_BRG";
        }

		/**
		 * MARINE PORTFOLIOS
		 * -----------------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MARINE";
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_BL";
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_CAR";
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_CPM";
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_EEI";
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_EAR";
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
			$partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_ENG_MB";
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_BB";
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_GPA";
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_PA";
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_PL";
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_CT";
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_CS";
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_CC";
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_EPA";
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_TMI";
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_FG";
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $partial_view = "endorsements/snippets/{$view_prefix}_cost_calculation_table_MISC_HI";
        }

        /**
         * Throw Exception
         */
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__partial_view__cost_calculation_table()]: No Cost Calculation Table View defined for supplied portfolio.");
		}

		return $partial_view;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__partial_view__premium_form'))
{
	/**
	 * Get Endorsement Premium Form View
	 *
	 * @param id $portfolio_id Portfolio ID
	 * @return	string
	 */
	function _POLICY__partial_view__premium_form( $portfolio_id )
	{
		$form_view = '';

		/**
         * AGRICULTURE - ALL SUB-PORTFOLIOs
         * ---------------------------------
         */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR)) )
		{
			$form_view = 'endorsements/forms/_form_premium_AGR';
		}

		/**
		 * MOTOR PORTFOLIOS
		 * ----------------
		 * For all type of motor portfolios, we have same package list
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$form_view = 'endorsements/forms/_form_premium_MOTOR';
		}

		/**
         * FIRE - FIRE
         * -------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_FIRE_FIRE';
        }

        /**
         * FIRE - HOUSEHOLDER
         * -------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_FIRE_HHP';
        }

        /**
         * FIRE - LOSS OF PROFIT
         * ----------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_FIRE_LOP_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_FIRE_LOP';
        }

		/**
         * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
         * --------------------------------------------------
         */
        else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_BRG';
        }

		/**
		 * MARINE
		 * ------
		 */
		else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
		{
			$form_view = 'endorsements/forms/_form_premium_MARINE';
		}

		/**
         * ENGINEERING - BOILER EXPLOSION
         * ------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_ENG_BL';
        }

        /**
         * ENGINEERING - CONTRACTOR ALL RISK
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_ENG_CAR';
        }

        /**
         * ENGINEERING - CONTRACTOR PLANT & MACHINARY
         * ------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_ENG_CPM';
        }

        /**
         * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_ENG_EEI';
        }

        /**
         * ENGINEERING - ERECTION ALL RISKS
         * ---------------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
        {
			$form_view = 'endorsements/forms/_form_premium_ENG_EAR';
        }

        /**
         * ENGINEERING - MACHINE BREAKDOWN
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_ENG_MB';
        }

        /**
         * MISCELLANEOUS - BANKER'S BLANKET(BB)
         * -------------------------------------
         * Sub-portfolio wise view
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_BB';
        }

        /**
         * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_GPA';
        }

        /**
         * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
         * ---------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_PA';
        }

        /**
         * MISCELLANEOUS - PUBLIC LIABILITY(PL)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_PL';
        }

        /**
         * MISCELLANEOUS - CASH IN TRANSIT
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_CT';
        }

        /**
         * MISCELLANEOUS - CASH IN SAFE
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_CS';
        }

        /**
         * MISCELLANEOUS - CASH IN COUNTER
         * -------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_CC';
        }

        /**
         * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_EPA';
        }

        /**
         * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
         * --------------------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_TMI';
        }

        /**
         * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_FG';
        }

        /**
         * MISCELLANEOUS - HEALTH INSURANCE (HI)
         * ----------------------------------------
         */
        else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
        {
            $form_view = 'endorsements/forms/_form_premium_MISC_HI';
        }


		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__partial_view__premium_form()]: No premium form defined for supplied portfolio.");
		}

		return $form_view;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__compute_short_term_premium'))
{
	/**
	 * Compute Short Term Policy Premium
	 *
	 * @param object $policy_record Policy Record
	 * @param object $pfs_record Portfolio Settings Record
	 * @param array $cost_table Cost Table computed by Specific cost table function
	 * @return	array
	 */
	function _POLICY__compute_short_term_premium( $policy_record, $pfs_record, $cost_table )
    {
        /**
         * SHORT TERM POLICY?
         * ---------------------
         *
         * If the policy is short term policy, we have to calculate the short term values
         *
         */
        $CI =& get_instance();
        $fy_record = $CI->fiscal_year_model->get_fiscal_year( $policy_record->issued_date );
        $short_term_info = _POLICY__get_short_term_info( $policy_record->portfolio_id, $fy_record, $policy_record->start_date, $policy_record->end_date );

        if(
            $pfs_record->flag_short_term === IQB_FLAG_YES
            &&
            $short_term_info['flag'] === IQB_FLAG_YES
            &&
            $policy_record->flag_short_term === IQB_FLAG_YES )
        {
            $short_term_record = $short_term_info['record'];

            $short_term_rate = $short_term_record->rate ?? 100.00;
            $short_term_rate = (float)$short_term_rate;

            // Compute Total Amount (Pool do not apply short term rate)
            $amt_basic_premium               = $cost_table['amt_total_premium'] - $cost_table['amt_pool_premium'];
            $amt_pool_premium                = $cost_table['amt_pool_premium'];
            $amt_basic_premium               = ($amt_basic_premium * $short_term_rate)/100.00;
            $cost_table['amt_total_premium'] = $amt_basic_premium + $cost_table['amt_pool_premium'];

            // Update Commissionable Amount and Commission
            $amt_commissionable = $cost_table['amt_commissionable'] ?? NULL;
            if($amt_commissionable)
            {
                $cost_table['amt_commissionable']   = ($cost_table['amt_commissionable'] * $short_term_rate)/100.00;
                $cost_table['amt_agent_commission'] = ($cost_table['amt_commissionable'] * $pfs_record->agent_commission)/100.00;
            }
        }

        return $cost_table;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_short_term_flag'))
{
    /**
     * Get Short Term Policy Flag
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  char
     */
    function _POLICY__get_short_term_flag( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $info = _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date );
        return $info['flag'];
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__is_short_term'))
{
    /**
     * Is Policy Short Term?
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  bool
     */
    function _POLICY__is_short_term( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $info = _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date );
        return $info['flag'] === IQB_FLAG_NO;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_short_term_info'))
{
    /**
     * Get Short Term Policy Info
     *
     * @param integer $portfolio_id Portfolio ID
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  array
     */
    function _POLICY__get_short_term_info( $portfolio_id, $fy_record, $start_date, $end_date )
    {
        $CI =& get_instance();
        $CI->load->model('portfolio_setting_model');

        $false_return = [
            'flag'      => IQB_FLAG_NO,
            'record'    => NULL
        ];

        /**
         * Current Fiscal Year Record & Portfolio Settings for This Fiscal Year
         */
        $pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($fy_record->id, $portfolio_id);
        if(!$pfs_record)
        {
        	throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__get_short_term_info()]: No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en})");
        }

        // update false return with default duration
        $false_return['default_duration'] = (int)$pfs_record->default_duration;

        if($pfs_record->flag_short_term === IQB_FLAG_NO )
        {
            return $false_return;
        }


        /**
         * Let's find if the policy duration falls under Short Term Duration List
         *
         * Calculate the Number of Days between given two dates
         */
        $start_timestamp    = strtotime($start_date);
        $end_timestamp      = strtotime($end_date);
        $difference         = $end_timestamp - $start_timestamp;
        $days               = floor($difference / (60 * 60 * 24));
        $default_duration 	= (int)$pfs_record->default_duration;

        /**
         * Supplied Duration === Default Duration?
         */
        if($days == $default_duration)
        {
        	return $false_return;
        }


        $short_term_policy_rate = $pfs_record->short_term_policy_rate ? json_decode($pfs_record->short_term_policy_rate) : [];
        echo '<pre>'; print_r($short_term_policy_rate);exit;
        // Build The Duration List
        $duration_list = [$default_duration];
        foreach($short_term_policy_rate as $spr)
        {
            $duration_list[] = (int)$spr->duration;
        }
        sort($duration_list);
        $duration_list = array_values(array_unique($duration_list));

        // Index
        $index_days       = array_search($days, $duration_list);
        $index_default    = array_search($default_duration, $duration_list);

        $element_count = count($duration_list);

        // If days is exactly found in duration list, bang...
        $flag_short_term = FALSE;
        $found          = FALSE;
        $found_index    = 0;
        if($index_days !== FALSE)
        {
            // We found the key
            // Last key? then its not short term policy
            $flag_short_term    = $index_days !== $index_default;
            $found_index        = $index_days;
        }
        else
        {
            // Let's loop through to find where it falls
            foreach ($duration_list as $key => $value)
            {
                if( !$found && $days < $value )
                {
                    $found = TRUE;
                    $found_index = $key;
                }
            }
            // Let's check if we have found
            $flag_short_term = $found_index !== $index_default;
        }

        // is Short TERM?
        if( !$flag_short_term )
        {
            return $false_return;
        }

        // Now Let's Get the Short Term Duration Record
        $spr_record = NULL;
        foreach($short_term_policy_rate as $spr)
        {
            $spr_duration = (int)$spr->duration;
            if($spr_duration === $duration_list[$found_index] )
            {
                $spr_record = $spr;
            }
        }
        return [
            'flag'      		=> IQB_FLAG_YES,
            'record'    		=> $spr_record,
            'default_duration' 	=> $default_duration
        ];
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__schedule_pdf'))
{
    /**
     * Save or Print Policy Schedule.
     *
     * 1. Save the Original Policy Schedule (PDF)
     * 		Once the policy number is generated, the Original/First Copy of
     * 		policy is saved as pdf. This is required because the policy contents
     * 		change over the period of time via "Endorsement".
     *
     * 2. Print The Policy Schedule (PDF)
     * 		This action is called to generate current policy schedule's pdf.
     *
     * Filename: <policycode>.pdf
     *
     * @param array $data 		['record' => xxx, 'endorsement_record' => yyy]
     * @param string $action 	[save|print]
     * @return  void
     */
    function _POLICY__schedule_pdf( $data, $action )
    {
    	if( !in_array($action, ['save', 'print', 'download']) )
    	{
    		throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: Invalid Action({$action}).");
    	}

    	$CI =& get_instance();

		/**
		 * Extract Policy Record and Endorsement Record
		 */
		$record 		= $data['record'];
		$endorsement_record 	= $data['endorsement_record'];
		$schedule_view 	= _POLICY__get_schedule_view($record->portfolio_id);
		if( $schedule_view )
		{
			$CI->load->library('pdf');
	        $mpdf = $CI->pdf->load();
	        // $mpdf->SetMargins(10, 10, 5);
	        $mpdf->SetMargins(10, 5, 10, 5);
	        $mpdf->margin_header = 5;
	        $mpdf->margin_footer = 5;
	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Schedule - {$record->code}");
	        $mpdf->SetAuthor($CI->settings->orgn_name_en);

	        /**
	         * Only Active Policy Does not have watermark!!!
	         */
	        if( $action === 'print' ||  $action === 'download')
	        {
		        if( !in_array($record->status, [IQB_POLICY_STATUS_ACTIVE, IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED]))
		        {
		        	$mpdf->SetWatermarkText( 'DEBIT NOTE - ' . strtoupper(_POLICY_status_text($record->status)) );
		        }
		    }

	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $CI->load->view( $schedule_view, $data, TRUE);
	        $mpdf->WriteHTML($html);
	        // $filename = $upload_path . "policy-{$record->code}.pdf";
	        $filename = "policy-{$record->code}.pdf";
	        if( $action === 'save' )
	        {
	        	$save_full_path = rtrim(INSQUBE_MEDIA_PATH, '/') . '/policies/' . $filename;
	        	$mpdf->Output($save_full_path,'F');
	        }
	        else if($action === 'download')
	        {
	        	$mpdf->Output($filename,'D');      // make it to DOWNLOAD
	        }
	        else
	        {
	        	$mpdf->Output();
	        }
		}
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: No schedule view exists for given portfolio({$record->portfolio_name}).");
		}
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_schedule_view'))
{
    /**
     * Get the policy schedule view
     *
     * @param integer $portfolio_id Portfolio ID
     * @return  void
     */
    function _POLICY__get_schedule_view( $portfolio_id )
    {
    	$schedule_view = '';
    	$portfolio_id  = (int)$portfolio_id;

		switch ($portfolio_id)
		{
			// AGRICULTURE - CROP SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_CROP_ID:
				$schedule_view = 'policies/print/schedule_AGR_CROP';
				break;

			// AGRICULTURE - CATTLE SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_CATTLE_ID:
				$schedule_view = 'policies/print/schedule_AGR_CATTLE';
				break;

			// AGRICULTURE - POULTRY SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_POULTRY_ID:
				$schedule_view = 'policies/print/schedule_AGR_POULTRY';
				break;

			// AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_FISH_ID:
				$schedule_view = 'policies/print/schedule_AGR_FISH';
				break;

			// AGRICULTURE - BEE SUB-PORTFOLIO
			case IQB_SUB_PORTFOLIO_AGR_BEE_ID:
				$schedule_view = 'policies/print/schedule_AGR_BEE';
				break;

			// Motor
			case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
			case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
			case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
					$schedule_view = 'policies/print/schedule_MOTOR';
				break;

			// FIRE - FIRE
			case IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID:
					$schedule_view = 'policies/print/schedule_FIRE_FIRE';
				break;

			// FIRE - HOUSEHOLDER
			case IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID:
					$schedule_view = 'policies/print/schedule_FIRE_HHP';
				break;

			// FIRE - LOSS OF PROFIT
			case IQB_SUB_PORTFOLIO_FIRE_LOP_ID:
					$schedule_view = 'policies/print/schedule_FIRE_LOP';
				break;


			// Burglary
			case IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID:
			case IQB_SUB_PORTFOLIO_MISC_BRGHB_ID:
			case IQB_SUB_PORTFOLIO_MISC_BRGCS_ID:
					$schedule_view = 'policies/print/schedule_MISC_BRG';
				break;

			// Marine
			case IQB_SUB_PORTFOLIO_MARINE_AIR_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_MARINE_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_OPEN_MARINE_ID:
			case IQB_SUB_PORTFOLIO_MARINE_ROAD_AIR_TRANSIT_ID:
			case IQB_SUB_PORTFOLIO_MARINE_ROAD_TANSIT_ID:
				$schedule_view = 'policies/print/schedule_MARINE';
				break;

			// ENGINEERING - BOILER EXPLOSION
	        case IQB_SUB_PORTFOLIO_ENG_BL_ID:
	        	$schedule_view = 'policies/print/schedule_ENG_BL';
				break;

			// ENGINEERING - CONTRACTOR ALL RISK
			case IQB_SUB_PORTFOLIO_ENG_CAR_ID:
				$schedule_view = 'policies/print/schedule_ENG_CAR';
				break;

			// ENGINEERING - CONTRACTOR PLANT & MACHINARY
	        case IQB_SUB_PORTFOLIO_ENG_CPM_ID:
	        	$schedule_view = 'policies/print/schedule_ENG_CPM';
				break;

			// ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
			case IQB_SUB_PORTFOLIO_ENG_EEI_ID:
				$schedule_view = 'policies/print/schedule_ENG_EEI';
				break;

			// ENGINEERING - ERECTION ALL RISKS
			case IQB_SUB_PORTFOLIO_ENG_EAR_ID:
				$schedule_view = 'policies/print/schedule_ENG_EAR';
				break;

			// ENGINEERING - MACHINE BREAKDOWN
			case IQB_SUB_PORTFOLIO_ENG_MB_ID:
				$schedule_view = 'policies/print/schedule_ENG_MB';
				break;

			// MISCELLANEOUS - BANKER'S BLANKET(BB)
			case IQB_SUB_PORTFOLIO_MISC_BB_ID:
				$schedule_view = 'policies/print/schedule_MISC_BB';
				break;

			// MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
			case IQB_SUB_PORTFOLIO_MISC_GPA_ID:
				$schedule_view = 'policies/print/schedule_MISC_GPA';
				break;

			// MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
			case IQB_SUB_PORTFOLIO_MISC_PA_ID:
				$schedule_view = 'policies/print/schedule_MISC_PA';
				break;

			// MISCELLANEOUS - PUBLIC LIABILITY(PL)
			case IQB_SUB_PORTFOLIO_MISC_PL_ID:
				$schedule_view = 'policies/print/schedule_MISC_PL';
				break;

			// MISCELLANEOUS - CASH IN TRANSIT
			case IQB_SUB_PORTFOLIO_MISC_CT_ID:
				$schedule_view = 'policies/print/schedule_MISC_CT';
				break;

			// MISCELLANEOUS - CASH IN SAFE
			case IQB_SUB_PORTFOLIO_MISC_CS_ID:
				$schedule_view = 'policies/print/schedule_MISC_CS';
				break;

			// MISCELLANEOUS - CASH IN COUNTER
			case IQB_SUB_PORTFOLIO_MISC_CC_ID:
				$schedule_view = 'policies/print/schedule_MISC_CC';
				break;

			// MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
			case IQB_SUB_PORTFOLIO_MISC_EPA_ID:
				$schedule_view = 'policies/print/schedule_MISC_EPA';
				break;

			// MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
			case IQB_SUB_PORTFOLIO_MISC_TMI_ID:
				$schedule_view = 'policies/print/schedule_MISC_TMI';
				break;

			// MISCELLANEOUS - FIDELITY GUARANTEE (FG)
			case IQB_SUB_PORTFOLIO_MISC_FG_ID:
				$schedule_view = 'policies/print/schedule_MISC_FG';
				break;

			// MISCELLANEOUS - HEALTH INSURANCE (HI)
			case IQB_SUB_PORTFOLIO_MISC_HI_ID:
				$schedule_view = 'policies/print/schedule_MISC_HI';
				break;


			default:
				# code...
				break;
		}

		return $schedule_view;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_schedule_title_prefix'))
{
	/**
	 * Get policy number title based on policy status
	 *
	 * If status is draft/verify - it will show label as "Debit No" else "Policy No"
	 *
	 * @param char $status 	Policy Status
	 * @param string $lang 	Language
	 * @return	bool
	 */
	function _POLICY_schedule_title_prefix( $status, $lang = 'np' )
	{
		if( in_array($status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_VERIFIED]) )
		{
			$label = [
				'np' => 'डेबिट नोट नं.',
				'en' => 'Debit Note No.'
			];
		}
		else
		{
			$label = [
				'np' => 'बीमालेख नं.',
				'en' => 'Policy No.'
			];
		}

		return $label[$lang];
	}
}


// ------------------------------------------------------------------------
// POLICY TRANSACTION HELPER FUNCTIONS
// ------------------------------------------------------------------------


// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_status_dropdown'))
{
	/**
	 * Get Endorsement Status Dropdown
	 *
	 * @return	bool
	 */
	function _ENDORSEMENT_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_ENDORSEMENT_STATUS_DRAFT			=> 'Draft',
			IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED		=> 'Verified',
			IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED	=> 'RI Approved',
			IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED		=> 'Vouchered',
			IQB_POLICY_ENDORSEMENT_STATUS_INVOICED		=> 'Invoiced',
			IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE		=> 'Active'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_status_text'))
{
	/**
	 * Get Endorsement Status Text
	 *
	 * @return	string
	 */
	function _ENDORSEMENT_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = _ENDORSEMENT_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED, IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED, IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE]) )
			{
				// Green
				$css_class = 'text-green';
			}
			else
			{
				// Orange
				$css_class = 'text-orange';
			}

			$text = '<strong class="'.$css_class.'">'.$text.'</strong>';
		}
		return $text;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT_is_editable'))
{
	/**
	 * Is Endorsement Editable?
	 *
	 * Check if the given Endorsement is editable.
	 *
	 * @param char $status 	Endorsement Status
	 * @param char $flag_current 	Is this Current Endorsement
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function _ENDORSEMENT_is_editable($status, $flag_current, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 	= FALSE;

		/**
		 * Is it current Transaction?
		 */
		if( $flag_current == IQB_FLAG_ON )
		{
			$__flag_authorized 	= TRUE;
		}

		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft
		 *
		 * Editable Permissions Are
		 * 		edit.draft.endorsement
		 */

		// Editable Permissions ?
		if( $__flag_authorized && $status === IQB_POLICY_ENDORSEMENT_STATUS_DRAFT )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_ENDORSEMENT_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('endorsements', 'edit.draft.endorsement') )

			)
			{
				$__flag_authorized = TRUE;
			}

		}
        else
        {
            $__flag_authorized  = FALSE;
        }

		// Terminate on Exit?
		if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
		{
			$CI->dx_auth->deny_access();
			exit(1);
		}

		return $__flag_authorized;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_type_dropdown'))
{
	/**
	 * Get Endorsement Type Dropdown
	 *
	 * @return	array
	 */
	function _ENDORSEMENT_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_ENDORSEMENT_TYPE_FRESH 		=> 'Fresh',
			IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL 	=> 'Renewal',

			IQB_POLICY_ENDORSEMENT_TYPE_GENERAL 			=> 'General (Nil)',
			IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER 	=> 'Ownership Transfer',
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE 	=> 'Premium Upgrade',
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND 		=> 'Premium Refund',
			IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE 			=> 'Terminate'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_type_eonly_dropdown'))
{
	/**
	 * Get Endorsement Type (Endorsement Only) Dropdown
	 *
	 * @return	array
	 */
	function _ENDORSEMENT_type_eonly_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_ENDORSEMENT_TYPE_GENERAL 			=> 'General (Nil)',
			IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER 	=> 'Ownership Transfer',
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE 	=> 'Premium Upgrade',
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND 		=> 'Premium Refund',
			IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE 			=> 'Terminate'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_first'))
{
	/**
	 * Check if given Endorsement is first (Fresh/Renewal).
	 *
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_first( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types 	= [
			IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
			IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_deletable_by_type'))
{
	/**
	 * Get Endorsement Type - Deletable only
	 *
	 * Endorsement Only Transaction Types are deletable from transactions tab.
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_deletable_by_type( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types =  array_keys( _ENDORSEMENT_type_eonly_dropdown(FALSE) );

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_premium_computable_by_type'))
{
	/**
	 * Check if given Endorsement type is Premium Computable.
	 *
	 * Allowed Transaction Types
	 * 	- Fresh
	 * 	- Renewal
	 * 	- Premium Upgrade
	 * 	- Premium Refund
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_premium_computable_by_type( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types 	= [
			IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
			IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_policy_editable_by_type'))
{
	/**
	 * Check if given Endorsement type allows policy to edit.
	 *
	 * Allowed Transaction Types
	 * 	- General
	 * 	- Premium Upgrade
	 * 	- Premium Refund
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_policy_editable_by_type( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types 	= [
			IQB_POLICY_ENDORSEMENT_TYPE_GENERAL,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_object_editable_by_type'))
{
	/**
	 * Check if given Endorsement type allows policy object to edit.
	 *
	 * Allowed Transaction Types
	 * 	- General
	 * 	- Premium Upgrade
	 * 	- Premium Refund
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_object_editable_by_type( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types 	= [
			IQB_POLICY_ENDORSEMENT_TYPE_GENERAL,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_customer_editable_by_type'))
{
	/**
	 * Check if given Endorsement type allows policy customer to edit.
	 *
	 * Allowed Transaction Types
	 * 	- General
	 * 	- Premium Upgrade
	 * 	- Premium Refund
	 *
	 * @param 	int 	Transaction Type
	 * @return	array
	 */
	function _ENDORSEMENT_is_customer_editable_by_type( $txn_type )
	{
		$txn_type 		= (int)$txn_type;
		$allowed_types 	= [
			IQB_POLICY_ENDORSEMENT_TYPE_GENERAL,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_computation_basis_dropdown'))
{
	/**
	 * Get Endorsement Computation Basis Dropdown
	 *
	 * @return	array
	 */
	function _ENDORSEMENT_computation_basis_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_ENDORSEMENT_CB_ANNUAL     => 'Annual/Complete',
			IQB_POLICY_ENDORSEMENT_CB_STR        => 'Short Term Rate',
			IQB_POLICY_ENDORSEMENT_CB_PRORATA 	  => 'Prorata',
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_apply_computation_basis'))
{
    /**
     * Apply computation Basis for given endorsement data
     *
     * @param record $policy_record Policy Record
     * @param record $endorsement_record Endorsement Record
     * @param record $pfs_record    Portfoli Setting Record
     * @param array $premium_data       Endorsement Data
     * @return type
     */
    function _ENDORSEMENT_apply_computation_basis( $policy_record, $endorsement_record, $pfs_record, $premium_data )
    {
        $computed_data = [];
        $computation_basis = (int)$endorsement_record->computation_basis;
        switch ($computation_basis)
        {
            /**
             * No computation needed. The whole amount is used.
             */
            case IQB_POLICY_ENDORSEMENT_CB_ANNUAL:
                $computed_data = $premium_data;
                break;

            case IQB_POLICY_ENDORSEMENT_CB_STR:
                $computed_data = _ENDORSEMENT__compute_short_term_premium( $pfs_record, $premium_data, $endorsement_record->txn_date, $policy_record->end_date );
                break;

            case IQB_POLICY_ENDORSEMENT_CB_PRORATA:
                $computed_data = _ENDORSEMENT__compute_prorata_premium( $premium_data, $endorsement_record->txn_date, $policy_record->end_date );
                break;

            default:
                # code...
                break;
        }

        /**
         * Computation Right?
         * i.e.
         *  If endorsement is premium upgrade -> total premium and/or pool premium MUST be positive
         *  If endorsement is premium refund -> total premium and/or pool premium MUST be negative
         */
        $allowed_types  = [
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];
        if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
        {
            $txn_type           = (int)$endorsement_record->txn_type;
            $amt_total_premium  = $computed_data['amt_total_premium'];

            if( $txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE && $amt_total_premium < 0 )
            {
                throw new Exception("Exception [Helper: policy_helper][Method: _ENDORSEMENT_apply_computation_basis()]: Negative Premium. Please change the endorsement type to 'Premium Refund' and update premium again!");
            }
            else if ($txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND && $amt_total_premium > 0 )
            {
                throw new Exception("Exception [Helper: policy_helper][Method: _ENDORSEMENT_apply_computation_basis()]: Positive Premium. Please change the endorsement type to 'Premium Upgrade' and update premium again!");
            }
        }

        return $computed_data;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__compute_prorata_premium'))
{
    /**
     * Compute Prorata Premium of a Policy
     *
     * @param array $premium_data
     * @param date $start_date
     * @param date $end_date
     * @return array
     */
    function _ENDORSEMENT__compute_prorata_premium( $premium_data, $start_date, $end_date )
    {
        $today = date('Y-m-d');
        $policy_duration  = date_difference($start_date, $end_date, 'd');
        $prorata_duration = date_difference($today, $end_date, 'd');


        $rate = $prorata_duration / $policy_duration;

        // Compute Total Amount (Pool do not apply prorata)
        $amt_basic_premium                  = $premium_data['amt_total_premium'] - $premium_data['amt_pool_premium'];
        $amt_pool_premium                   = $premium_data['amt_pool_premium'];
        $amt_basic_premium                  = $amt_basic_premium * $rate;
        $premium_data['amt_total_premium']  = $amt_basic_premium + $amt_pool_premium ;

        // Update Commissionable Amount and Commission
        $amt_commissionable = $cost_table['amt_commissionable'] ?? NULL;
        if($amt_commissionable)
        {
            $premium_data['amt_commissionable']     = (float)$premium_data['amt_total_premium'] * $rate ;
            $premium_data['amt_agent_commission']   = (float)$premium_data['amt_agent_commission'] * $rate ;
        }

        return $premium_data;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__compute_short_term_premium'))
{
    /**
     * Compute Short Term Policy Premium
     *
     * @param object $pfs_record Portfolio Settings Record
     * @param array $premium_data Cost Table computed by Specific cost table function
     * @param date $start_date Endorsement/Policy Start Date
     * @param date $end_date Policy End Date
     * @return  array
     */
    function _ENDORSEMENT__compute_short_term_premium( $pfs_record, $premium_data, $start_date, $end_date )
    {
        $rate = _ENDORSEMENT__get_short_term_rate( $pfs_record, $start_date, $end_date  );

        // Compute Total Amount (Pool do not apply short term rate)
        $amt_basic_premium                  = $premium_data['amt_total_premium'] - $premium_data['amt_pool_premium'];
        $amt_pool_premium                   = $premium_data['amt_pool_premium'];
        $amt_basic_premium                  = ($amt_basic_premium * $rate)/100.00;
        $premium_data['amt_total_premium']  = $amt_basic_premium + $premium_data['amt_pool_premium'];

        // Update Commissionable Amount and Commission
        $amt_commissionable = $premium_data['amt_commissionable'] ?? NULL;
        if($amt_commissionable)
        {
            $premium_data['amt_commissionable']   = ($premium_data['amt_commissionable'] * $rate)/100.00;
            $premium_data['amt_agent_commission'] = ($premium_data['amt_commissionable'] * $pfs_record->agent_commission)/100.00;
        }

        return $premium_data;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__get_short_term_rate'))
{
    /**
     * Get Short Term Policy Rate
     *
     * @param object $pfs_record Portfolio Settings Record
     * @param date  $start_date Endorsement Start Date
     * @param date $end_date    Policy End Date
     * @return  float
     */
    function _ENDORSEMENT__get_short_term_rate( $pfs_record, $start_date, $end_date )
    {
        $default_rate = 100.00;

        /**
         * Let's find if the policy duration falls under Short Term Duration List
         *
         * Calculate the Number of Days between given two dates
         */
        $start_timestamp    = strtotime($start_date);
        $end_timestamp      = strtotime($end_date);
        $difference         = $end_timestamp - $start_timestamp;
        $days               = floor($difference / (60 * 60 * 24));
        $default_duration   = (int)$pfs_record->default_duration;


        /**
         * Supplied Duration === Default Duration? it's 100%
         */
        if($days == $default_duration)
        {
            return $default_rate;
        }

        $short_term_policy_rates = $pfs_record->short_term_policy_rate ? json_decode($pfs_record->short_term_policy_rate) : NULL;

        if( !$short_term_policy_rates )
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _ENDORSEMENT__get_short_term_rate()]: No Short Term Policy Rates found for the supplied portfolio.");

        }

        $rate_list = [];
        foreach($short_term_policy_rates as $r)
        {
            $rate_list[$r->duration] = $r->rate;
        }
        ksort($rate_list);

        foreach($rate_list as $duration=>$rate)
        {
            if( $days <= $duration )
            {
                $default_rate = $rate;
                break;
            }
        }

        return $default_rate;
    }
}


// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_type_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function _ENDORSEMENT_type_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = _ENDORSEMENT_type_dropdown();

		$text = $list[$key] ?? '';

		return $text;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__ri_approval_constraint'))
{
	/**
	 * RI Approval Constraint on Endorsement
	 *
	 * Check if the Endorsement record requires RI Approval and is Approved
	 * i.e.
	 * 		if RI Approval required and not approved yet, it returns TRUE
	 * 		FALSE otherwise.
	 *
	 * @param char 	$status 			Endorsement Status
	 * @param int 	$flag_ri_approval 	Endorsement flag_ri_approval
	 * @return	bool
	 */
	function _ENDORSEMENT__ri_approval_constraint( $status, $flag_ri_approval )
	{
		$constraint = FALSE;

        // First check if it requires RI Approval
        if( (int)$flag_ri_approval === IQB_FLAG_ON )
        {
            // Transaction status must be "RI Approved"
            $constraint = $status !== IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED;
        }

        return $constraint;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT_endorsement_pdf'))
{
    /**
     * Print Policy Endorsement PDF
     *
     * @param array $data
     * @return  void
     */
    function _ENDORSEMENT_endorsement_pdf( $data )
    {
    	$CI =& get_instance();

		/**
		 * Extract Policy Record and Endorsement Record
		 */
		$records 		= $data['records'];
		$type 			= $data['type'];

		if( $type == 'single' )
		{
			// check if this is not fresh/renewal transaction
			$record = $records[0] ?? NULL;

			if($record && in_array($record->txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_FRESH, IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL]) )
			{
				throw new Exception("Exception [Helper: policy_helper][Method: _ENDORSEMENT_endorsement_pdf()]: You can not have endrosement print of FRESH/RENEWAL Transaction/endorsement.");
			}
		}

		$schedule_view 	= 'endorsements/print/endorsement';

		$record = $records[0] ?? NULL;

		if( $record )
		{
			$CI->load->library('pdf');
	        $mpdf = $CI->pdf->load();
	        // $mpdf->SetMargins(10, 10, 5);
	        $mpdf->SetMargins(10, 5, 10, 5);
	        $mpdf->margin_header = 5;
	        $mpdf->margin_footer = 5;
	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Endorsement - {$record->code}");
	        $mpdf->SetAuthor($CI->settings->orgn_name_en);

	        /**
	         * Only Active Endorsement Does not have watermark!!!
	         */
	        if( $record->status !== IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE )
	        {
	        	$mpdf->SetWatermarkText( 'ENDORSEMENT - ' . strtoupper(_ENDORSEMENT_status_text($record->status)) );
	        }

	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $CI->load->view( $schedule_view, $data, TRUE);
	        $mpdf->WriteHTML($html);

	        $filename = "endorsement-all-{$record->code}.pdf";
	        // $mpdf->Output($filename,'D');      // make it to DOWNLOAD
	        $mpdf->Output();      // make it to DOWNLOAD
		}
		else
		{
			throw new Exception("Exception [Helper: policy_helper][Method: _ENDORSEMENT_endorsement_pdf()]: No endorsement found.");
		}



        // if( $action === 'save' )
        // {
        // 	$save_full_path = rtrim(INSQUBE_MEDIA_PATH, '/') . '/policies/' . $filename;
        // 	$mpdf->Output($save_full_path,'F');
        // }
        // else if($action === 'download')
        // {
		// 		$mpdf->Output($filename,'D');      // make it to DOWNLOAD
        // }
        // else
        // {
        // 	$mpdf->Output();
        // }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT_premium_basic_v_rules'))
{
	/**
	 * Get common/basic premium validation rules for all portfolios
	 *
	 * @param integer $portfolio_id 	Portfolio ID
	 * @param object $pfs_record		Portfolio Setting Record
	 * @return	array
	 */
	function _ENDORSEMENT_premium_basic_v_rules( $portfolio_id, $pfs_record )
	{
		$CI =& get_instance();

		// Let's have the Endorsement Templates
		$CI->load->model('endorsement_template_model');
		$template_dropdown = $CI->endorsement_template_model->dropdown( $portfolio_id );

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

		return $basic_rules;
	}
}

// ------------------------------------------------------------------------
// POLICY INSTALLMENT HELPER FUNCTIONS
// ------------------------------------------------------------------------


if ( ! function_exists('_POLICY_INSTALLMENT_status_dropdown'))
{
	/**
	 * Get Endorsement Status Dropdown
	 *
	 * @return	bool
	 */
	function _POLICY_INSTALLMENT_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_INSTALLMENT_STATUS_DRAFT			=> 'Due',
			IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED		=> 'Vouchered',
			IQB_POLICY_INSTALLMENT_STATUS_INVOICED		=> 'Invoiced',
			IQB_POLICY_INSTALLMENT_STATUS_PAID			=> 'Paid'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_POLICY_INSTALLMENT_status_text'))
{
	/**
	 * Get Endorsement Status Text
	 *
	 * @return	string
	 */
	function _POLICY_INSTALLMENT_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = _POLICY_INSTALLMENT_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED, IQB_POLICY_INSTALLMENT_STATUS_INVOICED, IQB_POLICY_INSTALLMENT_STATUS_PAID]) )
			{
				// Green
				$css_class = 'text-green';
			}
			else
			{
				// Orange
				$css_class = 'text-orange';
			}

			$text = '<strong class="'.$css_class.'">'.$text.'</strong>';
		}
		return $text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_POLICY_INSTALLMENT_type_dropdown'))
{
    /**
     * Get Installment Type Dropdown
     *
     * @return  bool
     */
    function _POLICY_INSTALLMENT_type_dropdown( $flag_blank_select = true )
    {
        $dropdown = [
            IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER     => 'Invoice to Customer',
            IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER      => 'Refund to Customer'
        ];

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('_POLICY_INSTALLMENT_type_by_endorsement_type'))
{
    /**
     * Get policy installment type by endorsement type
     *
     * Allowed Transaction Types
     *  - Fresh
     *  - Renewal
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * @param   int     Transaction Type
     * @return  array
     */
    function _POLICY_INSTALLMENT_type_by_endorsement_type( $txn_type )
    {
        $txn_type = (int)$txn_type;
        if( !_ENDORSEMENT_is_premium_computable_by_type($txn_type) )
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY_INSTALLMENT_type_by_endorsement_type()]: Invalid Endorsement Type.");
        }

        $installment_type = NULL;
        switch($txn_type)
        {
            case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
            case IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL:
            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
                $installment_type = IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER;
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
                $installment_type = IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER;
                break;

            default:
                break;
        }

        return $installment_type;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_INSTALLMENT__voucher_constraint'))
{
	/**
	 * Check voucher constraint for a policy installment
	 *
	 * Logic:
	 *
	 *  Case 1: First Installment
	 *      The first installment is only eligible for voucher if Endorsement record is eligible
	 *      i.e. either ri_approved or no ri_approval constraint with verified status
	 *
	 *  Case 2: Other installmemnts
	 *      The first installment of this transaction record has to be paid.
	 * 		And its status must be draft
	 *
	 * 	NOTE: You can only generate voucher for the given installment, only if voucher constraint is TRUE
	 *
	 *
	 * @param object 	$record 	Policy Installment Record
	 * @return	bool
	 */
	function _POLICY_INSTALLMENT__voucher_constraint( $record )
	{
		$passed = FALSE;

		/**
		 * Case 1: First Installment
		 */
		if( $record->flag_first == IQB_FLAG_ON )
		{
			$ri_approval_constraint = _ENDORSEMENT__ri_approval_constraint($record->endorsement_status, $record->endorsement_flag_ri_approval);

			$passed = 	($record->endorsement_status === IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED)
					        ||
				    	(	$record->endorsement_status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED
				    			&&
		    				$ri_approval_constraint == FALSE
		    			);
		}
		/**
		 * Case 2: Other Installment
		 */
		else
		{
			$CI =& get_instance();
			$CI->load->model('policy_installment_model');

			$first_installment_status = $CI->policy_installment_model->first_installment_status($record->endorsement_id);

			$passed = 	(
							$first_installment_status === IQB_POLICY_INSTALLMENT_STATUS_PAID
								&&
							$record->status === IQB_POLICY_INSTALLMENT_STATUS_DRAFT
						);
		}

		return $passed;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_INSTALLMENT_validation_rules'))
{
	/**
	 * Get premium installment validation rules for all portfolios
	 *
	 * @param integer $portfolio_id 	Portfolio ID
	 * @param object $pfs_record		Portfolio Setting Record
	 * @return	array
	 */
	function _POLICY_INSTALLMENT_validation_rules( $portfolio_id, $pfs_record )
	{
		$rules = [];

		if($pfs_record->flag_installment === IQB_FLAG_YES )
		{
			$CI =& get_instance();

			// Let's have the Endorsement Templates
			$CI->load->model('policy_installment_model');

			$rules = $CI->policy_installment_model->validation_rules;
		}

		return $rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_INSTALLMENT_list_by_transaction'))
{
	/**
	 * Get the list of installments by a Endorsement
	 *
	 * @param integer $endorsement_id 	Policy TXN ID
	 * @return	array
	 */
	function _POLICY_INSTALLMENT_list_by_transaction( $endorsement_id )
	{
		$CI =& get_instance();
		$CI->load->model('policy_installment_model');

		return $CI->policy_installment_model->get_many_by_endorsement($endorsement_id);
	}
}

// ------------------------------------------------------------------------


