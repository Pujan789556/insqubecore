<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extra Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation {



	/**
	 * Initialize Form_Validation class
	 *
	 * @param	array	$rules
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		// Load extra validation Language
		$this->CI->lang->load('my_form_validation');
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Date
	 *
	 * 	Format: yyyy-mm-dd
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_date($str)
	{
		$date_values = explode('-',$str);
		if((sizeof($date_values)!=3) || !checkdate( (int) $date_values[1], (int) $date_values[2], (int) $date_values[0]))
		{
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Time
	 *
	 * 	Format: H:i:s
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_time($str)
	{
		$time_values = explode(':',$str);
		if((sizeof($time_values)!=3) || (int) $time_values[0]>23 || (int) $time_values[1]>59 || (int) $time_values[2]>59)
		{
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Datetme
	 *
	 * 	Format: yyyy-mm-dd hh:mm:ss
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_datetime($str)
	{
		$date_time = explode(' ',$str);
		if(sizeof($date_time)==2)
		{
			$date = $date_time[0];
			$time = $date_time[1];

			return ($this->valid_date($date) && $this->valid_time($time)) ? TRUE : FALSE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Decimal
	 *
	 * If supplied value is numeric, convert this number into a decimal number
	 *
	 * @param	string
	 * @return	string
	 */
	public function prep_decimal($str = '')
	{
		//  Number but not Decimal
		if( is_numeric( $str ) && floor( $str ) == $str )
		{
			$str = number_format($str, 2, '.', '');
		}
		return $str;
	}
}
