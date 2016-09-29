<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Username</th>
		<th>Role</th>
		<th>Branch</th>
		<th>Fullname</th>
		<th>Actions</th>
	</tr>	
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */ 
	$this->load->view('setup/users/_rows');	
	?>			
</table>