<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Treaty Types:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
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