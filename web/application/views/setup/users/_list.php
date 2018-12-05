<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Users:  Data List
*/
?>
<table class="table table-hover" id="search-result-user">
	<tr>
		<th>ID</th>
		<th>Username</th>
		<th>Role</th>
		<th>Email</th>
		<th>Department</th>
		<th>Branch</th>
		<th>Fullname</th>
		<th>Banned</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/users/_rows');
	?>
</table>