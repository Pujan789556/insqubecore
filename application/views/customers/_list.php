<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Customer Code</th>
		<th>Full Name</th>
		<th>Type</th>
		<th>Profession/Expertise</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('customers/_rows');
	?>
</table>