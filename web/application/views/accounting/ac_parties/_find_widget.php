<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties: Find Widget
*/

// --------------------------------------------------------------------
?>
<div class="box box-solid no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;">Find Accounting Party</h1>
			</div>
			<div class="col-sm-4 master-actions text-right">
				<?php if( $this->dx_auth->is_authorized('ac_parties', 'add.ac_party') ): ?>
					<a href="#"
						title="Add New Accounting Party"
						data-toggle="tooltip"
						class="btn btn-success btn-round trg-dialog-edit"
						data-box-size="large"
						data-title='<i class="fa fa-pencil-square-o"></i> Add New Accounting Party'
						data-url="<?php echo site_url($this->data['_url_base'] .'/add/y/' . $widget_reference);?>"
						data-form="#_form-ac_party"
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
		$this->load->view($this->data['_view_base'] .'/_list');
		?>
	</div>
	<!-- /.box-body -->
</div>
<!-- /.box -->