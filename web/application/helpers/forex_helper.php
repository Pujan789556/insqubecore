<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Forex Helper Functions
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
     * Get Base Currency Dropdown.
     *
     * @param bool $flag_blank_select
     * @return array
     */
    function dropdown_base_currency( bool $flag_blank_select = true )
    {
        $dropdown = [
            'INR' => 'INR',
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'CHF' => 'CHF',
            'AUD' => 'AUD',
            'CAD' => 'CAD',
            'SGD' => 'SGD',
            'JPY' => 'JPY',
            'CNY' => 'CNY',
            'SAR' => 'SAR',
            'QAR' => 'QAR',
            'THB' => 'THB',
            'AED' => 'AED',
            'MYR' => 'MYR',
            'KRW' => 'KRW',
            'SEK' => 'SEK',
            'DKK' => 'DKK',
            'HKD' => 'HKD',
            'KWD' => 'KWD',
            'BHD' => 'BHD'
        ];

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_forex_rate_by_base_currency'))
{
    /**
     * Get Exchange Rate Data for Given Base Currency for Specific date
     *
     * @param string $date
     * @param string $currency
     * @return object
     */
    function get_forex_rate_by_base_currency( string $date, string $currency )
    {
        $CI =& get_instance();

        $CI->load->model('forex_model');

        $row            = $CI->forex_model->get_by_date($date);
        $exchange_rates = json_decode($row->exchange_rates ?? '[]'); // If no data, we should have an empty array
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

if ( ! function_exists('forex_conversion'))
{
    /**
     * Convert the given amount on Base Currency into Target Currency
     * @param string $date
     * @param string $currency
     * @param float $amount
     * @return decimal
     */
    function forex_conversion(string $date, string $currency,  float $amount )
    {
        $forex  = get_forex_rate_by_base_currency($date, $currency);

        if( !$forex )
        {
            throw new Exception("Exception [Helper: forex_helper][Method: forex_conversion()]: No Forex data found for the date ({$date}).");
        }
        else
        {
            $amount = ( $forex->TargetSell / $forex->BaseValue ) * $amount;
        }
        return $amount;
    }
}

// ------------------------------------------------------------------------

