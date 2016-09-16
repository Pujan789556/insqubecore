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
				<li><a href="#tab-sms-settings" data-toggle="tab">SMS</a></li>
				<li><a href="#tab-others" data-toggle="tab">Others</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-general-settings">
					<?php 
					/**
					 * Load General Form 
					 */
					$this->load->view('settings/_form_general');
					?>
				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="tab-sms-settings">
					
				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="tab-others">
					
				</div>
				<!-- /.tab-pane -->
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>