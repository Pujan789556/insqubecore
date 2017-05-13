<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agents:  Data List
*/
?>
<table class="table table-hover" >
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
					<th>ID</th>
			<?php endif?>
			<th>Name</th>
			<th>UD Code</th>
			<th>BS Code</th>
			<th>Type</th>
			<th>Active</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Actions</th>
			<?php endif?>
		</tr>
	</thead>
	<tbody id="search-result-agent">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('setup/agents/_rows');
		?>
	</tbody>
</table>