<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Settings: Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>Fiscal Year</th>
		<th>Ownership</th>
		<th>Portfolio</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view($this->data['_view_base'] . '/motor/_single_row_by_fiscal_year', compact('record'));
	}
	?>
</table>