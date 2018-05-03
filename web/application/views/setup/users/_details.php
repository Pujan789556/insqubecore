<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* User: Details View
*/
$profile = $record->profile ? json_decode($record->profile) : new class($record){
	public function __construct($record)
	{
		$this->name = $record->username;
		$this->dob = $this->gender = $this->picture = $this->designation = $this->salary = '';
	}
};
?>
<div class="row">
	<div class="col-md-3">

		<div class="box box-primary">
			<div class="box-body box-profile">

				<?php if( $profile && $profile->picture ):?>
					<img
						class="profile-user-img img-responsive img-circle ins-img-ip"
						title="View large"
						src="<?php echo INSQUBE_MEDIA_URL?>users/<?php echo thumbnail_name($profile->picture);?>"
						alt="User profile picture"
						data-src="<?php echo INSQUBE_MEDIA_URL?>users/<?php echo $profile->picture?>"
                      	onclick="InsQube.imagePopup(this, 'Profile Picture')">
				<?php else:?>
					<p class="text-center img-circle profile-user-img">
                    	<i class="ion-ios-person-outline text-muted img-alt"></i>
                    </p>
                <?php endif?>


				<h3 class="profile-username text-center"><?php echo $profile ? $profile->name : $record->username;?></h3>
				<h5 class="text-center"><strong><?php echo $profile->designation; ?></strong></h5>
				<p class="text-center text-muted"><?php echo $record->branch_name_en, ' (', $record->branch_name_np, ')';?></p>

				<!-- <ul class="list-group list-group-unbordered">
					<li class="list-group-item">
						<b>Annual Target</b> <a class="pull-right">1,322</a>
					</li>
					<li class="list-group-item">
						<b>Total Sales</b> <a class="pull-right">543</a>
					</li>
					<li class="list-group-item">
						<b>Staffs</b> <a class="pull-right">13,287</a>
					</li>
				</ul> -->
			</div>
		</div>

		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">About Me</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body">

				<strong>Department</strong>
				<p class="text-muted"><?php echo $record->department_name;?></p>
				<hr/>

				<strong><i class="fa fa-genderless margin-r-5"></i> Gender</strong>
				<p class="text-muted"><?php echo ucfirst($profile->gender);?></p>
				<hr>

				<strong><i class="fa fa-calendar margin-r-5"></i> Date of Birth</strong>
				<p class="text-muted"><?php echo $profile->dob ? date('Y M d', strtotime($profile->dob)) : '';?></p>
				<hr>

				<strong><i class="fa  fa-dollar margin-r-5"></i> Sallary</strong>
				<p class="text-muted"><?php echo $profile->salary;?></p>
			</div>
			<!-- /.box-body -->
			<div class="box-footer">
				<a href="#"
					class="btn btn-primary btn-block trg-dialog-edit"
					data-title='<i class="fa fa-pencil-square-o"></i> Edit Profile'
					data-url="<?php echo site_url('users/update_profile/' . $record->id);?>"
					data-form=".form-iqb-general"><i class="fa fa-pencil-square-o margin-r-5"></i><b>Edit Profile</b></a>
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