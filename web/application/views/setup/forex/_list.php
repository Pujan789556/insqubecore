<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Forex:  Data List
*/
?>
<table class="table table-hover" id="<?php echo $DOM_RowBoxId ?>">
	<thead>
		<tr>
			<th>ID</th>
			<th>Exchange Date</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="<?php echo $DOM_RowBoxId ?>">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view( $this->data['_view_base'] . '/_rows' );
		?>
	</tbody>
</table>