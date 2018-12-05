<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pools :  Data List
*/
?>
<table class="table table-hover" id="search-result-ri_setup_pools">
	<tr>
		<th>ID</th>
		<th>Title</th>
		<th>Fiscal Year</th>
		<th>Treaty Type</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/ri/pools/_rows');
	?>
</table>