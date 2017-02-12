<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branch:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Code</th>
		<th>Actions</th>
	</tr>					
	<?php
	/**
	 * Load Rows from View
	 */ 
	foreach($records as $record)
	{
		$this->load->view('setup/branches/_single_row', compact('record'));
	}
	?>				
</table>