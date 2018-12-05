<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pool : Details View
*/
?>
<div class="row">
	<div class="col-md-5">
		<?php
		/**
		 * Basic Overview
		 */
		$this->load->view('setup/ri/pools/snippets/_ri_basic');
		?>
	</div>
	<div class="col-md-7">
		<?php
		/**
		 * RI Distribution
		 */
		$this->load->view('setup/ri/pools/snippets/_ri_distribution',['record' => $record, 'treaty_distribution' => $treaty_distribution]);
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php
		/**
		 * RI Portfolios
		 */
		$this->load->view('setup/ri/pools/snippets/_ri_portfolios',['record' => $record, 'portfolios' => $portfolios]);
		?>
	</div>
</div>