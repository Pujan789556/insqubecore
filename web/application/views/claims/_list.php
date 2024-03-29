<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claims:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif;?>
			<th>Claim Code</th>
			<th>Policy</th>
			<th>Date of Loss</th>
			<th>Date of Intimation</th>
			<th>Status</th>
			<th>Paid</th>
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