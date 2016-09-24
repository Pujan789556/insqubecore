<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Layout: Dashboard
 * Section: Sidebar
*/

/**
 * Active Primary Navigation Data
 */
$nav_level_0      = $_nav_primary['level_0'];
$nav_level_1      = $_nav_primary['level_1'];
$nav_level_2      = isset($_nav_primary['level_2']) ? $_nav_primary['level_2'] : NULL;

?>
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
      <?php /*?>
      <!-- Sidebar user panel -->
      <div class="user-panel">
            <div class="pull-left image">
                  <img src="http://insqube.dev/public/themes/AdminLTE-2.3.6/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                  <p>Alexander Pierce</p>
                  <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
      </div>
      <!-- search form -->
      
      <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                  <input type="text" name="q" class="form-control" placeholder="Search...">
                  <span class="input-group-btn">
                        <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                        </button>
                  </span>
            </div>
      </form><?php */?>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="<?php echo set_menu_active($nav_level_0, 'dashboard');?>">
                  <a href="<?php echo site_url('dashboard');?>">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                  </a>
            </li>
            <li class="<?php echo set_menu_active($nav_level_0, 'settings');?>">
                  <a href="<?php echo site_url('settings');?>">
                        <i class="fa fa-cog"></i> <span>Application Settings</span>
                  </a>
            </li>
            <?php if( $this->dx_auth->is_admin() ):?>
                  <li class="treeview <?php echo set_menu_active($nav_level_0, 'master_setup');?>">
                        <a href="#">
                              <i class="fa fa-server"></i>
                              <span>Master Setup</span>
                              <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                              </span>
                        </a>
                        <ul class="treeview-menu">
                              <li class="<?php echo set_menu_active($nav_level_1, 'general');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> General
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'fiscal_years');?>"><a href="<?php echo site_url('fiscal_years');?>"><i class="fa fa-circle-o"></i> Fiscal Years</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'departments');?>"><a href="<?php echo site_url('departments');?>"><i class="fa fa-circle-o"></i> Departments</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'countries');?>"><a href="<?php echo site_url('countries');?>"><i class="fa fa-globe"></i> Countries</a></li> 
                                          <li class="<?php echo set_menu_active($nav_level_2, 'districts');?>"><a href="<?php echo site_url('districts');?>"><i class="fa fa-circle-o"></i> Districts</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'branches');?>"><a href="<?php echo site_url('branches');?>"><i class="fa fa-circle-o"></i> Branches</a></li>    
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'security');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Security
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'roles');?>"><a href="<?php echo site_url('roles');?>"><i class="fa fa-lock"></i> Roles &amp; Permissions</a></li>    
                                    </ul>
                              </li>
                        </ul>
                  </li>
            <?php endif; ?>
      </ul>
</section>
<!-- /.sidebar -->