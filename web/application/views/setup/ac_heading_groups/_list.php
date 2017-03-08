<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Heading Groups:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Range Min</th>
		<th>Range Max</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/ac_heading_groups/_single_row', compact('record'));
	}
	?>
</table>