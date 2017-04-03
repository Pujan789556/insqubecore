<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : Details View
*/
?>
<div class="row">
	<div class="col-md-4">
		<?php
		/**
		 * Basic Overview
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_basic');

		/**
		 * Brokers Overview
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_brokers');
		?>
	</div>
	<div class="col-md-8">
		<?php
		/**
		 * RI Distribution
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_distribution',['record' => $record, 'treaty_distribution' => $treaty_distribution]);
		?>

		<?php
		/**
		 * RI Tax & Commission
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_tnc',['record' => $record]);
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php
		/**
		 * RI Portfolios
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_portfolios',['record' => $record, 'portfolios' => $portfolios]);
		?>
	</div>
</div>