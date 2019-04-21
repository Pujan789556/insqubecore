<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Compulsory Cession Distribution View
*/
?>
<div class="box box-success">
	<div class="box-body" id="ri-distribution-data">
		<?php
		/**
		 * Load distribution data
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_distribution_data');
		?>
	</div>
</div>