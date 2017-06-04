<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-policy-overview" data-toggle="tab">Overview</a></li>
				<li><a href="#timeline" data-toggle="tab">Transactions/Endorsements</a></li>
				<li><a href="#targets" data-toggle="tab">Vouchers</a></li>
				<li><a href="#targets" data-toggle="tab">Invoices</a></li>
				<li><a href="#targets" data-toggle="tab">Payments</a></li>
				<li><a href="#settings" data-toggle="tab">Documents</a></li>
				<li><a href="#settings" data-toggle="tab">Claim</a></li>
				<li><a href="#settings" data-toggle="tab">Logs</a></li>
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
				<!-- /.tab-pane -->
				<div class="tab-pane" id="timeline">

				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="settings">

				</div>
				<!-- /.tab-pane -->
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>