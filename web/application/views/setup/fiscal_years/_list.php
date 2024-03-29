<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Data List
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Code (NP)</th>
		<th>Code (EN)</th>
		<th>Starts at(NP)</th>
		<th>Ends at(NP)</th>
		<th>Starts at (EN)</th>
		<th>Ends at (EN)</th>
	</tr>					
	<?php
	/**
	 * Load Rows from View
	 */ 
	foreach($records as $record)
	{
		$this->load->view($this->data['_view_base'] . '/_single_row', compact('record'));
	}
	?>				
</table>