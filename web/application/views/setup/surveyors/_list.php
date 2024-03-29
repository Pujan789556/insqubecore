<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyors:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Name</th>
			<th>Profile</th>
			<th>Type</th>
			<th>VAT Registered?</th>
			<th>Active</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody id="<?php echo $DOM_RowBoxId ?>">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view($this->data['_view_base'] . '/_rows');
		?>
	</tbody>

</table>