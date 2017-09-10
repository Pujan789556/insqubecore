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