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

if ( ! function_exists('get_policy_status_dropdown'))
{
	/**
	 * Get Policy Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [

			IQB_POLICY_STATUS_DRAFT 		=> 'Draft',
			IQB_POLICY_STATUS_UNVERIFIED 	=> 'Unverified',
			IQB_POLICY_STATUS_VERIFIED 		=> 'Verified',
			IQB_POLICY_STATUS_APPROVED 		=> 'Approved',
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

if ( ! function_exists('get_policy_status_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function get_policy_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_STATUS_APPROVED, IQB_POLICY_STATUS_ACTIVE]) )
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
if ( ! function_exists('get_policy_txn_status_dropdown'))
{
	/**
	 * Get Policy Transaction Status Dropdown
	 *
	 * @return	bool
	 */
	function get_policy_txn_status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_STATUS_DRAFT			=> 'Draft',
			IQB_POLICY_TXN_STATUS_UNVERIFIED	=> 'Unverified',
			IQB_POLICY_TXN_STATUS_VERIFIED		=> 'Verified',
			IQB_POLICY_TXN_STATUS_RI_APPROVED	=> 'RI Approved',
			IQB_POLICY_TXN_STATUS_APPROVED		=> 'Approved',
			IQB_POLICY_TXN_STATUS_VOUCHERED		=> 'Vouchered',
			IQB_POLICY_TXN_STATUS_INVOICED		=> 'Invoiced',
			IQB_POLICY_TXN_STATUS_ACTIVE		=> 'Active'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_txn_status_text'))
{
	/**
	 * Get Policy Transaction Status Text
	 *
	 * @return	string
	 */
	function get_policy_txn_status_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_txn_status_dropdown();

		$text = $list[$key] ?? '';

		if($formatted && $text != '')
		{
			if( in_array($key, [IQB_POLICY_TXN_STATUS_APPROVED, IQB_POLICY_TXN_STATUS_VOUCHERED, IQB_POLICY_TXN_STATUS_INVOICED, IQB_POLICY_TXN_STATUS_ACTIVE]) )
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
if ( ! function_exists('get_policy_txn_type_dropdown'))
{
	/**
	 * Get Policy Transaction Status Dropdown
	 *
	 * @return	array
	 */
	function get_policy_txn_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_TXN_TYPE_FRESH 		=> 'Fresh',
			IQB_POLICY_TXN_TYPE_RENEWAL 	=> 'Renewal',
			IQB_POLICY_TXN_TYPE_ET 			=> 'Endorsement-TXNL',
			IQB_POLICY_TXN_TYPE_EG 			=> 'Endorsement-GNRL'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_txn_type_text'))
{
	/**
	 * Get Policy Status Text
	 *
	 * @return	string
	 */
	function get_policy_txn_type_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_txn_type_dropdown();

		$text = $list[$key] ?? '';

		return $text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_crf_transfer_type_dropdown'))
{
	/**
	 * Get Policy CRF Transfer Type Dropdown
	 *
	 * @return	array
	 */
	function get_policy_crf_transfer_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_CRF_TRANSFER_TYPE_FULL 						=> 'Transfer Full Amount',
			IQB_POLICY_CRF_TRANSFER_TYPE_PRORATA_ON_DIFF 			=> 'Transfer Prorata on Difference',
			IQB_POLICY_CRF_TRANSFER_TYPE_SHORT_TERM_RATE_ON_FULL 	=> 'Transfer Short Term Rate on Full Amount',
			IQB_POLICY_CRF_TRANSFER_TYPE_DIRECT_DIFF 				=> 'Transfer Direct Difference'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_crf_transfer_type_text'))
{
	/**
	 * Get Policy CRF Transfer Type Text
	 *
	 * @return	string
	 */
	function get_policy_crf_transfer_type_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_txn_type_dropdown();

		$text = $list[$key] ?? '';

		return $text;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_crf_computation_type_dropdown'))
{
	/**
	 * Get Policy CRF Computation Type Dropdown
	 *
	 * @return	array
	 */
	function get_policy_crf_computation_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_POLICY_CRF_COMPUTE_AUTO 	=> 'Automatic',
			IQB_POLICY_CRF_COMPUTE_MANUAL 	=> 'Manual'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('get_policy_crf_computation_type_text'))
{
	/**
	 * Get Policy CRF Computation Type Text
	 *
	 * @return	string
	 */
	function get_policy_crf_computation_type_text( $key, $formatted = FALSE, $sentence = FALSE )
	{
		$list = get_policy_txn_type_dropdown();

		$text = $list[$key] ?? '';

		return $text;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_policy_editable'))
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
	function is_policy_editable( $status, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 		= FALSE;
		$__flag_editable_status = FALSE;

		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft | unverified
		 *
		 * Editable Permissions Are
		 * 		edit.draft.policy | edit.unverified.policy
		 */
		$editable_status 		= [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED];

		// Editable Status?
		if( in_array($status, $editable_status) )
		{
			$__flag_editable_status = TRUE;
		}

		// Editable Permissions ?
		if( $__flag_editable_status )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policies', 'edit.draft.policy') )

				||

				( $status === IQB_POLICY_STATUS_UNVERIFIED &&  $CI->dx_auth->is_authorized('policies', 'edit.unverified.policy') )

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

if ( ! function_exists('is_policy_txn_editable'))
{
	/**
	 * Is Policy Transaction Editable?
	 *
	 * Check if the given policy transaction is editable.
	 *
	 * @param char $status 	Policy Transaction Status
	 * @param char $flag_current 	Is this Current Policy Transaction
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function is_policy_txn_editable($status, $flag_current, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Editable Permissions ?
		$__flag_authorized 		= FALSE;
		$__flag_editable_status = FALSE;


		/**
		 * Check Permissions
		 *
		 * Editable Status
		 * 		draft | unverified
		 *
		 * Editable Permissions Are
		 * 		edit.draft.transaction | edit.unverified.transaction
		 */
		$editable_status 	= [IQB_POLICY_TXN_STATUS_DRAFT, IQB_POLICY_TXN_STATUS_UNVERIFIED];

		// Editable Status? Must be Current Transaction
		if( in_array($status, $editable_status) && $flag_current == IQB_FLAG_ON)
		{
			$__flag_editable_status = TRUE;
		}

		// Editable Permissions ?
		if( $__flag_editable_status )
		{
			if(
				$CI->dx_auth->is_admin()

				||

				( $status === IQB_POLICY_TXN_STATUS_DRAFT &&  $CI->dx_auth->is_authorized('policy_txn', 'edit.draft.transaction') )

				||

				( $status === IQB_POLICY_TXN_STATUS_UNVERIFIED &&  $CI->dx_auth->is_authorized('policy_txn', 'edit.unverified.transaction') )

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
		$partial_view = '';

		/**
		 * MOTOR
		 * -----
		 * For all type of motor portfolios, we have same package list
		 */
		if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
		{
			$view_prefix = $view_for === 'print' ? '_print' : '';
			$partial_view = "policy_txn/snippets/{$view_prefix}_cost_calculation_table_MOTOR";
		}
		return $partial_view;
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

if ( ! function_exists('_POLICY__ri_approval_constraint'))
{
	/**
	 * RI Approval Constraint on Policy
	 *
	 * Check if the current policy transaction record requires RI Approval and is Approved
	 * i.e.
	 * 		if RI Approval required and not approved yet, it returns TRUE
	 * 		FALSE otherwise.
	 *
	 * @param char $policy_id_or_txn_record 	Policy ID or Txn Record
	 * @return	bool
	 */
	function _POLICY__ri_approval_constraint( $policy_id_or_txn_record )
	{
		$CI =& get_instance();
		$CI->load->model('policy_txn_model');

		return $CI->policy_txn_model->ri_approved($policy_id_or_txn_record) != TRUE;
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
     * @param array $data 		['record' => xxx, 'txn_record' => yyy]
     * @param string $action 	[save|print]
     * @return  void
     */
    function _POLICY__schedule_pdf( $data, $action )
    {
    	if( !in_array($action, ['save', 'print', 'download']) )
    	{
    		throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__save_schedule()]: Invalid Action({$action}).");
    	}

    	$CI =& get_instance();

		/**
		 * Extract Policy Record and Policy Transaction Record
		 */
		$record 		= $data['record'];
		$txn_record 	= $data['txn_record'];
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
		        	$mpdf->SetWatermarkText( 'DEBIT NOTE - ' . strtoupper(get_policy_status_text($record->status)) );
		        }
		    }

	        $mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
	        $mpdf->SetDisplayMode('fullpage');

	        $html = $CI->load->view( $schedule_view, $data, TRUE);
	        $mpdf->WriteHTML($html);
	        // $filename = $data_path . "policy-{$record->code}.pdf";
	        $filename = "policy-{$record->code}.pdf";
	        if( $action === 'save' )
	        {
	        	$save_full_path = rtrim(INSQUBE_DATA_PATH, '/') . '/policies/' . $filename;
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
			throw new Exception("Exception [Helper: policy_helper][Method: _POLICY__save_schedule()]: No schedule view exists for given portfolio({$record->portfolio_name}).");
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
			// Motor
			case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
			case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
			case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
					$schedule_view = 'policies/print/schedule_MOTOR';
				break;

			default:
				# code...
				break;
		}

		return $schedule_view;
    }
}

// ------------------------------------------------------------------------



