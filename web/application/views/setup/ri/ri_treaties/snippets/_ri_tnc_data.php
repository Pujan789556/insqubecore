<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Tax & Commission - Data View
*/

if($record->treaty_type_id == IQB_RI_TREATY_TYPE_EOL)
{
	$this->load->view($this->data['_view_base'] . '/snippets/_ri_tnc_data_eol');
}
else
{
	$this->load->view($this->data['_view_base'] . '/snippets/_ri_tnc_data_qs');
}
?>