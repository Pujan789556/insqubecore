<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Installments:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Endorsement ID</th>
			<th>Installment Date</th>
			<th>First Installment?</th>
			<th>Percent</th>
			<th>Premium (Rs.)</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-policy_installments">
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('policy_installments/_rows');
	?>
	</tbody>
</table>