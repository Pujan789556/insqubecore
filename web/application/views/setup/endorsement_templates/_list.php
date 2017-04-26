<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Template:  Data List
*/
?>
<table class="table table-hover" id="search-result-endorsement_templates">
	<tr>
		<th>ID</th>
		<th>Portfolio</th>
		<th>Type</th>
		<th>Actions</th>
	</tr>
	<?php
	/**
	 * Load Rows & Next Link (if any)
	 */
	$this->load->view('setup/endorsement_templates/_rows');
	?>
</table>