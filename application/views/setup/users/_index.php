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
						<a href="#" 
							title="Add New User"
							data-toggle="tooltip"
							class="btn btn-success btn-round trg-dialog-edit" 
							data-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Add New User'
							data-url="<?php echo site_url('users/add/');?>" 
							data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>

						<a href="javascript:;" 
							title="Refresh"
							data-toggle="tooltip"
							class="btn btn-primary btn-round" 
							data-url="<?php echo site_url($this->router->class);?>"
							data-method="html"
							data-box="#iqb-data-list"
							data-self-destruct="false"
							data-loader-box="false"
							onclick="return InsQube.load(event, this)"
						><i class="ion-refresh"></i> Refresh</a>						
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