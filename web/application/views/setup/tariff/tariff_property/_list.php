<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff Property:  Data List
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
		$this->load->view($this->data['_view_base'] . '/_single_row', compact('record'));
	}
	?>
</table>