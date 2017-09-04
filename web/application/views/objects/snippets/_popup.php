<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Object Popover
*/

/**
 * MOTOR PORTFOLIOS
 * ----------------
 * For all type of motor portfolios, we have same snippet
 */
if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
{
	$this->load->view('objects/snippets/_popup_motor', ['record' => $record]);
}

/**
 * FIRE PORTFOLIOS
 * ----------------
 */
else if( in_array($record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE)) )
{
	$this->load->view('objects/snippets/_popup_fire', ['record' => $record]);
}