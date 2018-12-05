<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vehicle Registration Prefix:  Data List
*/
?>
<table class="table table-hover" >
	<thead>
		<tr>
			<th>ID</th>
			<th>Name (EN)</th>
			<th>Name (NP)</th>
			<th>Type</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-vehicle_reg_prefix">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('setup/vehicle_reg_prefix/_rows');
		?>
	</tbody>
</table>