<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Account Helper Functions
 *
 * This file contains helper functions related to Accounting
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('ac_party_types_dropdown'))
{
	/**
	 * Get Accounting Party Types Dropdown
	 *
	 * @return	string
	 */
	function ac_party_types_dropdown( $flag_blank_select = true )
	{
		$dropdown = IQB_AC_PARTY_TYPES;

		if($flag_blank_select)
		{
			$dropdown = IQB_BLANK_SELECT + $dropdown;
		}
		return $dropdown;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_voucher_editable'))
{
	/**
	 * Is Voucher Editable?
	 *
	 * Check if a Voucher is Editable?
	 * It is only editable if it
	 * 		- is manual voucher
	 * 		- is within this fiscal year
	 *
	 * @param object $record 	Voucher Record
	 * @param bool $terminate_on_fail Terminate Right Here if not editable.
	 * @return	bool
	 */
	function is_voucher_editable( $record, $terminate_on_fail = TRUE )
	{
		$CI =& get_instance();

		/**
		 * Manual Voucher? Belong to Current Fiscal Year?
		 */
		$__flag_editable = $record->flag_internal == IQB_FLAG_OFF && $record->flag_complete == IQB_FLAG_ON && $record->fiscal_yr_id == $CI->current_fiscal_year->id;

		// Terminate on Exit?
		if( $__flag_editable === FALSE && $terminate_on_fail == TRUE)
		{
			$CI->dx_auth->deny_access();
			exit(1);
		}

		return $__flag_editable;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('voucher_complete_flag_text'))
{
	/**
	 * Voucher Complete Flag Text
	 *
	 * @param integer $flag 	Voucher Complete Flag
	 * @param bool $plain_text Return as HTML formatted or plain text
	 * @return	bool
	 */
	function voucher_complete_flag_text( $flag, $plain_text = FALSE )
	{
		$title = $flag == IQB_FLAG_ON ? 'Complete' : 'Incomplete';
		if($plain_text)
		{
			return $title;
		}

		if( $flag == IQB_FLAG_ON )
		{
			$css = 'fa-check text-green';
		}
		else
		{
			$css = 'fa-exclamation-triangle text-muted';
		}
		return '<i class="fa '.$css.'" data-toggle="tooltip" title="'.$title.'"></i>';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('invoice_complete_flag_text'))
{
	/**
	 * Invoice Complete Flag Text
	 *
	 * @param integer $flag 	Invoice Complete Flag
	 * @param bool $plain_text Return as HTML formatted or plain text
	 * @return	bool
	 */
	function invoice_complete_flag_text( $flag, $plain_text = FALSE )
	{
		$title = $flag == IQB_FLAG_ON ? 'Complete' : 'Incomplete';
		if($plain_text)
		{
			return $title;
		}

		if( $flag == IQB_FLAG_ON )
		{
			$css = 'fa-check text-green';
		}
		else
		{
			$css = 'fa-exclamation-triangle text-muted';
		}
		return '<i class="fa '.$css.'" data-toggle="tooltip" title="'.$title.'"></i>';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('ac_account_group_path_formatted'))
{
	/**
	 * Get Account Group Path Formatted
	 *
	 * @param array $acg_path
	 * @param string $account_name
	 * @param string $path_separator
	 * @return	string
	 */
	function ac_account_group_path_formatted( $acg_path, $account_name = '', $path_separator = 'html'  )
	{
		$group_path = [];
		if( count($acg_path) >= 2 )
		{
			// array_shift($acg_path); // Remove "Chart of Account"
			foreach($acg_path as $path)
			{
				$group_path[]=$path->name;
			}
		}
		else
		{
			$group_path[] = $acg_path[0]->name;
		}

		// If account name is supplied, append it too
		if($account_name)
		{
			$group_path[] = '<strong>' . $account_name . '</strong>';
		}

		// Path Seperator
		// Options: html|regular
		if($path_separator == 'html')
		{
			$seperator = '<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>';
		}
		else{
			$seperator = ' &gt; ';
		}


		return implode($seperator, $group_path);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('number_to_words'))
{
	/**
	 * Convert number to words
	 *
	 * @param numeric $$number
	 * @return	string
	 */
	function number_to_words( $number  )
	{
		$hyphen      = '-';
	    $conjunction = ' and ';
	    $separator   = ', ';
	    $negative    = 'negative ';
	    $decimal     = ' point ';
	    $dictionary  = array(
	        0                   => 'zero',
	        1                   => 'one',
	        2                   => 'two',
	        3                   => 'three',
	        4                   => 'four',
	        5                   => 'five',
	        6                   => 'six',
	        7                   => 'seven',
	        8                   => 'eight',
	        9                   => 'nine',
	        10                  => 'ten',
	        11                  => 'eleven',
	        12                  => 'twelve',
	        13                  => 'thirteen',
	        14                  => 'fourteen',
	        15                  => 'fifteen',
	        16                  => 'sixteen',
	        17                  => 'seventeen',
	        18                  => 'eighteen',
	        19                  => 'nineteen',
	        20                  => 'twenty',
	        30                  => 'thirty',
	        40                  => 'fourty',
	        50                  => 'fifty',
	        60                  => 'sixty',
	        70                  => 'seventy',
	        80                  => 'eighty',
	        90                  => 'ninety',
	        100                 => 'hundred',
	        1000                => 'thousand',
	        1000000             => 'million',
	        1000000000          => 'billion',
	        1000000000000       => 'trillion',
	        1000000000000000    => 'quadrillion',
	        1000000000000000000 => 'quintillion'
	    );

	    if (!is_numeric($number)) {
	        return false;
	    }

	    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
	        // overflow
	        trigger_error(
	            'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
	            E_USER_WARNING
	        );
	        return false;
	    }

	    if ($number < 0) {
	        return $negative . number_to_words(abs($number));
	    }

	    $string = $fraction = null;

	    if (strpos($number, '.') !== false) {
	        list($number, $fraction) = explode('.', $number);
	    }

	    switch (true) {
	        case $number < 21:
	            $string = $dictionary[$number];
	            break;
	        case $number < 100:
	            $tens   = ((int) ($number / 10)) * 10;
	            $units  = $number % 10;
	            $string = $dictionary[$tens];
	            if ($units) {
	                $string .= $hyphen . $dictionary[$units];
	            }
	            break;
	        case $number < 1000:
	            $hundreds  = $number / 100;
	            $remainder = $number % 100;
	            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	            if ($remainder) {
	                $string .= $conjunction . number_to_words($remainder);
	            }
	            break;
	        default:
	            $baseUnit = pow(1000, floor(log($number, 1000)));
	            $numBaseUnits = (int) ($number / $baseUnit);
	            $remainder = $number % $baseUnit;
	            $string = number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
	            if ($remainder) {
	                $string .= $remainder < 100 ? $conjunction : $separator;
	                $string .= number_to_words($remainder);
	            }
	            break;
	    }

	    if (null !== $fraction && is_numeric($fraction)) {
	        $string .= $decimal;
	        $words = array();
	        foreach (str_split((string) $fraction) as $number) {
	            $words[] = $dictionary[$number];
	        }
	        $string .= implode(' ', $words);
	    }

	    return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_INVOICE__pdf'))
{
    /**
     * Save, Print or Download Invoice.
     *
     *
     * Filename: invoice-<invoice_code>.pdf
     *
     * @param array $data       ['record' => xxx, 'rows' => []]
     * @param string $action    [save|print|download]
     * @return  void
     */
    function _INVOICE__pdf( $data, $action )
    {
        if( !in_array($action, ['save', 'print', 'download']) )
        {
            throw new Exception("Exception [Helper: account_helper][Method: _INVOICE__pdf()]: Invalid Action.");
        }

        $CI =& get_instance();

        /**
         * Extract Invoice Record and Invoice Rows
         */
        $record    = $data['record'];
        $rows      = $data['rows'];

        $CI->load->library('pdf');
        $mpdf = $CI->pdf->load();
        $mpdf->SetMargins(10, 5, 10, 5);
        $mpdf->margin_header = 5;
        $mpdf->margin_footer = 5;
        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle("Policy Invoice - {$record->invoice_code}");
        $mpdf->SetAuthor($CI->settings->orgn_name_en);

        if( $record->flag_printed == IQB_FLAG_ON )
        {
            $mpdf->SetWatermarkText( 'INVOICE COPY - ' . $CI->settings->orgn_name_en );
        }

        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->SetDisplayMode('fullpage');

        $html = $CI->load->view( 'accounting/invoices/print/invoice', $data, TRUE);
        $mpdf->WriteHTML($html);
        $filename =  "invoice-{$record->invoice_code}.pdf";
        if( $action === 'save' )
        {
            $save_full_path = rtrim(INSQUBE_DATA_PATH, '/') . '/invoices/' . $filename;
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


