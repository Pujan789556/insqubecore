<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties:  Data List
*/
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Party Name</th>
			<th>Contact</th>
			<th>Type</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Actions</th>
			<?php endif?>
		</tr>
	</thead>
	<tbody id="search-result-ac_party">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('accounting/parties/_rows');
		?>
	</tbody>
</table>