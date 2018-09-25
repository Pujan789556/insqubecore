<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties: Details View
*/
?>
<div class="row">
	<div class="col-md-3">

		<?php
		/**
		 * Profile Card
		 */
		$this->load->view('accounting/parties/snippets/_profile_card', ['record' => $record]);

		/**
		 * Contact Widget
		 */
		echo address_widget($address_record);
		?>
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-overview" data-toggle="tab">Overview</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-overview">
				</div>
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>