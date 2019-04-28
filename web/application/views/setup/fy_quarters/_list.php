<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year Quarters:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Fiscal Year</th>
		<th>Starts at (EN)</th>
		<th>Ends at (EN)</th>
		<th>Quarter</th>
		<th>Action</th>
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