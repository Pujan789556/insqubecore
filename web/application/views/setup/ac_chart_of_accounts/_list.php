<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Accounts:  Data List
*/
?>
<table class="table table-hover" id="search-result-ac-chart-of-account">
	<tr>
		<th>ID</th>
		<th>Account Group</th>
		<th>Parent</th>
		<th>Account Number</th>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/ac_chart_of_accounts/_rows');
	?>
</table>