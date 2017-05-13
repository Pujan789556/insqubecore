<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company:  Data List
*/
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Name</th>
			<th>Pan No</th>
			<th>Type</th>
			<th>Active</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Actions</th>
			<?php endif?>
		</tr>
	</thead>

	<tbody id="search-result-company">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('setup/companies/_rows');
		?>
	</tbody>
</table>