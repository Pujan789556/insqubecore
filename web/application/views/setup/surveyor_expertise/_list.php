<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyor Expertise:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/surveyor_expertise/_single_row', compact('record'));
	}
	?>
</table>