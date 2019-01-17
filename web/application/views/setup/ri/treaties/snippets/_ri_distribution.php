<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Distribution View
*/
$_edit_url 		= $this->data['_url_base'] . '/distribution/'  . $record->id;
?>
<div class="box box-success">
	<div class="box-header with-border">
		<h3 class="box-title">RI Distribution</h3>
		<a href="#"
            	class="action narrow trg-dialog-edit pull-right btn btn-sm btn-primary"
            	title="Edit RI Distribution"
            	data-toggle="tooltip"
            	data-box-size="full-width"
            	data-title="<i class='fa fa-pencil-square-o'></i> Edit RI Distribution"
            	data-url="<?php echo site_url($_edit_url)?>"
            	data-form="#__form-treaty-setup-distribution">
                <i class="fa fa-pencil-square-o"></i> Manage
            </a>

	</div>
	<div class="box-body" id="ri-distribution-data">
		<?php
		/**
		 * Load distribution data
		 */
		$this->load->view('setup/ri/treaties/snippets/_ri_distribution_data');
		?>
	</div>
</div>