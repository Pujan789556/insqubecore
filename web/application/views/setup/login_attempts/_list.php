<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Login Attempts:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>IP Address</th>
		<th>Attempts #</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/login_attempts/_single_row', compact('record'));
	}
	?>
</table>