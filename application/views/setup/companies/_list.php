<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>UD Code</th>
		<th>Pan No</th>
		<th>Type</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>	
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */ 
	$this->load->view('setup/companies/_rows');	
	?>			
</table>