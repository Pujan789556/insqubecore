<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Headings:  Data List
*/
?>
<table class="table table-hover" id="search-result-ac-heading">
	<tr>
		<th>ID</th>
		<th>Heading Group</th>
		<th>Heading</th>
		<th>Account Number</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/ac_headings/_rows');
	?>
</table>