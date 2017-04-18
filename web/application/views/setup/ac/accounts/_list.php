<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Accounts:  Data List
*/
?>
<table class="table table-hover" id="search-result-ac-account">
	<tr>
		<th>ID</th>
		<th>Account Group</th>
		<th>Name</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/ac/accounts/_rows');
	?>
</table>