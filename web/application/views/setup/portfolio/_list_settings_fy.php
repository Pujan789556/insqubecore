<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Settings: Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>Portfolio</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view($this->data['_view_base'] . '/_single_row_settings_fy', compact('record'));
	}
	?>
</table>