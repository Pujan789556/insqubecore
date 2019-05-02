<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object:  Data List
*/
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
?>
<table class="table table-hover">
	<thead>
		<tr>
			<?php if( $this->dx_auth->is_admin() ): ?>
				<th>ID</th>
			<?php endif?>
			<th>Portfolio</th>
			<th>Customer</th>
			<th>Object Snippet</th>
			<th>Locked?</th>
			<?php if( !$_flag__show_widget_row ):?>
				<th>Preview</th>
				<th>Actions</th>
			<?php endif?>
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