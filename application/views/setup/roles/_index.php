<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles: Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header">				
				<div class="row">
					<div class="col-sm-6">
						<div class="input-group input-group-sm">
							<span class="input-group-addon"><i class="fa fa-search"></i></span>
							<input type="search" name="live_search" class="form-control pull-right" placeholder="Search" onkeyup="InsQube.liveSearch(this)">						
						</div>
					</div>					
					<div class="col-sm-6">
						<a href="#" title="Add new role"
							class="btn btn-success pull-right trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Add New Role' data-url="<?php echo site_url('roles/add/');?>" data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */ 
				$this->load->view('setup/roles/_list');
				?>				
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>