<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Layout: Dashboard
 * Section: Sidebar
*/

/**
 * Active Primary Navigation Data
 */
$nav_level_0      = $_nav_primary['level_0'];
$nav_level_1      = $_nav_primary['level_1'] ?? NULL;
$nav_level_2      = $_nav_primary['level_2'] ?? NULL;
$nav_level_3      = $_nav_primary['level_3'] ?? NULL;
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

            <?php if( $this->dx_auth->is_admin() ):?>

                  <li class="<?php echo set_menu_active($nav_level_0, 'settings');?>">
                        <a href="<?php echo site_url('settings');?>">
                              <i class="fa fa-cog"></i> <span>Application Settings</span>
                        </a>
                  </li>

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
                                          <li class="<?php echo set_menu_active($nav_level_2, 'agents');?>"><a href="<?php echo site_url('agents');?>"><i class="fa fa-circle-o"></i> Agents</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'companies');?>"><a href="<?php echo site_url('companies');?>"><i class="fa fa-circle-o"></i> Companies</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'surveyors');?>"><a href="<?php echo site_url('surveyors');?>"><i class="fa fa-circle-o"></i> Surveyors</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'fiscal_years');?>"><a href="<?php echo site_url('fiscal_years');?>"><i class="fa fa-circle-o"></i> Fiscal Years</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'fy_quarters');?>"><a href="<?php echo site_url('fy_quarters');?>"><i class="fa fa-circle-o"></i> Fiscal Years Quarters</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'departments');?>"><a href="<?php echo site_url('departments');?>"><i class="fa fa-circle-o"></i> Departments</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'countries');?>"><a href="<?php echo site_url('countries');?>"><i class="fa fa-globe"></i> Countries</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'districts');?>"><a href="<?php echo site_url('districts');?>"><i class="fa fa-circle-o"></i> Districts</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'branches');?>">
                                                <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Branches
                                                      <span class="pull-right-container">
                                                            <i class="fa fa-angle-left pull-right"></i>
                                                      </span>
                                                </a>
                                                <ul class="treeview-menu">
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'index');?>">
                                                            <a href="<?php echo site_url('branches');?>" title="Manage Branches">
                                                                  <i class="fa fa-circle-o"></i> Manage Branches</a>
                                                      </li>
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'targets');?>">
                                                            <a href="<?php echo site_url('branches/targets');?>" title="Manage branch-wise targets"><i class="fa fa-circle-o"></i> Branch Targets</a>
                                                      </li>
                                                </ul>
                                          </li>
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'account');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Account
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_account_groups');?>">
                                                <a href="<?php echo site_url('ac_account_groups');?>"><i class="fa fa-circle-o"></i> Account Groups</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_accounts');?>">
                                                <a href="<?php echo site_url('ac_accounts');?>"><i class="fa fa-circle-o"></i> Accounts</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_voucher_types');?>">
                                                <a href="<?php echo site_url('ac_voucher_types');?>"><i class="fa fa-circle-o"></i> Voucher Types</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_duties_and_tax');?>">
                                                <a href="<?php echo site_url('ac_duties_and_tax');?>"><i class="fa fa-circle-o"></i> Duties &amp; Tax</a>
                                          </li>


                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'ri');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> RI Setup
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ri_setup_treaty_types');?>">
                                                <a href="<?php echo site_url('ri_setup_treaty_types');?>"><i class="fa fa-circle-o"></i> Treaty Types</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ri_setup_treaties');?>">
                                                <a href="<?php echo site_url('ri_setup_treaties');?>"><i class="fa fa-circle-o"></i> Treaty Setup</a>
                                          </li>
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'portfolio');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Portfolio
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'portfolio');?>"><a href="<?php echo site_url('portfolio');?>"><i class="fa fa-circle-o"></i> Manage Portfolio</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'settings');?>"><a href="<?php echo site_url('portfolio/settings');?>"><i class="fa fa-circle-o"></i> Portfolio Settings</a></li>



                                          <li class="<?php echo set_menu_active($nav_level_2, 'tariff');?>">
                                                <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Tarrif
                                                      <span class="pull-right-container">
                                                            <i class="fa fa-angle-left pull-right"></i>
                                                      </span>
                                                </a>
                                                <ul class="treeview-menu">
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'motor');?>">
                                                            <a href="<?php echo site_url('tariff/motor');?>">
                                                                  <i class="fa fa-circle-o"></i> Motor
                                                            </a>
                                                      </li>
                                                </ul>
                                          </li>

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
                                          <li class="<?php echo set_menu_active($nav_level_2, 'users');?>"><a href="<?php echo site_url('users');?>"><i class="fa fa-users"></i> Users</a></li>
                                    </ul>
                              </li>
                        </ul>
                  </li>
            <?php endif; ?>

            <li class="<?php echo set_menu_active($nav_level_0, 'activities');?>">
                  <a href="<?php echo site_url('activities');?>">
                        <i class="fa fa-history"></i> <span>Activities</span>
                  </a>
            </li>

            <li class="<?php echo set_menu_active($nav_level_0, 'customers');?>">
                  <a href="<?php echo site_url('customers');?>">
                        <i class="fa fa-users"></i> <span>Customers</span>
                  </a>
            </li>
            <li class="<?php echo set_menu_active($nav_level_0, 'policies');?>">
                  <a href="<?php echo site_url('policies');?>">
                        <i class="fa fa-certificate"></i> <span>Policies</span>
                  </a>
            </li>
            <li class="<?php echo set_menu_active($nav_level_0, 'objects');?>">
                  <a href="<?php echo site_url('objects');?>">
                        <i class="fa fa-certificate"></i> <span>Objects</span>
                  </a>
            </li>
      </ul>
</section>
<!-- /.sidebar -->