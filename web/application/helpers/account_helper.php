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



