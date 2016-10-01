<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles: Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header gray">				
				<div class="row">
					<div class="col-xs-12">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('setup/users/_search_filters');
						?>
					</div>										
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */ 
				$this->load->view('setup/users/_list');
				?>				
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>