<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Users:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Role</th>
			<th>Email</th>
			<th>Department</th>
			<th>Branch</th>
			<th>Fullname</th>
			<th>Banned</th>
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