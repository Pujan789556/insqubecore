<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Search Filters
*/


?>
<?php echo form_open( $filter_url,  
                        [
                        	'id'  => '_form-iqub-filter',
                            'class' => 'form-inline form-iqb-filter',
                            'data-box' => '#iqb-data-list', // Filter Result Box
                            'data-method' => 'html'
                        ]); 

	/**
	 * Load Filter Components
	 */
	$this->load->view('templates/_common/_form_components_filter');
	?>
	<div class="row margin-t-10">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-info" id="_btn-filter-submit"><i class="fa fa-search"></i> Search</button>
			<button type="reset" class="btn btn-default" id="_btn-filter-reset"
			onclick='$("#btn-refresh").trigger("click")'>Clear</button>
		</div>
	</div>
<?php echo form_close();?>