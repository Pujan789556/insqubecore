<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Commission Scale View
*/
$_edit_url 		= $this->data['_url_base'] . '/commission_scales/'  . $record->id;
?>
<div class="box box-success">
	<div class="box-header with-border">
		<h3 class="box-title">Treaty Commission Scale</h3>
		<a href="#"
            	class="action narrow trg-dialog-edit pull-right btn btn-sm btn-primary"
            	title="Edit Treaty Commission Scale"
            	data-toggle="tooltip"
            	data-box-size="large"
            	data-title="<i class='fa fa-pencil-square-o'></i> Edit Treaty Commission Scale"
            	data-url="<?php echo site_url($_edit_url)?>"
            	data-form="#__form-treaty-setup-commission-scale">
                <i class="fa fa-pencil-square-o"></i> Manage
            </a>

	</div>
	<div class="box-body" id="ri-commission-scale-data">
		<?php
		/**
		 * Load commission_scale data
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_commission_scale_data');
		?>
	</div>
</div>