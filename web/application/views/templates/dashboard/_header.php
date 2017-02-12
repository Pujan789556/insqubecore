<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Layout: Dashboard
 * Section: Header
*/
?>
<!-- Logo -->
<a href="<?php site_url('dashboard');?>" class="logo">
	<!-- mini logo for sidebar mini 50x50 pixels -->
	<span class="logo-mini"><b>I</b>QB</span>
	<!-- logo for regular state and mobile devices -->
	<span class="logo-lg"><b>Ins</b>Qube</span>
</a>
<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-static-top">
	<!-- Sidebar toggle button-->
	<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
		<span class="sr-only">Toggle navigation</span>
	</a>
	<!-- Navbar Right Menu -->
	<div class="navbar-custom-menu">
		<ul class="nav navbar-nav">

			<?php 
            /**
             * Templete Sub-Section: Header Message
             * 
             * Section Data will be available to this view
             * 
             * Load Directly
             */
            $this->load->view('templates/dashboard/_header_message');
            ?>

            <?php 
            /**
             * Templete Sub-Section: Header Notification
             * 
             * Section Data will be available to this view
             * 
             * Load Directly
             */
            $this->load->view('templates/dashboard/_header_notification');
            ?>

            <?php 
            /**
             * Templete Sub-Section: Header Task
             * 
             * Section Data will be available to this view
             * 
             * Load Directly
             */
            $this->load->view('templates/dashboard/_header_task');
            ?>

            <?php 
            /**
             * Templete Sub-Section: Header User Control
             * 
             * Section Data will be available to this view
             * 
             * Load Directly
             */
            $this->load->view('templates/dashboard/_header_user_control');
            ?>

			<!-- Control Sidebar Toggle Button -->
			<li>
				<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
			</li>
		</ul>
	</div>
</nav>
<?php
/**
* Templete Section: Header
*/
echo isset($__section_header) ? $__section_header : '';
?>