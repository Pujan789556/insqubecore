<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Users: Content Header
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
					title="Add New User"
					data-toggle="tooltip"
					class="btn btn-success btn-round trg-dialog-edit"
					data-size="large"
					data-title='<i class="fa fa-pencil-square-o"></i> Add New User'
					data-url="<?php echo site_url('users/add/');?>"
					data-form=".form-iqb-general"><i class="ion-plus-circled"></i> Add</a>

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
					onclick="return InsQube.load(event, this)">
						<i class="ion-refresh"></i> Refresh</a>

				<a href="#"
					title="Revoke Back Date Setting from All Users"
					data-title="Revoke Back Date Setting from All Users?"
					data-confirm="true"
					class="btn btn-danger btn-round trg-dialog-action"
					data-message="Are you sure you want to do this?<br/>Users having back date settings will be reset."
					data-url="<?php echo site_url('users/revoke_all_backdate/');?>">
						<i class="fa fa-undo"></i> Back Date</a>
			</div>
		</div>
	</div>
</div>
