<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyor: Find Widget
*/

// --------------------------------------------------------------------
?>
<div class="box box-solid no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;">Find Surveyor</h1>
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
		$this->load->view($this->data['_view_base'] . '/_list');
		?>
	</div>
	<!-- /.box-body -->
</div>
<!-- /.box -->