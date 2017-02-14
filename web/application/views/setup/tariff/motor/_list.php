<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Settings: Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>Fiscal Year</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/tariff/motor/_single_row', compact('record'));
	}
	?>
</table>