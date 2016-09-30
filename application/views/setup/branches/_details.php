<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branch: Details View
*/
?>
<div class="row">
	<div class="col-md-3">

		<div class="box box-primary">
			<div class="box-body box-profile">				
				<h3 class="profile-username text-center"><?php echo $record->name;?></h3>
				<h5 class="text-muted text-center"><?php echo $record->code;?></h5>
			
				<ul class="list-group list-group-unbordered">
					<li class="list-group-item">
						<b>Annual Target</b> <a class="pull-right">1,322</a>
					</li>
					<li class="list-group-item">
						<b>Total Sales</b> <a class="pull-right">543</a>
					</li>
					<li class="list-group-item">
						<b>Staffs</b> <a class="pull-right">13,287</a>
					</li>
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