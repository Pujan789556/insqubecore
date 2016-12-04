<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Advanced Search Filter: General
*
* Variables Required: $filter_url, $filters, $data_box
*/
$data_box = $data_box ?? '#iqb-data-list';
?>
<div class="box box-solid no-margin ins-box-filter">
	<?php echo form_open( $filter_url,
            [
            	'id'  => '_form-iqub-filter',
                'class' => 'form-inline form-iqb-filter',
                'data-box' => $data_box, // Filter Result Box
                'data-method' => 'html'
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
			<button type="submit" class="btn btn-info" id="_btn-filter-submit"><i class="fa fa-search"></i> Search</button>
			<button type="reset" class="btn btn-default" id="_btn-filter-reset"
				onclick='$("#btn-refresh").trigger("click")'>Clear</button>
		</div>
	<?php echo form_close();?>
</div>