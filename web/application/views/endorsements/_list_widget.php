<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement: List View
*/
?>
<div class="row" id="list-widget-endorsements">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header with-border gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Manage Endorsements</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">

						<?php if( $this->dx_auth->is_authorized('endorsements', 'print.endorsement') ): ?>
							<a href="<?php echo site_url($print_url);?>"
								target="_blank"
								title="Print all active endorsement/transactions"
								data-toggle="tooltip"
								class="btn bg-navy btn-round"
							><i class="fa fa-print"></i> Print All</a>
						<?php endif?>

						<?php if( $this->dx_auth->is_authorized('endorsements', 'add.endorsement') ): ?>
							<div class="btn-group">
								<button type="button" class="btn btn-success btn-round dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
								<i class="ion-plus-circled margin-r-5"></i> Add <i class="fa fa-caret-down"></i></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<?php
									$txn_types = _ENDORSEMENT_type_eonly_dropdown(FALSE);
									foreach($txn_types as $key=>$label):
										$label = "Add Endorsement - " . $label;
									 ?>
									 	<li>
											<a href="#"
												title="<?php echo $label ?>"
												class="trg-dialog-edit"
												data-box-size="large"
												data-title='<i class="fa fa-pencil-square-o"></i> <?php echo $label ?>'
												data-url="<?php echo site_url( rtrim($add_url) . '/' . $key );?>"
												data-form="#_form-endorsements"
											><i class="ion-plus-circled margin-r-5"></i> <span><?php echo $label ?></span></a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						<?php endif?>

						<a href="#"
							title="Flush Cache"
							data-confirm="false"
							data-url="<?php echo site_url( 'endorsements/flush/' . $policy_record->id );?>"
							class="btn btn-warning btn-round trg-dialog-action"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-endorsements tr.searchable'])]);
						?>
					</div>

				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="_iqb-data-list-box-endorsements">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('endorsements/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>