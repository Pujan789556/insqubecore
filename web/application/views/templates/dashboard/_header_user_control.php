<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Layout: Dashboard
 * Section: Header
 * Sub-section: User Control
 */

$loggedin_user_profile 	= $this->user->profile ? json_decode($this->user->profile) : NULL;
$profile_image 			= $loggedin_user_profile->picture ?? NULL;
$profile_picture_url 	= $profile_image ? site_url('static/media/users/' . thumbnail_name($profile_image)) : '';
$profile_picture_url = '';
?>
<!-- User Account: style can be found in dropdown.less -->
<li class="dropdown user user-menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		<?php if($profile_picture_url):?>
			<img src="<?php echo $profile_picture_url;?>" class="user-image" alt="User Image">
		<?php else:?>
				<span class="user-image">
					<i class="ion-ios-person-outline"></i>
				</span>
		<?php endif?>
		<span class="hidden-xs"><?php echo $this->dx_auth->get_username();?></span>
	</a>
	<ul class="dropdown-menu">
		<!-- User image -->
		<li class="user-header">
			<?php if($profile_picture_url):?>
				<img src="<?php echo $profile_picture_url;?>" class="img-circle" alt="User Image">
			<?php else:?>
					<span class="img-circle">
						<i class="ion-ios-person-outline"></i>
					</span>
			<?php endif?>

			<p>
				<?php echo $loggedin_user_profile ? $loggedin_user_profile->name :  $this->dx_auth->get_username(); ?> - <?php echo $this->user->role_name; ?>

				<?php if($loggedin_user_profile):?>
					<small><?php echo $loggedin_user_profile->designation;?></small>
				<?php endif;?>
			</p>
		</li>
		<!-- Menu Body -->
		<li class="user-body">
			<div class="row">
				<div class="col-xs-12">
					<a href="#">Settings <i class="fa fa-cog pull-right"></i></a>
				</div>
			</div>
		</li>

		<li class="user-body">
			<div class="row">
				<div class="col-xs-12">
					<a href="#">Help <i class="fa fa-book pull-right"></i></a>
				</div>
			</div>
		</li>

		<!-- Menu Footer-->
		<li class="user-footer">
			<div class="pull-left">
				<a href="<?php echo site_url('profile');?>" class="btn btn-primary btn-flat"><i class="ion-person"></i> My Profile</a>
			</div>
			<div class="pull-right">
				<a href="<?php echo site_url($this->dx_auth->logout_uri);?>" class="btn btn-default btn-flat"><i class="ion-log-out"></i> Log Out</a>
			</div>
		</li>
	</ul>
</li>