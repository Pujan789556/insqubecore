<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Agriculture Portfolio Helper Functions
 *
 * This file contains common helper functions related to all Agriculture sub-portfolios
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_area_unit_dropdown'))
{
    /**
     * Get Area Unit Dropdown
     *
     *
     * @param bool $flag_blank_select   Whether to append blank select
     * @return  bool
     */
    function _OBJ_AGR_area_unit_dropdown( $flag_blank_select = true )
    {
        $dropdown = [
            'dhur'   => 'Dhur (धुर)',
            'khatha' => 'Khatha (कठ्ठा)',
            'bigha'  => 'Bigha (बिघा)',
            'ana'    => 'Aana (आना)',
            'ropani' => 'Ropani (रोपनी)'
        ];

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_category_dropdown'))
{
    /**
     * Beema Samiti Agriculture Category Dropdown
     *
     * @param int $portfolio_id  Portfolio ID
     * @return  array
     */
    function _OBJ_AGR_category_dropdown( $portfolio_id )
    {
        $CI =& get_instance();
        $CI->load->model('bs_agro_category_model');
        $dropdown = $CI->bs_agro_category_model->dropdwon_by_portfolio($portfolio_id);
        if( !$dropdown )
        {
            throw new Exception('Exception [Helper: ph_agr_helper][Method: _OBJ_AGR_category_dropdown()]: Please add the "Beema Samiti Agriculture Category" for this portfolio first.<br><br>Master Setup >> Beema Samiti >> Agriculture Categories');
        }

        return $dropdown;
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_breed_dropdown'))
{
    /**
     * Get Breed dropwon by Beema Samiti Agriculture Category
     *
     * @param int $bs_agro_category_id  Category ID
     * @return  array
     */
    function _OBJ_AGR_breed_dropdown( $bs_agro_category_id )
    {
        $CI =& get_instance();
        $CI->load->model('bs_agro_breed_model');
        $dropdown = $CI->bs_agro_breed_model->dropdown_by_category($bs_agro_category_id);

        return $dropdown;
    }
}