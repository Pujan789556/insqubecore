<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Advanced Search Filter: General
*
* Variables Required: $filter_url, $filters, $DOM_DataListBoxId, $DOM_FilterFormId
*/
$DOM_DataListBoxId = $DOM_DataListBoxId ?? '#_iqb-data-list';
$DOM_FilterFormId = $DOM_FilterFormId ?? '_form-iqb-filter';
?>
<div class="box box-solid no-margin ins-box-filter">
	<?php echo form_open( $filter_url,
            [
            	'id'  			=> $DOM_FilterFormId,
                'class' 		=> 'form-inline form-iqb-filter',
                'data-box' 		=> '#' . $DOM_DataListBoxId, // Filter Result Box
                'data-method' 	=> 'html'
            ]);?>
		<div class="box-body">
			<?php
			/**
			 * Load Filter Components
			 */
			$this->load->view('templates/_common/_form_components_filter');
			?>
		</div>
		<div class="box-footer text-right">
			<button type="submit" class="btn btn-info filter" id="_btn-filter-submit"><i class="fa fa-search"></i> Search</button>
			<button type="reset" class="btn btn-default" id="_btn-filter-reset"
				onclick='var f = $(this).closest("form"); f[0].reset(); f.find("button.filter").trigger("click"); return false;'>Clear</button>
		</div>
	<?php echo form_close();?>
</div>