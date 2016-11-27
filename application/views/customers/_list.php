<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<?php if( $this->dx_auth->is_admin() ): ?>
			<th>ID</th>
		<?php endif?>
		<th>Customer Name</th>
		<th>Contact</th>
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