<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vouchers:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif;?>
			<th>Code</th>
			<th>Branch</th>
			<th>Type</th>
			<th>Date</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody id="search-result-voucher">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('accounting/vouchers/_rows');
		?>
	</tbody>
</table>