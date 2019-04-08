<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - Property - List by Fiscal year
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>ID</th>
		<th>Fiscal Year</th>
		<th>Code</th>
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
		$this->load->view('setup/tariff/property/_single_row_by_fiscal_year', compact('record'));
	}
	?>
</table>