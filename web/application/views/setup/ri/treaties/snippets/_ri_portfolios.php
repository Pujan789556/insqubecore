<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Portfolios View
*/
$_edit_url 		= $this->data['_url_base'] . '/portfolios/'  . $record->id;
?>
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">Portfolios</h3>
		<a href="#"
        	class="action narrow trg-dialog-edit pull-right btn btn-sm btn-primary"
        	title="Edit RI Portfolios"
        	data-toggle="tooltip"
        	data-box-size="full-width"
        	data-title="<i class='fa fa-pencil-square-o'></i> Edit RI Portfolios"
        	data-url="<?php echo site_url($_edit_url)?>"
        	data-form="#__form-treaty-setup-distribution">
            <i class="fa fa-pencil-square-o"></i> Manage
        </a>
	</div>
	<div class="box-body small" style="overflow-x: scroll;" id="ri-portfolio-data">
		<?php
		/**
		 * Load portfolio data
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_portfolio_data');
		?>
	</div>
</div>