<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company Branch: Index
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray">
				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-company-branch tr.searchable'])]);
						?>
					</div>
					<div class="col-sm-6 master-actions">
						<?php if( $this->dx_auth->is_authorized('companies', 'add.company.branch') ): ?>
							<a href="#"
								title="Add New Company Branch"
								data-toggle="tooltip"
								class="btn btn-success btn-round trg-dialog-edit pull-right"
								data-box-size="large"
								data-title='<i class="fa fa-pencil-square-o"></i> Add New Company Branch'
								data-url="<?php echo site_url($add_url);?>"
								data-form="#_form-company-branch"
							><i class="ion-plus-circled"></i> Add</a>
						<?php endif?>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="_iqb-data-list-box-company-branch">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('setup/company_branches/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>