<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Claim Helper Functions
 *
 * This file contains helper functions related to Claim
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__status_dropdown'))
{
	/**
	 * Get Claim Status Dropdown
	 *
	 * @return	bool
	 */
	function CLAIM__status_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_CLAIM_STATUS_DRAFT 			=> 'Draft',
			IQB_CLAIM_STATUS_VERIFIED 		=> 'Verified',
			IQB_CLAIM_STATUS_APPROVED 		=> 'Approved',
			IQB_CLAIM_STATUS_SETTLED 		=> 'Settled',
			IQB_CLAIM_STATUS_WITHDRAWN 		=> 'Withdrawn',
			IQB_CLAIM_STATUS_CLOSED 		=> 'Closed'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__is_closed'))
{
	/**
	 * Is Claim Closed?
	 *
	 * @return	bool
	 */
	function CLAIM__is_closed( $status )
	{
		return $status === IQB_CLAIM_STATUS_CLOSED;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__is_widthdrawn'))
{
	/**
	 * Is Claim Withdrawn?
	 *
	 * @return	bool
	 */
	function CLAIM__is_widthdrawn( $status )
	{
		return $status === IQB_CLAIM_STATUS_WITHDRAWN;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__death_injured_type_dropdown'))
{
	/**
	 * Get Death/Injured Type Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__death_injured_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'DTH' => 'Death',
			'INJ' => 'Injured',
			'PRD' => 'Partially Disabled',
			'FLD' => 'Fully Disabled',
			'ILN' => 'Illness'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_type_dropdown'))
{
	/**
	 * Get Surveyor Type Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__surveyor_type_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'P' => 'Preliminary',
			'F' => 'Final',
			'R' => 'Re-inspection'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__supporting_docs_dropdown'))
{
	/**
	 * Get supporting documents dropdown
	 *
	 * @return	array
	 */
	function CLAIM__supporting_docs_dropdown( $portfolio_id, $flag_blank_select = true )
	{
		$CI =& get_instance();

		$CI->load->model('portfolio_model');
        $dropdown = $CI->portfolio_model->dropdown_claim_docs($portfolio_id);
		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------


if ( ! function_exists('CLAIM__is_editable'))
{
	/**
	 * Is Claim Editable?
	 *
	 * Check if the given policy claim is editable.
	 *
	 * @param char $status 	Claim Status
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function CLAIM__is_editable($status, $terminate_on_fail = TRUE )
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
		 * 		edit.claim.draft
		 */

		// Editable Permissions ?
		if(
			$status === IQB_CLAIM_STATUS_DRAFT
				&&
			( $CI->dx_auth->is_admin() || $CI->dx_auth->is_authorized('claims', 'edit.claim.draft') )
		)
		{
			$__flag_authorized = TRUE;
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

if ( ! function_exists('CLAIM__approval_constraint'))
{
	/**
	 * Is Claim Eligible to approve?
	 *
	 * Check if the given policy claim is valid for approval.
	 * The following criteria must be met:
	 *
	 * 		1. net_amt_payable_insured must be set
     *  	2. claim_scheme_id must be set
     *  	3. assessment_note must be set
	 *
	 * @param object $record 	Claim Record
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function CLAIM__approval_constraint($record, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		// Authorized Flag ?
		$__flag_authorized 		= TRUE;


		/**
		 * Applied Claim Scheme?
		 */
		if( $record->claim_scheme_id == NULL )
		{
			$__flag_authorized = FALSE;

			$message = 'You must first set  <strong>"Claim Scheme"</strong> in order to approve a claim.';
		}

		/**
		 * Claim Settlement ??
		 */
		elseif( !CLAIM__net_total_payable_insured($record->id) )
		{
			$__flag_authorized = FALSE;

			$message = 'You must first update <strong>"Claim Settlement Amount"</strong> in order to approve a claim.';
		}

		/**
		 * Claim Assessment ??
		 */
		elseif( empty($record->assessment_note) OR empty($record->status_remarks))
		{
			$__flag_authorized = FALSE;

			$message = 'You must first update <strong>"Update Claim Assessment"</strong> in order to approve a claim.';
		}


		/**
		 * Beema Samiti Reports ??
		 */
		if( $__flag_authorized  )
		{
			$CI->load->model('rel_claim_bsrs_heading_model');
			$rel_exists = $CI->rel_claim_bsrs_heading_model->rel_exists($record->id);

			if(!$rel_exists)
			{
				$__flag_authorized 	= FALSE;
				$message = 'You must first update <strong>"Beema Samiti Report Information"</strong> in order to approve a claim.';
			}
		}


		// Terminate on Exit?
		if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
		{
			$CI =& get_instance();

			$CI->dx_auth->deny_access('deny', $message);
			exit(1);
		}

		return $__flag_authorized;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__settlement_category_dropdown'))
{
	/**
	 * Get Claim Settlement Category Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__settlement_category_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'OD' => 'Own Damage',
			'TP' => 'Third Party',
			'ED' => 'Excess Deductible',
			'NA' => 'Not Applicable'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__settlement_subcategory_dropdown'))
{
	/**
	 * Get Claim Settlement Category Dropdown
	 *
	 * @return	array
	 */
	function CLAIM__settlement_subcategory_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			'DTH' => 'Death',
			'INJ' => 'Injured',
			'PRD' => 'Partially Disabled',
			'FLD' => 'Fully Disabled',
			'ILN' => 'Illness',
			'PRP' => 'Property Damage',
			'NA'  => 'Not Applicable'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__flag_surveyor_voucher_dropdown'))
{
	/**
	 * Get Claim flag_surveyor_voucher Dropdown
	 *
	 * @return	bool
	 */
	function CLAIM__flag_surveyor_voucher_dropdown( $flag_blank_select = true )
	{
		$dropdown = [
			IQB_CLAIM_FLAG_SRV_VOUCHER_NOT_REQUIRED 	=> 'Not Required',
			IQB_CLAIM_FLAG_SRV_VOUCHER_REQUIRED 		=> 'Required',
			IQB_CLAIM_FLAG_SRV_VOUCHER_VOUCHERED 		=> 'Vouchered'
		];

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__total_estimated_amount'))
{
	/**
	 * Get Total Esitmated Claim Amount
	 *
	 *
	 * @param 	object|id $record 	Claim Record or ID
	 * @return	decimal
	 */
	function CLAIM__total_estimated_amount( $record )
	{
		$CI =& get_instance();
		$CI->load->model('claim_model');

		return $CI->claim_model->compute_total_estimated_amount($record);
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_vat_total_by_claim'))
{
	/**
	 * VAT Total - From All surveyors of a Claim
	 *
	 * @param int $claim_id
	 * @return	decimal
	 */
	function CLAIM__surveyor_vat_total_by_claim( $claim_id)
	{
		$CI =& get_instance();
		$CI->load->model('claim_surveyor_model');

		return $CI->claim_surveyor_model->compute_vat_total_by_claim($claim_id);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_net_total_fee'))
{
	/**
	 * Get NET Total fee for a surveyor
	 *
	 * FORMULA: surveyor_fee + other_fee + vat_amount - tds_amount
	 *
	 * @param 	Object $claim_surveyor_record Surveyor_Claim Record or dt_claim_surveyors Primary KEY (ID)
	 * @return	decimal
	 */
	function CLAIM__surveyor_net_total_fee( $claim_surveyor_record)
	{
		$CI =& get_instance();
		$CI->load->model('claim_surveyor_model');

		return $CI->claim_surveyor_model->compute_net_total_fee($claim_surveyor_record);

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_net_total_fee_by_claim'))
{
	/**
	 * Get NET Total Surveyor Fee for a Claim
	 *
	 *
	 * @param 	int $claim_id
	 * @return	decimal
	 */
	function CLAIM__surveyor_net_total_fee_by_claim( $claim_id)
	{
		$CI =& get_instance();
		$CI->load->model('claim_surveyor_model');

		return $CI->claim_surveyor_model->compute_net_total_fee_by_claim($claim_id);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_gross_total_fee'))
{
	/**
	 * Get Gross Total fee for a surveyor
	 *
	 * FORMULA: surveyor_fee + other_fee
	 *
	 * @param 	Object $claim_surveyor_record Surveyor_Claim Record or dt_claim_surveyors Primary KEY (ID)
	 * @return	decimal
	 */
	function CLAIM__surveyor_gross_total_fee( $claim_surveyor_record)
	{
		$CI =& get_instance();
		$CI->load->model('claim_surveyor_model');

		return $CI->claim_surveyor_model->compute_gross_total_fee($claim_surveyor_record);

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__surveyor_gross_total_fee_by_claim'))
{
	/**
	 * Get Gross Total Surveyor Fee for a Claim
	 *
	 *
	 * @param 	int $claim_id
	 * @return	decimal
	 */
	function CLAIM__surveyor_gross_total_fee_by_claim( $claim_id)
	{
		$CI =& get_instance();
		$CI->load->model('claim_surveyor_model');

		return $CI->claim_surveyor_model->compute_gross_total_fee_by_claim($claim_id);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__net_total_payable_insured'))
{
	/**
	 * Get Net Total Payble to Insured Party
	 *
	 * @param 	int $claim_id
	 * @return	decimal
	 */
	function CLAIM__net_total_payable_insured( $claim_id)
	{
		$CI =& get_instance();
		$CI->load->model('claim_settlement_model');

		return $CI->claim_settlement_model->compute_net_payable($claim_id);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__gross_total'))
{
	/**
	 * Gross Total of a Claim
	 *
	 * @param 	int $claim_id
	 * @param 	bool $surveyor_fee_only
	 * @return	decimal
	 */
	function CLAIM__gross_total($claim_id, $surveyor_fee_only = FALSE)
	{
		$CI =& get_instance();
		$CI->load->model('claim_model');

		return $CI->claim_model->compute_claim_gross_total($claim_id, $surveyor_fee_only);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__net_total'))
{
	/**
	 * NET Total of a Claim
	 *
	 * @param 	int $claim_id
	 * @param 	bool $surveyor_fee_only
	 * @return	decimal
	 */
	function CLAIM__net_total($claim_id, $surveyor_fee_only = FALSE)
	{
		$CI =& get_instance();
		$CI->load->model('claim_model');

		return $CI->claim_model->compute_claim_net_total($claim_id, $surveyor_fee_only);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__ri_breakdown'))
{
	/**
	 * Get Claim RI Breakdown
	 *
	 * @param 	Object $record Claim Record
	 * @param 	Object $for_display For Data Display?
	 * @return	decimal
	 */
	function CLAIM__ri_breakdown( $record, $for_display = FALSE)
	{
		$CI =& get_instance();
		$CI->load->model('claim_model');

		return $CI->claim_model->ri_breakdown($record, $for_display);

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__ri_breakdown_estimated'))
{
	/**
	 * Get Claim RI Breakdown
	 *
	 * @param 	Object $record Claim Record
	 * @param 	Object $for_display For Data Display?
	 * @return	decimal
	 */
	function CLAIM__ri_breakdown_estimated( $record, $for_display = FALSE)
	{
		$CI =& get_instance();
		$CI->load->model('claim_model');

		return $CI->claim_model->ri_breakdown_estimated($record, $for_display);

	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('CLAIM__discharge_voucher_pdf'))
{
    /**
     * Print Policy Endorsement PDF
     *
     * @param array $data
     * @return  void
     */
    function CLAIM__discharge_voucher_pdf( $data )
    {
    	$CI =& get_instance();

    	// Claim Record??
    	if( !isset($data['record']) )
		{
			throw new Exception("Exception [Helper: claim_helper][Method: CLAIM__discharge_voucher_pdf()]: No claim information Found.");
		}

		// Endorsement Record??
		if( !isset($data['endorsement']) )
		{
			throw new Exception("Exception [Helper: claim_helper][Method: CLAIM__discharge_voucher_pdf()]: No endorsement information Found.");
		}

		$record = $data['record'];

		$CI->load->library('pdf');
        $mpdf = $CI->pdf->load();
        // $mpdf->SetMargins(10, 20, 10, 20);
        // $mpdf->SetMargins(10, 5, 10, 5);
        // $mpdf->margin_header = 5;
        // $mpdf->margin_footer = 5;
        $mpdf->setAutoTopMargin = true;
        $mpdf->setAutoBottomMargin = true;

        // Image Error
        $mpdf->showImageErrors = true;

        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle("Policy Claim - {$record->claim_code}");
        $mpdf->SetAuthor($CI->settings->orgn_name_en);

        /**
         * Only Active Endorsement Does not have watermark!!!
         */
        if( $record->status == IQB_CLAIM_STATUS_DRAFT )
        {
        	$mpdf->SetWatermarkText( 'DRAFT - ' . strtoupper(CLAIM__status_dropdown(FALSE)[$record->status] ) );

        	$mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
        }

        $mpdf->SetDisplayMode('fullpage');

        $schedule_view 	= 'claims/print/discharge_voucher';
        $html 			= $CI->load->view( $schedule_view, $data, TRUE);
        $mpdf->WriteHTML($html);

        $filename = "claim-discharge-voucher-{$record->claim_code}.pdf";
        $mpdf->Output($filename, 'I'); // Render in Browser



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

if ( ! function_exists('CLAIM__note_pdf'))
{
    /**
     * Print Policy Endorsement PDF
     *
     * @param array $data
     * @return  void
     */
    function CLAIM__note_pdf( $data )
    {
    	$CI =& get_instance();

    	// Claim Record??
    	if( !isset($data['record']) )
		{
			throw new Exception("Exception [Helper: claim_helper][Method: CLAIM__note_pdf()]: No claim information Found.");
		}

		// Endorsement Record??
		if( !isset($data['policy_record']) )
		{
			throw new Exception("Exception [Helper: claim_helper][Method: CLAIM__note_pdf()]: No policy information Found.");
		}

		$record = $data['record'];

		$CI->load->library('pdf');
        $mpdf = $CI->pdf->load();
        // $mpdf->SetMargins(10, 20, 10, 20);
        // $mpdf->SetMargins(10, 5, 10, 5);
        // $mpdf->margin_header = 5;
        // $mpdf->margin_footer = 5;
        $mpdf->setAutoTopMargin = true;
        $mpdf->setAutoBottomMargin = true;

        // Image Error
        $mpdf->showImageErrors = true;

        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle("Claim Note - {$record->claim_code}");
        $mpdf->SetAuthor($CI->settings->orgn_name_en);

        /**
         * Only Active Endorsement Does not have watermark!!!
         */
        if( $record->status == IQB_CLAIM_STATUS_DRAFT )
        {
        	$mpdf->SetWatermarkText( 'DRAFT - ' . strtoupper(CLAIM__status_dropdown(FALSE)[$record->status] ) );

        	$mpdf->showWatermarkText = true;
	        $mpdf->watermark_font = 'DejaVuSansCondensed';
	        $mpdf->watermarkTextAlpha = 0.1;
        }

        $mpdf->SetDisplayMode('fullpage');

        $schedule_view 	= 'claims/print/note';
        $html 			= $CI->load->view( $schedule_view, $data, TRUE);
        $mpdf->WriteHTML($html);

        $filename = "claim-note-{$record->claim_code}.pdf";
        $mpdf->Output($filename, 'I'); // Render in Browser



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


