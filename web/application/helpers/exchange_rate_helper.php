<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Exchange Rate Helper Functions
 *
 * 	This will have general helper functions required
 *
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

// ------------------------------------------------------------------------

if ( ! function_exists('dropdown_base_currency'))
{
    /**
     * Get Base Currency Dropdown for specific date.
     *
     * @param string $date
     * @param bool $flag_blank_select
     * @return array
     */
    function dropdown_base_currency( string $date, bool $flag_blank_select = true )
    {
        $CI =& get_instance();

        $CI->load->model('exchange_rate_model');

        $row            = $CI->exchange_rate_model->get_by_date($date);
        $exchange_rates = json_decode($row->exchange_rates ?? []);
        $dropdown       = [];

        foreach( $exchange_rates  as $r )
        {
            $dropdown[$r->BaseCurrency] = $r->BaseCurrency;
        }

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_exchange_rate_by_base_currency'))
{
    /**
     * Get Exchange Rate Data for Given Base Currency for Specific date
     *
     * @param string $date
     * @param string $currency
     * @return object
     */
    function get_exchange_rate_by_base_currency( string $date, string $currency )
    {
        $CI =& get_instance();

        $CI->load->model('exchange_rate_model');

        $row            = $CI->exchange_rate_model->get_by_date($date);
        $exchange_rates = json_decode($row->exchange_rates ?? []);
        $single_rate    = NULL;

        $currency = strtoupper($currency);
        foreach( $exchange_rates  as $r )
        {
            if($currency === strtoupper($r->BaseCurrency)  )
            {
                $single_rate = $r;
                break;
            }
        }
        return $single_rate;
    }
}


// ------------------------------------------------------------------------

