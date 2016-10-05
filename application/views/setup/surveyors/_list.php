<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyors:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Type</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>	
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */ 
	$this->load->view('setup/surveyors/_rows');	
	?>			
</table>