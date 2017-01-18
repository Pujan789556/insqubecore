<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company Branches:  Data List
*/
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Contact Snippet</th>
			<th width="20%">Actions</th>
		</tr>
	</thead>
	<tbody id="search-result-company-branch">
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/company_branches/_rows');
	?>
	</tbody>
</table>