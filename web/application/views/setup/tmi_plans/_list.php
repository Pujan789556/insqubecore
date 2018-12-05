<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Code</th>
		<th>Name</th>
		<th>Parent Plan</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/' . $this->router->class . '/_single_row', compact('record'));
	}
	?>
</table>