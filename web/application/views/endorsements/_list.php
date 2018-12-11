<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Type</th>
			<th>Sold By</th>
			<th>Agent</th>
			<th>RI Approval?</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-endorsements">
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('endorsements/_rows');
	?>
	</tbody>
</table>