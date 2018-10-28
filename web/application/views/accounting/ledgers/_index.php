<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Ledgers: Index View
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
				// $this->load->view('accounting/vouchers/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>

<div class="hide data-template">
	<?php
	$month_dd 	= nepali_month_dropdown();
	$quarter_dd = fiscal_year_quarters_dropdown();
	echo form_dropdown(['id'=>'month-dropdown-template'],$month_dd);
	echo form_dropdown(['id'=>'quarter-dropdown-template'],$quarter_dd);
	 ?>
</div>