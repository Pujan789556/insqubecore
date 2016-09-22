<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branch: Details View
*/
?>
<div class="row">
	<div class="col-md-3">

		<div class="box box-widget widget-user">
			<div class="pad bg-aqua-active">				
				<h3 class="widget-user-username"><?php echo $record->name;?></h3>
				<h5 class="widget-user-desc"><?php echo $record->code;?></h5>
			</div>
			<div class="box-footer no-padding">
				<ul class="nav nav-stacked">
					<li><a href="#">Projects <span class="pull-right badge bg-blue">31</span></a></li>
					<li><a href="#">Tasks <span class="pull-right badge bg-aqua">5</span></a></li>
					<li><a href="#">Completed Projects <span class="pull-right badge bg-green">12</span></a></li>
					<li><a href="#">Followers <span class="pull-right badge bg-red">842</span></a></li>
				</ul>
			</div>
		</div>

		
		<!-- About Me Box -->
		<div class="box box-primary">
			<?php
			/**
			 * Contact Widget
			 */
			echo get_contact_widget($record->contacts);
			?>
		</div>
		<!-- /.box -->
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#activity" data-toggle="tab">Summary</a></li>
				<li><a href="#timeline" data-toggle="tab">Activities</a></li>
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