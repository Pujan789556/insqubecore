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
					<!-- <a href="#tab-policy-overview" aria-controls="tab-policy-overview" role="tab" data-toggle="tab">Overview</a> -->

					<a href="#tab-policy-overview"
						data-url="<?php echo site_url('policies/details/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-overview"
						data-method="html"
						aria-controls="tab-policy-overview"
						role="tab"
						data-toggle="tab">Overview</a>

				</li>
				<li role="presentation">
					<a href="#tab-endorsements"
						data-url="<?php echo site_url('endorsements/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-endorsements"
						data-method="html"
						aria-controls="tab-endorsements"
						role="tab"
						data-toggle="tab">Endorsements</a>
				</li>
				<li role="presentation">
					<a href="#tab-policy-installments"
						data-url="<?php echo site_url('policy_installments/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-installments"
						data-method="html"
						aria-controls="tab-policy-installments"
						role="tab"
						data-toggle="tab">Installments/Refunds</a>
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
					<a href="#tab-policy-credit_notes"
						data-url="<?php echo site_url('ac_credit_notes/by_policy/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-credit_notes"
						data-method="html"
						aria-controls="tab-policy-credit_notes"
						role="tab"
						data-toggle="tab">Credit Notes</a>
				</li>

				<li role="presentation">
					<a href="#tab-policy-ri_transactions"
						data-url="<?php echo site_url('ri_transactions/by_policy/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-ri_transactions"
						data-method="html"
						aria-controls="tab-policy-ri_transactions"
						role="tab"
						data-toggle="tab">RI Transactions</a>
				</li>

				<li role="presentation">
					<a href="#tab-policy-claims"
						data-url="<?php echo site_url('claims/by_policy/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policy-claims"
						data-method="html"
						aria-controls="tab-policy-claims"
						role="tab"
						data-toggle="tab">Claims</a>
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

				<div class="tab-pane" id="tab-endorsements"></div>

				<div class="tab-pane" id="tab-policy-installments"></div>

				<div class="tab-pane" id="tab-policy-vouchers"></div>

				<div class="tab-pane" id="tab-policy-invoices"></div>

				<div class="tab-pane" id="tab-policy-credit_notes"></div>

				<div class="tab-pane" id="tab-policy-ri_transactions"></div>

				<div class="tab-pane" id="tab-policy-claims"></div>

				<div class="tab-pane" id="tab-policy-docs"></div>

				<div class="tab-pane" id="tab-policy-logs"></div>
			</div>
		</div>
	</div>
</div>