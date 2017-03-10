<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Accounts:  Data List
*/
?>
<table class="table table-hover" id="search-result-ac-account">
	<tr>
		<th>ID</th>
		<th>Account Number</th>
		<th>Account Group</th>
		<th>Parent</th>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/ac_accounts/_rows');
	?>
</table>