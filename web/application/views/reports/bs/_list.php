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
			<th>Type</th>
			<th>Fiscal Year</th>
			<th>Quarter/Month</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody id="search-result-reports">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('reports/bs/_rows');
		?>
	</tbody>
</table>