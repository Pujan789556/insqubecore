<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Installments:  Data List Rows
*/

/**
 * Load Rows from View
 */
foreach($records as $record)
{
	$this->load->view('policy_installments/_single_row', ['record' => $record, 'policy_record' => $policy_record]);
}
?>