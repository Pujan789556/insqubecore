<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Object Popover
*/

$data           = ['record' => $record, 'ref' => $ref ?? ''];
$portfolio_id   = (int)$record->portfolio_id;

/**
 * AGRICULTURE - CROP SUB-PORTFOLIO
 * ---------------------------------
 */
if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
{
    $this->load->view('objects/snippets/_popup_agr_crop', $data);
}

/**
 * AGRICULTURE - CATTLE SUB-PORTFOLIO
 * ---------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
{
    $this->load->view('objects/snippets/_popup_agr_cattle', $data);
}

/**
 * AGRICULTURE - POULTRY SUB-PORTFOLIO
 * -----------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
{
    $this->load->view('objects/snippets/_popup_agr_poultry', $data);
}

/**
 * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
 * ----------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
{
    $this->load->view('objects/snippets/_popup_agr_fish', $data);
}

/**
 * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
 * -------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
{
    $this->load->view('objects/snippets/_popup_agr_bee', $data);
}

/**
 * MOTOR PORTFOLIOS
 * ----------------
 * For all type of motor portfolios, we have same snippet
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
{
	$this->load->view('objects/snippets/_popup_motor', $data);
}

/**
 * FIRE PORTFOLIOS
 * ----------------
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
{
	$this->load->view('objects/snippets/_popup_fire', $data);
}

/**
 * MARINE PORTFOLIOS
 * ----------------
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
{
	$this->load->view('objects/snippets/_popup_marine', $data);
}

/**
 * ENGINEERING - BOILER EXPLOSION
 * ------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
{
    $this->load->view('objects/snippets/_popup_eng_bl', $data);
}

/**
 * ENGINEERING - CONTRACTOR ALL RISK
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
{
    $this->load->view('objects/snippets/_popup_eng_car', $data);
}

/**
 * ENGINEERING - CONTRACTOR PLANT & MACHINARY
 * ------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
{
    $this->load->view('objects/snippets/_popup_eng_cpm', $data);
}

/**
 * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
{
    $this->load->view('objects/snippets/_popup_eng_eei', $data);
}

/**
 * ENGINEERING - ERECTION ALL RISKS
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
{
    $this->load->view('objects/snippets/_popup_eng_ear', $data);
}

/**
 * ENGINEERING - MACHINE BREAKDOWN
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
{
    $this->load->view('objects/snippets/_popup_eng_mb', $data);
}

