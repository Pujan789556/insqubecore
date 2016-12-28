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
		?>

		<?php
		/**
		 * Contact Widget
		 */
		$this->load->view('customers/snippets/_contact_card', ['record' => $record]);
		?>
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#activity" data-toggle="tab">Summary</a></li>
				<li><a href="#tab-policy" data-toggle="tab">Policies</a></li>
				<li><a href="#tab-object" data-toggle="tab">Objects</a></li>
				<li><a href="#targets" data-toggle="tab">Invoices</a></li>
				<li><a href="#settings" data-toggle="tab">Documents</a></li>
				<li><a href="#settings" data-toggle="tab">Claims</a></li>
				<li><a href="#settings" data-toggle="tab">Reports</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-policy">

				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="tab-object">
					<?php
					/**
					 * Load Rows from View
					 */
					$this->load->view('objects/_list_widget', [
						'records' 			=> $objects,
						'customer_record' 	=> $record,
						'portfolio_record' 	=> NULL,
						'add_url' 			=> 'objects/add/' . $record->id
					]);
					?>
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