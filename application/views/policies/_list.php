<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy:  Data List
*/
?>
<table class="table table-hover" id="search-result-policy">
	<tr>
		<?php if( $this->dx_auth->is_admin() ): ?>
			<th>ID</th>
		<?php endif?>
		<th>Code</th>
		<th>Type</th>
		<th>Dates</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('policies/_rows');
	?>
</table>