<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Settings: Index View
*/
?>
<div class="row">
	<!-- /.col -->
	<div class="col-md-12">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-general-settings" data-toggle="tab">General</a></li>
				<li><a href="#tab-dates-settings" data-toggle="tab">Dates</a></li>
				<li><a href="#tab-sms-settings" data-toggle="tab">SMS</a></li>
				<li><a href="#tab-api-settings" data-toggle="tab">API</a></li>
				<li><a href="#tab-others-settings" data-toggle="tab">Others</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-general-settings">
					<?php
					/**
					 * Load General Form
					 */
					$this->load->view('setup/settings/_form_general', [
						'record' 		=> $record,
						'form_elements' => $rules['general']
					]);
					?>
				</div>
				<!-- /.tab-pane -->

				<div class="tab-pane" id="tab-dates-settings">
					<?php
					/**
					 * Load Section Form : Dates
					 */
					$action_url             = site_url("admin/settings/section/dates");
		            $dom_parent_container   = "#tab-dates-settings";
		            $this->load->view('setup/settings/_form_section', [
		                                'form_elements'         => $rules['dates'],
		                                'record'                => $record,
		                                'action_url'            => $action_url,
		                                'dom_parent_container'  => $dom_parent_container
		                            ]);
					?>
				</div>
				<!-- /.tab-pane -->

				<div class="tab-pane" id="tab-sms-settings">
					<?php
					/**
					 * Load Section Form : Dates
					 */
					$action_url             = site_url("admin/settings/section/sms");
		            $dom_parent_container   = "#tab-sms-settings";
		            $this->load->view('setup/settings/_form_section', [
		                                'form_elements'         => $rules['sms'],
		                                'record'                => $record,
		                                'action_url'            => $action_url,
		                                'dom_parent_container'  => $dom_parent_container
		                            ]);
					?>
				</div>
				<!-- /.tab-pane -->

				<div class="tab-pane" id="tab-api-settings">
					<?php
					/**
					 * Load Section Form : Dates
					 */
					$action_url             = site_url("admin/settings/section/api");
		            $dom_parent_container   = "#tab-api-settings";
		            $this->load->view('setup/settings/_form_section', [
		                                'form_elements'         => $rules['api'],
		                                'record'                => $record,
		                                'action_url'            => $action_url,
		                                'dom_parent_container'  => $dom_parent_container
		                            ]);
					?>
				</div>
				<!-- /.tab-pane -->


				<div class="tab-pane" id="tab-others-settings">
					<?php
					/**
					 * Load Section Form : Dates
					 */
					$action_url             = site_url("admin/settings/section/others");
		            $dom_parent_container   = "#tab-others-settings";
		            $this->load->view('setup/settings/_form_section', [
		                                'form_elements'         => $rules['others'],
		                                'record'                => $record,
		                                'action_url'            => $action_url,
		                                'dom_parent_container'  => $dom_parent_container
		                            ]);
					?>
				</div>
				<!-- /.tab-pane -->
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>