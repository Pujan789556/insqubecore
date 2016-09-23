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
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search');
						?>
					</div>					
					<div class="col-sm-6 master-actions text-right">
						<a href="#" title="Add new role"
							class="btn btn-success btn-round trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Add New Role' data-url="<?php echo site_url('roles/add/');?>" data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
						<a href="#" 
							title="Revoke all permissions"
							data-confirm="true"
							class="btn btn-danger btn-round trg-dialog-action" 	
							data-message="Are you sure you want to do this?<br/>All permissions for all roles will be cleared out."						
							data-url="<?php echo site_url('roles/revoke_all_permissions/');?>"
						><i class="fa fa-undo"></i> Revoke All</a>
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