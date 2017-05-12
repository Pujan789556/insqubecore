<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Account: Find Widget
*/

// --------------------------------------------------------------------
?>
<div class="box box-solid no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;">Find Account</h1>
			</div>
			<div class="col-sm-4 master-actions text-right">
				<?php if( $this->dx_auth->is_authorized('ac_accounts', 'add.ac_account') ): ?>
					<a href="#"
						title="Add New Account"
						data-toggle="tooltip"
						class="btn btn-success btn-round trg-dialog-edit"
						data-box-size="large"
						data-title='<i class="fa fa-pencil-square-o"></i> Add New Account'
						data-url="<?php echo site_url('ac_accounts/add/y');?>"
						data-form="#__form-ac-chart-of-account"
					><i class="ion-plus-circled"></i> Add</a>
				<?php endif?>
			</div>
		</div>
	</div>
</div>
<?php
/**
 * Search Filters
 */
$this->load->view('templates/_common/_advanced_search_filter_general');
?>
<div class="box box-solid no-margin">
	<div class="box-body table-responsive no-padding" id="<?php echo $DOM_DataListBoxId?>" data-widget="search">
		<?php
		/**
		 * Load Rows from View
		 */
		$this->load->view('setup/ac/accounts/_list');
		?>
	</div>
</div>