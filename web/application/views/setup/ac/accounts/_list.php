<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Accounts:  Data List
*/
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
?>
<table class="table table-hover">
	<tbody>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Account Group</th>
			<th>Name</th>
			<th>Active</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Actions</th>
			<?php endif?>
		</tr>
	</tbody>
	<tbody id="search-result-ac-account">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('setup/ac/accounts/_rows');
		?>
	</tbody>
</table>