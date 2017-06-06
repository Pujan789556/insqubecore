<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Transaction:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Type</th>
			<th>RI Approval?</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-policy_txn">
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('policy_txn/_rows');
	?>
	</tbody>
</table>