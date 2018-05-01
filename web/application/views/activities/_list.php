<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Activity</th>
		<th>Module</th>
		<th>Module ID</th>
		<th>Date</th>
		<th>User ID</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('activities/_rows');
	?>
</table>
