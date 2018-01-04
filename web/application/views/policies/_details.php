<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs" id="policy-tabs">
				<li role="presentation" class="active">
					<a href="#tab-policy-overview" aria-controls="tab-policy-overview" role="tab" data-toggle="tab">Overview</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-transactions"
						data-url="<?php echo site_url('policy_transactions/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-transactions"
						data-method="html"
						aria-controls="tab-policy-transactions"
						role="tab"
						data-toggle="tab">Transactions</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-installments"
						data-url="<?php echo site_url('policy_installments/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-installments"
						data-method="html"
						aria-controls="tab-policy-installments"
						role="tab"
						data-toggle="tab">Installments</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-vouchers"
						data-url="<?php echo site_url('ac_vouchers/by_policy/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-vouchers"
						data-method="html"
						aria-controls="tab-policy-vouchers"
						role="tab"
						data-toggle="tab">Vouchers</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-invoices"
						data-url="<?php echo site_url('ac_invoices/by_policy/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-invoices"
						data-method="html"
						aria-controls="tab-policy-invoices"
						role="tab"
						data-toggle="tab">Invoices</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-payments" aria-controls="tab-policy-payments" role="tab"  data-toggle="tab">Payments</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-claims" aria-controls="tab-policy-claims" role="tab" data-toggle="tab">Claim</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-docs" aria-controls="tab-policy-docs" role="tab"  data-toggle="tab">Documents</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-logs" aria-controls="tab-policy-logs" role="tab" data-toggle="tab">Logs</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-policy-overview">
					<?php
					/**
					 * Tab : Overview
					 */
					$this->load->view('policies/tabs/_tab_overview');
					?>
				</div>

				<div class="tab-pane" id="tab-policy-transactions">

				</div>

				<div class="tab-pane" id="tab-policy-installments">

				</div>

				<div class="tab-pane" id="tab-policy-vouchers">

				</div>

				<div class="tab-pane" id="tab-policy-invoices">

				</div>

				<div class="tab-pane" id="tab-policy-payments">

				</div>

				<div class="tab-pane" id="tab-policy-claims">

				</div>

				<div class="tab-pane" id="tab-policy-docs">

				</div>

				<div class="tab-pane" id="tab-policy-logs">

				</div>
			</div>
		</div>
	</div>
</div>