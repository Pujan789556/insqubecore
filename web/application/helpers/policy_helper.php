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

if ( ! function_exists('_POLICY_duration'))
{
    /**
     * Get Policy Duration
     *
     *  Logic: We need to add 1 days on regular date difference
     *
     *  Return Years or Months or Days
     *
     * @param   date start Date
     * @param   date end Date
     * @param   str     Return Type y: years | m: months | d: days
     * @return  integer
     */
    function _POLICY_duration($from, $to, $what)
    {
        // Add 1 day on end date
        $end_date = new DateTime($to);
        $end_date->modify('+1 day');
        $to = $end_date->format('Y-m-d');

        // Let's get the date difference
        return date_difference($from, $to, $what);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_duration_formatted'))
{
    /**
     * Get Policy Duration Formatted
     *
     * Logic: We need to add 1 days on regular date difference
     *
     *  Return X Years Y Months Z Days
     *
     * @param   date    Start date
     * @param   date    End Date
     * @param   str     Language
     * @return  string
     */
    function _POLICY_duration_formatted($from, $to, $lang = 'en')
    {
        // Add 1 day on end date
        $end_date = new DateTime($to);
        $end_date->modify('+1 day');
        $to = $end_date->format('Y-m-d');


        // Let's get the formatted string
        return duration_formatted($from, $to, $lang);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_duration_days_formatted'))
{
    /**
     * Get Policy Duration Formatted - Days
     *
     * Logic: We need to add 1 days on regular date difference
     *
     *  Return X Years Y Months Z Days
     *
     * @param   date    Start date
     * @param   date    End Date
     * @param   str     Language
     * @return  string
     */
    function _POLICY_duration_days_formatted($from, $to, $lang = 'en')
    {
        // Add 1 day on end date
        $end_date = new DateTime($to);
        $end_date->modify('+1 day');
        $to = $end_date->format('Y-m-d');

        $days = date_difference($from, $to, 'd');

        if($lang == 'en')
        {
            $text =  $days == 1 ? $days . ' day' : $days . ' days';
        }
        else
        {
            $text = $days . ' दिन';
        }
        return $text;
    }
}

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
			    // Red
                $css_class = 'text-red';
			}
			else if( $key === IQB_POLICY_STATUS_CANCELED )
			{
                // Gray
                $css_class = 'text-gray';
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
if ( ! function_exists('_POLICY_status_icon'))
{
    /**
     * Get Policy Status Icon
     *
     *
     * @param char $key
     * @return  html
     */
    function _POLICY_status_icon($key)
    {
        $status_list  = _POLICY_status_dropdown(FALSE);

        $title  = $status_list[$key] ?? '';
        $icon   = '';

        if( in_array($key, array_keys( $status_list )) )
        {
            if( $key === IQB_POLICY_STATUS_ACTIVE )
            {
                // Green
                $css_class = 'text-green';
            }
            else if( $key === IQB_POLICY_STATUS_EXPIRED )
            {
                // Red
                $css_class = 'text-red';
            }
            else if( $key === IQB_POLICY_STATUS_CANCELED )
            {
                // Gray
                $css_class = 'text-gray';
            }
            else
            {
                // Orange
                $css_class = 'text-orange';
            }

            $icon = '<i class="fa fa-circle '.$css_class.'" title="'.$title.'" data-toggle="tooltip"></i>';
        }
        return $icon;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY_tags_text'))
{
    /**
     * Get Policy Tag Texts
     *
     * @param   array
     * @return  string
     */
    function _POLICY_tags_text( $tag_ids )
    {
        $CI =& get_instance();

        $CI->load->model('tag_model');
        $dd = $CI->tag_model->dropdown();
        $text = [];
        foreach($tag_ids as $tag_id)
        {
            $text[] = $dd[$tag_id];
        }

        return implode(', ', $text);
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

if ( ! function_exists('_POLICY__get_spr_goodies'))
{
    /**
     * Get Short Term Policy Rate Goodies
     *
     * Compute and Return
     *  - SPR Record,
     *  - Flag
     *  - Defualt Duration
     *
     *
     * @param object $pfs_record Portfolio Setting Record
     * @param date  $start_date Policy Start Date
     * @param date $end_date    Policy End Date
     * @return  array
     */
    function _POLICY__get_spr_goodies( $pfs_record, $start_date, $end_date )
    {
        $CI =& get_instance();
        $CI->load->model('portfolio_setting_model');

        $false_return = [
            'flag'      => IQB_FLAG_NO,
            'record'    => NULL
        ];

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
        $default_duration   = (int)$pfs_record->default_duration;


        $short_term_policy_rates = $pfs_record->short_term_policy_rate ? json_decode($pfs_record->short_term_policy_rate) : NULL;
        if( !$short_term_policy_rates )
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__get_spr_goodies()]: No Short Term Policy Rates found for the supplied portfolio.");
        }

        $rate_list = [];
        foreach($short_term_policy_rates as $r)
        {
            $rate_list[$r->duration] = $r;
        }
        ksort($rate_list);

        // If no spr found, we use this as a default spr
        $spr_record = (object)['rate' => 100.00, 'duration' => $default_duration, 'title' => 'Default Rate'];
        $found_spr = FALSE;
        foreach($rate_list as $duration=>$spr)
        {
            if( $days <= $duration )
            {
                $spr_record = $spr;
                $found_spr  = TRUE;
                break;
            }
        }

        return [
            'flag'              => $found_spr == TRUE ? IQB_FLAG_YES : IQB_FLAG_NO,
            'record'            => $spr_record,
            'default_duration'  => $default_duration
        ];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__get_short_term_flag'))
{
    /**
     * Get Policy Short Term Flat
     *
     * @param int $portfolio_id Portfolio ID
     * @param int $fiscal_yr_id Fiscal Year ID
     * @param date $start_date Policy Start Date
     * @param date $end_date Policy End Date
     * @return char
     */
    function _POLICY__get_short_term_flag( $portfolio_id, $fiscal_yr_id, $start_date, $end_date )
    {
        $CI =& get_instance();

        /**
         * Current Fiscal Year Record & Portfolio Settings for This Fiscal Year
         */
        $pfs_record = $CI->portfolio_setting_model->get_by_fiscal_yr_portfolio($fiscal_yr_id, $portfolio_id);
        if(!$pfs_record)
        {
            $fy_record = $this->fiscal_year_model->get($fiscal_yr_id);
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__get_short_term_info()]: No Portfolio Setting Record found for specified fiscal year {$fy_record->code_np}({$fy_record->code_en})");
        }

        // Get the goodies and return FLAG
        $info = _POLICY__get_spr_goodies( $pfs_record, $start_date, $end_date );
        return $info['flag'];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__schedule_pdf'))
{
    /**
     * Generate Policy Schedule PDF
     *
     * Filename: <policycode>.pdf
     *
     * @param object $record    Policy Record
     * @param string $action    [save|print|download]
     * @param   html $html  HTML
     * @return  void
     */
    function _POLICY__schedule_pdf( $record, $action, $html )
    {
        if( !in_array($action, ['save', 'print', 'download']) )
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: Invalid Action({$action}).");
        }

        if(!$html)
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__schedule_pdf()]: NO SCHEDULE DATA FOUND!.");
        }

        $CI =& get_instance();

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

        // echo $html;exit;
        $mpdf->WriteHTML($html);
        $filename = "policy-{$record->code}.pdf";
        if( $action === 'save' )
        {
            $save_full_path = rtrim(Policies::$data_upload_path, '/') . '/' . $filename;
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
			case IQB_SUB_PORTFOLIO_MARINE_AIR_ROAD_TRANSIT_ID:
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

if ( ! function_exists('_POLICY__schedule_anchor'))
{
    /**
     * Generate Policy Schedule Anchor
     *
     * @param Object $record    Policy Record
     * @return  html
     */
    function _POLICY__schedule_anchor( $record )
    {
        if( !in_array($record->status, [IQB_POLICY_STATUS_ACTIVE, IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED]))
        {
            $anchor = anchor(
                        'policies/debitnote/'.$record->id,
                        '<i class="fa fa-print"></i> Debit Note',
                        [
                            'title' => 'Print Policy Debit Note',
                            'target' => '_blank',
                            'class' => 'btn bg-navy btn-round',
                            'data-toggle' => 'tooltip'
                        ]
                    );
        }
        else
        {
            $anchor = anchor(
                        'policies/schedule/'.$record->id,
                        '<i class="fa fa-print"></i> Schedule',
                        [
                            'title' => 'Print Policy Schedule',
                            'target' => '_blank',
                            'class' => 'btn bg-navy btn-round',
                            'data-toggle' => 'tooltip'
                        ]
                    );

        }
        return $anchor;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_POLICY__schedule_exists'))
{
    /**
     * Check if Policy Schedule PDF Exists
     *
     * @param   string $code    Policy Code
     * @return  bool
     */
    function _POLICY__schedule_exists( $code )
    {
        $filename = "policy-{$code}.pdf";
        $schedule_full_path = rtrim(INSQUBE_DATA_ROOT, '/') . '/policies/' . $filename;

        return file_exists($schedule_full_path);
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

if ( ! function_exists('_POLICY_schedule_header_footer'))
{
    /**
     * Get Policy Schedule Header/Footer HTML
     *
     * @param object $record   Policy Record
     * @return  string
     */
    function _POLICY_schedule_header_footer( $record )
    {
        $CI =& get_instance();

        $creator_text = $record->created_by_username . ' - ' . $record->created_by_code;
        if($record->created_by_profile)
        {
            $_u_profile = json_decode($record->created_by_profile);
            $creator_text = htmlspecialchars( $_u_profile->name ) . ' - ' . $record->created_by_code;
        }

        $verifier_text = $record->verified_by_username . ' - ' . $record->verified_by_code;
        if($record->verified_by_profile)
        {
            $_u_profile = json_decode($record->verified_by_profile);
            $verifier_text = htmlspecialchars($_u_profile->name) . ' - ' . $record->verified_by_code;
        }

        $seller_text = $record->sold_by_username . ' - ' . $record->sold_by_code;
        if($record->sold_by_profile)
        {
            $_u_profile = json_decode($record->sold_by_profile);
            $seller_text = htmlspecialchars($_u_profile->name) . ' - ' . $record->sold_by_code;
        }

        $branch_contact_prefix = htmlspecialchars( $CI->settings->orgn_name_en . ', ' . $record->branch_name_en);

        $header_footer = '<htmlpagefooter name="myfooter">
                            <table class="table table-footer no-border">
                                <tr>
                                    <td align="left" class="border-b"> Created By: ' . $creator_text . ' </td>
                                    <td align="left" class="border-b"> Business By: ' . $seller_text . ' </td>
                                    <td align="right" class="border-b"> Verified By: ' . $verifier_text . ' </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="border-t">'. address_widget_two_lines( parse_address_record($record, 'addr_branch_'), $branch_contact_prefix) .'</td>
                                </tr>
                            </table>
                        </htmlpagefooter>
                        <sethtmlpagefooter name="myfooter" value="on" />';

        return $header_footer;
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
			IQB_POLICY_ENDORSEMENT_TYPE_FRESH 		         => 'Fresh',
			IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED 	      => 'Time Extended',

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
            IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED       => 'Time Extended',
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
if ( ! function_exists('_ENDORSEMENT_is_transactional_by_type'))
{
    /**
     * Check if given Endorsement type is Transactional.
     *
     * Allowed Transaction Types
     *  - Fresh
     *  - Renewal
     *  - Ownership Transfer
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * @param   int     Transaction Type
     * @return  array
     */
    function _ENDORSEMENT_is_transactional_by_type( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
            IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];

        return in_array($txn_type, $allowed_types);
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_refundable'))
{
    /**
     * Check if given Endorsement type is Transactional.
     *
     * Allowed Transaction Types
     *  - Fresh
     *  - Renewal
     *  - Ownership Transfer
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * @param   int     Transaction Type
     * @return  array
     */
    function _ENDORSEMENT_is_refundable( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
            IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE // In case of Terminate and Refund
        ];

        return in_array($txn_type, $allowed_types);
    }
}


// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_transactional'))
{
    /**
     * Check if given Endorsement is Transactional.
     *
     * Either its type must be transactional
     * OR Termination type with Refund Premium
     *
     * @param   object  $record Endorsement Record
     * @return  array
     */
    function _ENDORSEMENT_is_transactional( $record )
    {
        $allowed = _ENDORSEMENT_is_transactional_by_type( $record->txn_type )
                        ||
                    (
                        $record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE
                            &&
                        $record->flag_refund_on_terminate === IQB_FLAG_YES
                    );

        return $allowed;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_premium_only_types'))
{
    /**
     *  Get the list of all premium types constants
     *
     *  - Fresh
     *  - Renewal
     *  - Premium Upgrade
     *  - Premium Downgrade
     *
     * @param   int     Transaction Type
     * @return  array
     */
    function _ENDORSEMENT_premium_only_types(  )
    {
        return [
            IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
            IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
        ];
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
        $CI =& get_instance();
        $CI->load->model('endorsement_model');

        return $CI->endorsement_model->is_first($txn_type);
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
			IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
			IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND,
		];

		return in_array($txn_type, $allowed_types);
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('_ENDORSEMENT_is_invoicable_by_type'))
{
    /**
     * Check if given Endorsement type is Invoicable.
     *
     * Allowed Transaction Types
     *  - Fresh
     *  - Renewal
     *  - Premium Upgrade
     *
     * @param   int     Transaction Type
     * @return  array
     */
    function _ENDORSEMENT_is_invoicable_by_type( $txn_type )
    {
        $txn_type       = (int)$txn_type;
        $allowed_types  = [
            IQB_POLICY_ENDORSEMENT_TYPE_FRESH,
            IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED,
            IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE,
            IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER,
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
if ( ! function_exists('_ENDORSEMENT_compute_reference_dropdown'))
{
	/**
	 * Get Endorsement Computation Basis Dropdown
	 *
	 * @return	array
	 */
	function _ENDORSEMENT_compute_reference_dropdown( $flag_blank_select = true )
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

			if($record && $record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_FRESH )
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
	        // $mpdf->SetMargins(10, 5, 10, 5);
	        // $mpdf->margin_header = 5;
	        // $mpdf->margin_footer = 5;
            $mpdf->setAutoTopMargin = true;
            $mpdf->setAutoBottomMargin = true;

            // Image Error
            $mpdf->showImageErrors = true;

	        $mpdf->SetProtection(array('print'));
	        $mpdf->SetTitle("Policy Endorsement - {$record->policy_code}");
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

	        $filename = "endorsement-all-{$record->policy_code}.pdf";
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

		$basic_rules = [
			[
                'field' => 'net_amt_stamp_duty',
                'label' => 'Stamp Duty(Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[10]',
                '_type'     => 'text',
                '_default' 	=> $pfs_record->stamp_duty,
                '_required' => true
            ]
		];

		return $basic_rules;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__save_premium_manual'))
{
    /**
     * Save Endorsement Premium Manually
     *
     * This function is used to save manual endorsement for the following types
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * Currently Identified Portfolios are:
     *  - ENG - CAR
     *  - ENG - EAR
     *  - FIRE - FIRE
     *  - MISC - TMI
     *
     * @param Ojbect $endorsement_record
     * @param Ojbect $policy_record
     * @return  bool
     */
    function _ENDORSEMENT__save_premium_manual( $endorsement_record, $policy_record, $post_data )
    {
        $CI =& get_instance();

        if( $CI->input->post() )
        {
            /**
             * Manual Validatio Rules
             */
            $v_rules = $CI->endorsement_model->manual_premium_v_rules();
            $CI->form_validation->set_rules($v_rules);

            if($CI->form_validation->run() === TRUE )
            {
                return $CI->endorsement_model->save_premium_manual($endorsement_record, $policy_record, $post_data);
            }
            else
            {
                return $CI->template->json([
                    'status'    => 'error',
                    'title'     => 'Validation Error!',
                    'message'   => validation_errors()
                ]);
            }
        }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__is_portfolio_premium_manual'))
{
    /**
     * Check if given portfolio's Endorsement's Premium is to compute manually.
     *
     * This function is used to save manual endorsement for the following types
     *  - Premium Upgrade
     *  - Premium Refund
     *
     * Currently Identified Portfolios are:
     *  - AGR - ALL SUB PORTFOLIO
     *  - ENG - CAR
     *  - ENG - EAR
     *  - FIRE - FIRE
     *  - MISC - TMI
     *
     * @param int   $portfolio_id   Portfolio ID
     * @param int   $txn_type  Endorsement TXN Type
     * @return  bool
     */
    function _ENDORSEMENT__is_portfolio_premium_manual( $portfolio_id, $txn_type )
    {
        $portfolio_id = (int)$portfolio_id;
        $txn_type = (int)$txn_type;

        // Allowed Portfolios
        $manual_portolios   = [IQB_SUB_PORTFOLIO_ENG_CAR_ID, IQB_SUB_PORTFOLIO_ENG_EAR_ID, IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID, IQB_SUB_PORTFOLIO_MISC_TMI_ID];
        $agro_ids           = array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR);
        $manual_portolios   = array_merge($manual_portolios, $agro_ids);

        // Allowed Txn Types
        $txn_types          = [IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND];


        return in_array($portfolio_id, $manual_portolios) && in_array($txn_type, $txn_types);

    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__tariff_premium_defaults'))
{
    /**
     * Compute the Default Premium for Basic and Pool for Tariff Portfolio
     *
     * This is when the tariff-computed premium falls below the default basic and/or pool
     * we use the default
     *
     * @param array   $data   Endorsement Data
     * @param array   $defaults  Default ['basic' => xxx, 'pool' => yyy]
     * @param bool    $skip_pool_on_zero  Skip Pool Premium if Zero
     * @return  bool
     */
    function _ENDORSEMENT__tariff_premium_defaults( $data, $defaults, $skip_pool_on_zero = FALSE )
    {
        $default_basic  = $defaults['basic'];
        $default_pool   = $defaults['pool'];

        $data['gross_amt_basic_premium']  = $data['gross_amt_basic_premium'] < $default_basic ? $default_basic : $data['gross_amt_basic_premium'];

        /**
         * This gives a option to compute pool premium only if it is not zero
         */
        if( !$skip_pool_on_zero || $data['gross_amt_pool_premium'] > 0.00 )
        {
            $data['gross_amt_pool_premium'] = $data['gross_amt_pool_premium'] < $default_pool ? $default_pool : $data['gross_amt_pool_premium'];
        }


        return $data;
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('_ENDORSEMENT__compute_total_amount'))
{
    /**
     * Compute the Total Amount for this Endorsement
     *
     * @param integer $record   Policy Endorsement Record
     * @return  float
     */
    function _ENDORSEMENT__compute_total_amount( $record )
    {
        $CI =& get_instance();
        $CI->load->model('endorsement_model');
        return $CI->endorsement_model->total_amount($record);
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
            IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER     => 'Premium', // Invoice to Customer (Income)
            IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER      => 'Refund'   // Refund to Customer (Expense)
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
        $txn_type           = (int)$txn_type;
        $installment_type   = NULL;
        switch($txn_type)
        {
            case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
            case IQB_POLICY_ENDORSEMENT_TYPE_TIME_EXTENDED:
            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
            case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
                $installment_type = IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER;
                break;

            case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
            case IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE:
                $installment_type = IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER;
                break;

            default:
                break;
        }

        if( !$installment_type )
        {
            throw new Exception("Exception [Helper: policy_helper][Method: _POLICY_INSTALLMENT_type_by_endorsement_type()]: Invalid Endorsement Type.");
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

if ( ! function_exists('_POLICY_INSTALLMENT_compute_total_amount'))
{
    /**
     * Compute the Total Amount for this Installment
     *
     * @param integer $record   Policy Installment Record
     * @return  float
     */
    function _POLICY_INSTALLMENT_compute_total_amount( $record )
    {
        $CI =& get_instance();
        $CI->load->model('policy_installment_model');
        return $CI->policy_installment_model->total_amount($record);
    }
}

// ------------------------------------------------------------------------
// GENERAL PORTFOLIO HELPERS
// ------------------------------------------------------------------------


if ( ! function_exists('portfolio_risks'))
{
    /**
     * Get the list of Portfolio risks
     *
     * @param integer $portfolio_id   Portfolio ID
     * @return  array
     */
    function portfolio_risks( $portfolio_id )
    {
        $CI =& get_instance();
        $CI->load->model('portfolio_model');
        return $CI->portfolio_model->portfolio_risks($portfolio_id);
    }
}

