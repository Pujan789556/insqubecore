<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - Agriculture - List by Fiscal year
*/
?>
<table class="table table-hover" id="live-searchable">
	<tr>
		<th>Fiscal Year</th>
		<th>Portfolio</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows from View
	 */
	foreach($records as $record)
	{
		$this->load->view('setup/tariff/agriculture/_single_row_by_fiscal_year', compact('record'));
	}
	?>
</table>