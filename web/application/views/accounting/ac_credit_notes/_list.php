<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Credit Notes:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Reference</th>
			<th>Branch</th>
			<th>Date</th>
			<th>Refunded?</th>
			<th>Printed</th>
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