<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - MISC (Banker's Blanket) - List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>Fiscal Year</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/tariff/misc_bb/_single_row', compact('record'));
	}
	?>
</table>