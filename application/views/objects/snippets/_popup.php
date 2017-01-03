<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Object Popover
*/
switch ($record->portfolio_id)
{
	// Motor
	case IQB_MASTER_PORTFOLIO_MOTOR_ID:
		$this->load->view('objects/snippets/_popup_motor', ['record' => $record]);
		break;

	default:
		# code...
		break;
}