<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Object Popover
*/

$data = ['record' => $record, 'ref' => $ref ?? ''];

/**
 * MOTOR PORTFOLIOS
 * ----------------
 * For all type of motor portfolios, we have same snippet
 */
if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
{
	$this->load->view('objects/snippets/_popup_motor', $data);
}

/**
 * FIRE PORTFOLIOS
 * ----------------
 */
else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
{
	$this->load->view('objects/snippets/_popup_fire', $data);
}

/**
 * MARINE PORTFOLIOS
 * ----------------
 */
else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
{
	$this->load->view('objects/snippets/_popup_marine', $data);
}

/**
 * ENGINEERING - BOILER EXPLOSION
 * ------------------------------
 */
else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
{
    $this->load->view('objects/snippets/_popup_eng_bl', $data);
}

/**
 * ENGINEERING - CONTRACTOR PLANT & MACHINARY
 * ------------------------------------------
 */
else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
{
    $this->load->view('objects/snippets/_popup_eng_cpm', $data);
}

/**
 * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
 * ---------------------------------------------
 */
else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
{
    $this->load->view('objects/snippets/_popup_eng_eei', $data);
}

/**
 * ENGINEERING - MACHINE BREAKDOWN
 * ---------------------------------------------
 */
else if( $record->portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
{
    $this->load->view('objects/snippets/_popup_eng_mb', $data);
}

