<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Beema Samiti Report Setup - Heading Types:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Name (EN)</th>
		<th>Name (NP)</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/bsrs_heading_types/_single_row', compact('record'));
	}
	?>
</table>