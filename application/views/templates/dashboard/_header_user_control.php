<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Layout: Dashboard
 * Section: Header
 * Sub-section: User Control
 */
?>
<!-- User Account: style can be found in dropdown.less -->
<li class="dropdown user user-menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		<img src="http://insqube.dev/public/themes/AdminLTE-2.3.6/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
		<span class="hidden-xs"><?php echo $this->dx_auth->get_username();?></span>
	</a>
	<ul class="dropdown-menu">
		<!-- User image -->
		<li class="user-header">
			<img src="http://insqube.dev/public/themes/AdminLTE-2.3.6/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
			<p>
				Alexander Pierce - Web Developer
				<small>Member since Nov. 2012</small>
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