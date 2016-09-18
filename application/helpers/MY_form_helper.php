<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Extra Form Helpers
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola
 * @link		https://twitter.com/ipbastola
 */

// ------------------------------------------------------------------------

if ( ! function_exists('form_email'))
{
	/**
	 * Email Input Field
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_email($data = '', $value = '', $extra = '')
	{
		is_array($data) OR $data = array('name' => $data);
		$data['type'] = 'email';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_url'))
{
	/**
	 * URL Input Field
	 *
	 * @param	mixed
	 * @param	string
	 * @param	mixed
	 * @return	string
	 */
	function form_url($data = '', $value = '', $extra = '')
	{
		is_array($data) OR $data = array('name' => $data);
		$data['type'] = 'url';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_switch'))
{
	/**
	 * On/Off Checkbox Switch
	 * 
	 * attribute ID must be passed on $data param
	 *
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @return	string
	 */
	function form_switch($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		$switch_type = isset($data['switch-type']) ? $data['switch-type'] : 'switch-primary';
		$id = isset($data['id']) ? $data['id'] : '_chkbx_'. mt_rand(1000,999999);

		$output = 	'<div class="switch '.$switch_type.'">' .
						form_checkbox($data, $value, $checked, $extra) .
						'<label class="switch-label" for="'.$id.'"></label>'.
					'</div>';
		return $output;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('field_compulsary_text'))
{
	/**
	 * Get Compulsory Text on Form Label
	 * 
	 * @param bool $flag If true, we return the compulsory text
	 * @return string
	 */
	function field_compulsary_text( $flag = false )
	{
		return $flag ? '<span class="text-red" title="This field is compulsory.">*</span>' : '';
	}
}

// ------------------------------------------------------------------------

