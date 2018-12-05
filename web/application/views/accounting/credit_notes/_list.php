<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Credit Notes:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Reference</th>
			<th>Branch</th>
			<th>Date</th>
			<th>Status</th>
			<th>Paid</th>
			<th>Printed</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody id="search-result-credit_note">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('accounting/credit_notes/_rows');
		?>
	</tbody>
</table>