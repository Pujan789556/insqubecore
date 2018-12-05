<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy List: List View For Customer Tab
*/
?>
<div class="row" id="list-widget-policies">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Customer Policies</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<a  href="#"
							class="btn btn-warning btn-round insqube-load"
							data-url="<?php echo site_url( 'policies/by_customer/' . $customer_id . '/1' );?>"
							data-load-method="get"
							data-box="#tab-policies"
							data-method="html"
							role="button"><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-policy tr.searchable'])]);
						?>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('policies/_list');
				?>
			</div>
		</div>
	</div>
</div>