<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Invoices:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif;?>
			<th>Code</th>
			<th>Reference</th>
			<th>Branch</th>
			<th>Date</th>
			<th>Status</th>
			<th>Paid</th>
			<th>Invoice Printed</th>
			<th>Receipt Printed</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody id="search-result-invoice">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('accounting/invoices/_rows');
		?>
	</tbody>
</table>