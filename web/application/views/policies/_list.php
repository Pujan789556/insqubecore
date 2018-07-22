<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Code</th>
			<th>Customer</th>
			<th>Portfolio</th>
			<th>Policy Dates (From - To)</th>
			<th>Created</th>
			<th>Last Update</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody id="search-result-policy">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('policies/_rows');
		?>
	</tbody>
</table>