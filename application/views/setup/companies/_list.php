<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company:  Data List
*/
?>
<table class="table table-hover" id="search-result-company">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Pan No</th>
		<th>Type</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/companies/_rows');
	?>
</table>