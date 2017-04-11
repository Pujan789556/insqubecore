<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pool Treaty : RI Distribution View
*/
?>
<div class="box box-success">
	<div class="box-header with-border">
		<h3 class="box-title">Pool Distribution</h3>
	</div>
	<div class="box-body" id="pool-distribution-data">
		<?php
		/**
		 * Load distribution data
		 */
		$this->load->view('setup/ri/pools/snippets/_ri_distribution_data');
		?>
	</div>
</div>