<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pool Treaty : Pool Portfolios View
*/
?>
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">Portfolios</h3>
	</div>
	<div class="box-body" style="overflow-x: scroll;" id="pool-portfolio-data">
		<?php
		/**
		 * Load portfolio data
		 */
		$this->load->view('setup/ri/pools/snippets/_ri_portfolio_data');
		?>
	</div>
</div>