<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI Transactions:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif;?>
			<th>Policy Code</th>
			<th>Endorsement Type</th>
			<th>Treaty Type</th>
			<th>Distribution Type</th>
			<th>FAC</th>
			<th>Action</th>
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