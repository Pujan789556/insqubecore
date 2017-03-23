<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account - Transaction Type References:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Code</th>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/ac_ref_transaction_types/_single_row', compact('record'));
	}
	?>
</table>