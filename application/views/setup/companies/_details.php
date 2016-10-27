<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company: Details View
*/
?>
<div class="row">
	<div class="col-md-3">

		<div class="box box-primary">
			<div class="box-body box-profile">	

				<?php if( $record->picture ):?>
					<img 
						class="profile-user-img img-responsive img-circle ins-img-ip" 
						title="View large"
						src="<?php echo base_url()?>media/companies/<?php echo thumbnail_name($record->picture);?>" 
						alt="User profile picture"
						data-src="<?php echo base_url()?>media/companies/<?php echo $record->picture?>"
                      	onclick="InsQube.imagePopup(this, 'Company Logo')">
				<?php else:?>
					<p class="text-center img-circle profile-user-img">
                    	<i class="ion-ios-person-outline text-muted img-alt"></i>
                    </p>
                <?php endif?>

							
				<h3 class="profile-username text-center"><?php echo  $record->name;?></h3>

				<ul class="list-group list-group-unbordered">
					<li class="list-group-item">
						<b>UD Code</b> <span class="pull-right"><?php echo $record->ud_code?></span>
					</li>
					<li class="list-group-item">
						<b>Pan No</b> <span class="pull-right"><?php echo $record->pan_no?></span>
					</li>
					<li class="list-group-item">
						<b>Type</b> <span class="pull-right"><?php echo $record->type ;?></span>
					</li>
					
					<li class="list-group-item">
						<b>Active?</b> 
						<span class="pull-right">
						<?php
						if($record->active)
						{
							$active_str = '<i class="fa fa-circle text-green" title="Active" data-toggle="tooltip"></i>';
						}
						else
						{
							$active_str = '<i class="fa fa-circle-thin" title="Not Active" data-toggle="tooltip"></i>';
						}
						echo $active_str;
						?>	
						</span>
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
			echo get_contact_widget($record->contact);			
			?>
			<div class="box-footer">
				<a href="#" class="btn btn-primary btn-block"><i class="fa fa-pencil-square-o margin-r-5"></i><b>Edit Contact</b></a>	
			</div>
			
		</div>
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