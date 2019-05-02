<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Tax and Commission View
*/
$_edit_url 		= $this->data['_url_base'] . '/tnc/'  . $record->id;
?>
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">Tax &amp; Commission</h3>
		<a href="#"
        	class="action narrow trg-dialog-edit pull-right btn btn-sm btn-primary"
        	title="Edit RI Tax & Commission"
        	data-toggle="tooltip"
        	data-box-size="large"
        	data-title="<i class='fa fa-pencil-square-o'></i> Edit RI Tax & Commission"
        	data-url="<?php echo site_url($_edit_url)?>"
        	data-form="#__form-treaty-setup-tnc">
            <i class="fa fa-pencil-square-o"></i> Manage
        </a>
	</div>
	<div class="box-body" style="overflow-x: scroll;" id="ri-tnc-data">
		<?php
		/**
		 * Load tax data
		 */
		$this->load->view($this->data['_view_base'] . '/snippets/_ri_tnc_data');
		?>
	</div>
</div>