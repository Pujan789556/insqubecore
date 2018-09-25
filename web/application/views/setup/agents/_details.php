<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agent: Details View
*/
// $profile = $record->profile ? json_decode($record->profile) : new class($record){
// 	public function __construct($record)
// 	{
// 		$this->name = $record->username;
// 		$this->dob = $this->gender = $this->picture = $this->designation = $this->salary = '';
// 	}
// };
?>
<div class="row">
	<div class="col-md-3">

		<!-- About Me Box -->
		<?php
		/**
		 * Profile Card
		 */
		$this->load->view('setup/agents/snippets/_profile_card', ['record' => $record]);

		/**
		 * Contact Widget
		 */
		echo address_widget($address_record);
		?>
		<!-- /.box -->
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#activity" data-toggle="tab">Summary</a></li>
				<li><a href="#timeline" data-toggle="tab">Activities</a></li>
				<li><a href="#targets" data-toggle="tab">Targets</a></li>
				<li><a href="#settings" data-toggle="tab">Staffs</a></li>
				<li><a href="#settings" data-toggle="tab">Customers</a></li>
				<li><a href="#settings" data-toggle="tab">Policies</a></li>
				<li><a href="#settings" data-toggle="tab">Claims</a></li>
				<li><a href="#settings" data-toggle="tab">Reports</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="activity">

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