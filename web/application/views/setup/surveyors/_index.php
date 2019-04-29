<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyors: Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<?php
		/**
		 * Search Filters
		 */
		$this->load->view('templates/_common/_advanced_search_filter_general');
		?>
		<div class="box box-solid">
			<div class="box-body table-responsive data-rows" id="<?php echo $DOM_DataListBoxId?>">
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
	</div>
</div>