<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agents:  Data List
*/
?>
<table class="table table-hover" id="search-result-agent">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>UD Code</th>
		<th>BS Code</th>
		<th>Type</th>
		<th>Active</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/agents/_rows');
	?>
</table>