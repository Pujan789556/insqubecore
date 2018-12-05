<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* States:  Data List
*/
?>
<table class="table table-hover" >
	<thead>
		<tr>
			<th>ID</th>
			<th>Country</th>
			<th>Code</th>
			<th>Name (EN)</th>
			<th>Name (NP)</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-states">
		<?php
		/**
		 * Load Rows & Next Link (if any)
		 */
		$this->load->view('setup/states/_rows');
		?>
	</tbody>
</table>