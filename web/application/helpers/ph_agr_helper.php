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

// ------------------------------------------------------------------------

if ( ! function_exists('_OBJ_AGR_tariff_by_type'))
{
    /**
     * Get Tariff for supplied sub portfolio and agro category
     *
     * @param int $portfolio_id  Portfolio ID
     * @param int $bs_agro_category_id  Crop Category ID
     * @return  Object
     */
    function _OBJ_AGR_tariff_by_type( $portfolio_id, $bs_agro_category_id )
    {
        $portfolio_id = (int)$portfolio_id;
        load_portfolio_helper($portfolio_id);

        switch($portfolio_id)
        {
            case IQB_SUB_PORTFOLIO_AGR_CROP_ID:
                $tariff = _OBJ_AGR_CROP_tariff_by_type( $bs_agro_category_id );
                break;

            case IQB_SUB_PORTFOLIO_AGR_CATTLE_ID:
                $tariff = _OBJ_AGR_CATTLE_tariff_by_type( $bs_agro_category_id );
                break;

            case IQB_SUB_PORTFOLIO_AGR_POULTRY_ID:
                $tariff = _OBJ_AGR_POULTRY_tariff_by_type( $bs_agro_category_id );
                break;

            case IQB_SUB_PORTFOLIO_AGR_FISH_ID:
                $tariff = _OBJ_AGR_FISH_tariff_by_type( $bs_agro_category_id );
                break;

            case IQB_SUB_PORTFOLIO_AGR_BEE_ID:
                $tariff = _OBJ_AGR_BEE_tariff_by_type( $bs_agro_category_id );
                break;

            default:
                throw new Exception("Exception [Helper: ph_agr_helper][Method: _OBJ_AGR_tariff_by_type()]: No portfolio found!");
                break;
        }

        return $tariff;
    }
}
