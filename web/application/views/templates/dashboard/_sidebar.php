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
                  <img src="http://insqube.dev/static/themes/AdminLTE-2.3.6/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
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

                  <li class="treeview <?php echo set_menu_active($nav_level_0, 'application_setup');?>">
                        <a href="#">
                              <i class="fa fa-server"></i>
                              <span>Application Settings</span>
                              <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                              </span>
                        </a>
                        <ul class="treeview-menu">
                              <li class="<?php echo set_menu_active($nav_level_1, 'settings');?>">
                                    <a href="<?php echo site_url('admin/settings');?>">
                                          <i class="fa fa-cog"></i> <span>Core Settings</span>
                                    </a>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'general');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> General
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'agents');?>"><a href="<?php echo site_url('admin/agents');?>"><i class="fa fa-circle-o"></i> Agents</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'branches');?>">
                                                <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Branches
                                                      <span class="pull-right-container">
                                                            <i class="fa fa-angle-left pull-right"></i>
                                                      </span>
                                                </a>
                                                <ul class="treeview-menu">
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'index');?>">
                                                            <a href="<?php echo site_url('admin/branches');?>" title="Manage Branches">
                                                                  <i class="fa fa-circle-o"></i> Manage Branches</a>
                                                      </li>
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'targets');?>">
                                                            <a href="<?php echo site_url('admin/branches/targets');?>" title="Manage branch-wise targets"><i class="fa fa-circle-o"></i> Branch Targets</a>
                                                      </li>
                                                </ul>
                                          </li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'companies');?>"><a href="<?php echo site_url('admin/companies');?>"><i class="fa fa-circle-o"></i> Companies</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'countries');?>"><a href="<?php echo site_url('admin/countries');?>"><i class="fa fa-globe"></i> Countries</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'departments');?>"><a href="<?php echo site_url('admin/departments');?>"><i class="fa fa-circle-o"></i> Departments</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'districts');?>"><a href="<?php echo site_url('admin/districts');?>"><i class="fa fa-circle-o"></i> Districts</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'fiscal_years');?>"><a href="<?php echo site_url('admin/fiscal_years');?>"><i class="fa fa-circle-o"></i> Fiscal Years</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'fy_months');?>"><a href="<?php echo site_url('admin/fy_months');?>"><i class="fa fa-circle-o"></i> Fiscal Year Months</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'fy_quarters');?>"><a href="<?php echo site_url('admin/fy_quarters');?>"><i class="fa fa-circle-o"></i> Fiscal Year Quarters</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'local_bodies');?>"><a href="<?php echo site_url('admin/local_bodies');?>"><i class="fa fa-circle-o"></i> Local Bodies</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'forex');?>"><a href="<?php echo site_url('admin/forex');?>"><i class="fa fa-circle-o"></i> Forex</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'months');?>"><a href="<?php echo site_url('admin/months');?>"><i class="fa fa-circle-o"></i> Nepali Months</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'regions');?>"><a href="<?php echo site_url('admin/regions');?>"><i class="fa fa-circle-o"></i> Regions</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'states');?>"><a href="<?php echo site_url('admin/states');?>"><i class="fa fa-circle-o"></i> States</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'surveyor_expertise');?>"><a href="<?php echo site_url('admin/surveyor_expertise');?>"><i class="fa fa-circle-o"></i> Surveyor Expertise</a></li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'surveyors');?>"><a href="<?php echo site_url('admin/surveyors');?>"><i class="fa fa-circle-o"></i> Surveyors</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'tags');?>"><a href="<?php echo site_url('admin/tags');?>"><i class="fa fa-circle-o"></i> Tags</a></li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'vehicle_reg_prefix');?>"><a href="<?php echo site_url('admin/vehicle_reg_prefix');?>"><i class="fa fa-circle-o"></i> Vehicle Reg Prefix</a></li>
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'beema_samiti');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Beema Samiti
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'bsrs_heading_types');?>">
                                                <a href="<?php echo site_url('admin/bsrs_heading_types');?>"><i class="fa fa-circle-o"></i> Report Heading Types</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'bsrs_headings');?>">
                                                <a href="<?php echo site_url('admin/bsrs_headings');?>"><i class="fa fa-circle-o"></i> Report Headings</a>
                                          </li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'bs_agro_categories');?>">
                                                <a href="<?php echo site_url('bs_agro_categories');?>"><i class="fa fa-circle-o"></i> Agriculture Categories</a>
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
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_voucher_types');?>">
                                                <a href="<?php echo site_url('ac_voucher_types');?>"><i class="fa fa-circle-o"></i> Voucher Types</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ac_duties_and_tax');?>">
                                                <a href="<?php echo site_url('ac_duties_and_tax');?>"><i class="fa fa-circle-o"></i> Duties &amp; Tax</a>
                                          </li>
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'ri');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Re-Insurance
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ri_setup_treaty_types');?>">
                                                <a href="<?php echo site_url('ri_setup_treaty_types');?>"><i class="fa fa-circle-o"></i> Treaty Types</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ri_setup_treaties');?>">
                                                <a href="<?php echo site_url('ri_setup_treaties');?>"><i class="fa fa-circle-o"></i> Treaties</a>
                                          </li>
                                          <li class="<?php echo set_menu_active($nav_level_2, 'ri_setup_pools');?>">
                                                <a href="<?php echo site_url('ri_setup_pools');?>"><i class="fa fa-circle-o"></i> Pools</a>
                                          </li>
                                    </ul>
                              </li>

                              <li class="<?php echo set_menu_active($nav_level_1, 'claim');?>">
                                    <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Claim
                                          <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                    </a>
                                    <ul class="treeview-menu">
                                          <li class="<?php echo set_menu_active($nav_level_2, 'claim_schemes');?>">
                                                <a href="<?php echo site_url('claim_schemes');?>"><i class="fa fa-circle-o"></i> Claim Schemes</a>
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
                                          <li class="<?php echo set_menu_active($nav_level_2, 'portfolio');?>">
                                                <a href="<?php echo site_url('portfolio');?>">
                                                      <i class="fa fa-circle-o"></i> Manage Portfolio
                                                </a>
                                          </li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'settings');?>">
                                                <a href="<?php echo site_url('portfolio/settings');?>">
                                                      <i class="fa fa-circle-o"></i> Portfolio Settings
                                                </a>
                                          </li>

                                          <li class="<?php echo set_menu_active($nav_level_2, 'endorsement_templates');?>">
                                                <a href="<?php echo site_url('endorsement_templates');?>">
                                                      <i class="fa fa-circle-o"></i> Endorsement Templates
                                                </a>
                                          </li>


                                          <li class="<?php echo set_menu_active($nav_level_2, 'tariff');?>">
                                                <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Tarrif
                                                      <span class="pull-right-container">
                                                            <i class="fa fa-angle-left pull-right"></i>
                                                      </span>
                                                </a>
                                                <ul class="treeview-menu">
                                                      <li class="<?php echo set_menu_active($nav_level_3, 'agriculture');?>">
                                                            <a href="<?php echo site_url('tariff/agriculture');?>">
                                                                  <i class="fa fa-circle-o"></i> Agriculture
                                                            </a>
                                                      </li>

                                                      <li class="<?php echo set_menu_active($nav_level_3, 'misc_bb');?>">
                                                            <a href="<?php echo site_url('tariff/misc_bb');?>">
                                                                  <i class="fa fa-circle-o"></i> MISC - Banker's Blanket
                                                            </a>
                                                      </li>

                                                      <li class="<?php echo set_menu_active($nav_level_3, 'misc_epa');?>">
                                                            <a href="<?php echo site_url('tariff/misc_epa');?>">
                                                                  <i class="fa fa-circle-o"></i> MISC - Expedition Personnel Accident
                                                            </a>
                                                      </li>

                                                      <li class="<?php echo set_menu_active($nav_level_3, 'tmi_plans');?>">
                                                            <a href="<?php echo site_url('tmi_plans');?>" title="Manage Travel Medical Insurance Plans & Tariff">
                                                                  <i class="fa fa-circle-o"></i> TMI Plans/Tariff
                                                            </a>
                                                      </li>

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
                                          <li class="<?php echo set_menu_active($nav_level_2, 'login_attempts');?>"><a href="<?php echo site_url('login_attempts');?>"><i class="fa fa-lock"></i> Login Attempts</a></li>
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
            <li class="<?php echo set_menu_active($nav_level_0, 'claims');?>">
                  <a href="<?php echo site_url('claims');?>">
                        <i class="fa fa-warning"></i> <span>Claims</span>
                  </a>
            </li>

            <li class="treeview <?php echo set_menu_active($nav_level_0, 'ri');?>">
                  <a href="#">
                        <i class="fa fa-book"></i> <span>RI</span>
                        <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                        </span>
                  </a>
                  <ul class="treeview-menu">
                        <li class="<?php echo set_menu_active($nav_level_1, 'ri_transactions');?>">
                              <a href="<?php echo site_url('ri_transactions');?>"><i class="fa fa-circle-o"></i> Transactions</a>
                        </li>

                  </ul>
            </li>

            <li class="treeview <?php echo set_menu_active($nav_level_0, 'accounting');?>">
                  <a href="#">
                        <i class="fa fa-book"></i> <span>Accounting</span>
                        <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                        </span>
                  </a>
                  <ul class="treeview-menu">
                        <li class="<?php echo set_menu_active($nav_level_1, 'ac_accounts');?>">
                              <a href="<?php echo site_url('ac_accounts');?>"><i class="fa fa-circle-o"></i> Accounts</a>
                        </li>
                        <li class="<?php echo set_menu_active($nav_level_1, 'ac_credit_notes');?>">
                              <a href="<?php echo site_url('ac_credit_notes');?>"><i class="fa fa-circle-o"></i> Credit Notes</a>
                        </li>
                        <li class="<?php echo set_menu_active($nav_level_1, 'ac_invoices');?>">
                              <a href="<?php echo site_url('ac_invoices');?>"><i class="fa fa-circle-o"></i> Invoices</a>
                        </li>
                        <li class="<?php echo set_menu_active($nav_level_1, 'ac_parties');?>">
                              <a href="<?php echo site_url('ac_parties');?>"><i class="fa fa-circle-o"></i> Parties</a>
                        </li>
                        <li class="<?php echo set_menu_active($nav_level_1, 'reports');?>">

                              <a href="#"><i class="fa fa-arrow-circle-o-down"></i> Reports
                                    <span class="pull-right-container">
                                          <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                              </a>
                              <ul class="treeview-menu">
                                    <li class="<?php echo set_menu_active($nav_level_2, 'ac_ledgers');?>">
                                          <a href="<?php echo site_url('ac_ledgers');?>"><i class="fa fa-file-text-o"></i> Ledgers</a>
                                    </li>
                                    <li class="<?php echo set_menu_active($nav_level_2, 'ac_trial_balance');?>">
                                          <a href="<?php echo site_url('ac_trial_balance');?>"><i class="fa fa-file-text-o"></i> Trial Balance</a>
                                    </li>
                              </ul>
                        </li>
                        <li class="<?php echo set_menu_active($nav_level_1, 'ac_vouchers');?>">
                              <a href="<?php echo site_url('ac_vouchers');?>"><i class="fa fa-circle-o"></i> Vouchers</a>
                        </li>
                  </ul>
            </li>

            <li class="treeview <?php echo set_menu_active($nav_level_0, 'reports');?>">
                  <a href="#">
                        <i class="fa fa-file-text"></i> <span>Reports</span>
                        <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                        </span>
                  </a>
                  <ul class="treeview-menu">
                        <li class="<?php echo set_menu_active($nav_level_1, 'bs_reports');?>">
                              <a href="<?php echo site_url('bs_reports');?>"><i class="fa fa-file-text-o"></i> Beema Samiti</a>
                        </li>
                  </ul>
            </li>
      </ul>
</section>
<!-- /.sidebar -->