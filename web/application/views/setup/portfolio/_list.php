<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name (EN)</th>
		<th>Name (NP)</th>
		<th>Code</th>
		<th>Parent Portfolio</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/portfolio/_single_row', compact('record'));
	}
	?>
</table>