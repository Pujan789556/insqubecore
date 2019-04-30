<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - Agriculture - List
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
		$this->load->view($this->data['_view_base'] . '/agriculture/_single_row', compact('record'));
	}
	?>
</table>