<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Duties & Tax:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Rate</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/ac_duties_and_tax/_single_row', compact('record'));
	}
	?>
</table>