<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer:  Data List
*/
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Customer Name</th>
			<th>Mobile Identity</th>
			<th>Contact</th>
			<th>Type</th>
			<th>Profession/Expertise</th>
			<th>Locked?</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Actions</th>
			<?php endif?>
		</tr>
	</thead>
	<tbody id="search-result-customer">
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('customers/_rows');
	?>
	</tbody>
</table>