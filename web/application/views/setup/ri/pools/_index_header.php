<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Setup - RI - Pools : Content Header
 */
?>
<div class="box no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;"><?php echo $content_header; ?></h1>
			</div>
			<div class="col-sm-4 master-actions text-right">
				<a href="#"
					title="Add Pool Treaty"
					data-toggle="tooltip"
					class="btn btn-success btn-round trg-dialog-edit"
					data-box-size="large"
					data-title='<i class="fa fa-pencil-square-o"></i> Add Pool Treaty'
					data-url="<?php echo site_url('ri_setup_pools/add/');?>"
					data-form=".form-iqb-general"
				><i class="ion-plus-circled"></i> Add</a>

				<a href="javascript:;"
					title="Refresh"
					id="btn-refresh"
					data-toggle="tooltip"
					class="btn btn-primary btn-round"
					data-url="<?php echo site_url($this->router->class);?>/refresh"
					data-method="html"
					data-box="#<?php echo $DOM_DataListBoxId?>"
					data-self-destruct="false"
					data-loader-box="false"
					onclick="return InsQube.load(event, this)"
				><i class="ion-refresh"></i> Refresh</a>
			</div>
		</div>
	</div>
</div>
