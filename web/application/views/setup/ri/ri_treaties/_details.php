<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : Details View
*/
?>
<div class="row">
	<div class="col-md-5">
		<?php
		/**
		 * Basic Overview
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_basic');

		/**
		 * Brokers Overview
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_brokers');

		/**
		 * Commission Scale
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_commission_scale');
		?>
	</div>
	<div class="col-md-7">
		<?php
		/**
		 * RI Distribution
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_distribution',['record' => $record, 'treaty_distribution' => $treaty_distribution]);
		?>

		<?php
		/**
		 * RI Tax & Commission
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_tnc',['record' => $record]);
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php
		/**
		 * RI Portfolios
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_portfolios',['record' => $record, 'portfolios' => $portfolios]);
		?>
	</div>
</div>