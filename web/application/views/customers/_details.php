<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Details View
*/
?>
<div class="row">
	<div class="col-md-3">

		<?php
		/**
		 * Profile Card
		 */
		$this->load->view('customers/snippets/_profile_card', ['record' => $record]);

		/**
		 * Contact Widget
		 */
		echo address_widget($address_record);
		?>
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs" id="customer-tabs">
				<li role="presentation" class="active">
					<a href="#tab-customer-overview" aria-controls="tab-customer-overview" role="tab" data-toggle="tab">Overview</a>
				</li>
				<li role="presentation">
					<a href="#tab-policies"
						data-url="<?php echo site_url('policies/by_customer/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-policies"
						data-method="html"
						aria-controls="tab-policies"
						role="tab"
						data-toggle="tab">Policies</a>
				</li>
				<li role="presentation">
					<a href="#tab-objects"
						data-url="<?php echo site_url('objects/by_customer/'. $record->id)?>"
						data-load-method="get"
						data-box="#tab-objects"
						data-method="html"
						aria-controls="tab-objects"
						role="tab"
						data-toggle="tab">Objects</a>
				</li>
				<li><a href="#targets" data-toggle="tab">Invoices</a></li>
				<li><a href="#settings" data-toggle="tab">Documents</a></li>
				<li><a href="#settings" data-toggle="tab">Claims</a></li>
				<li><a href="#settings" data-toggle="tab">Reports</a></li>
			</ul>
			<div class="tab-content">
				<!-- Tab Pane: Overview -->
				<div class="active tab-pane" id="tab-customer-overview">
				</div>

				<!-- Tab Pane: Policies -->
				<div class="tab-pane" id="tab-policies"></div>

				<!-- Tab Pane: Objects -->
				<div class="tab-pane" id="tab-objects"></div>

			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>