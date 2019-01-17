<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Users: Content Header
 */
?>
<div class="box no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-md-4">
				<h1 style="margin:0; font-size:24px;"><?php echo $content_header; ?></h1>
			</div>
			<div class="col-md-8 master-actions text-right">
				<a href="#"
					title="Add New User"
					data-toggle="tooltip"
					class="btn btn-success btn-round trg-dialog-edit"
					data-size="large"
					data-title='<i class="fa fa-pencil-square-o"></i> Add New User'
					data-url="<?php echo site_url( $this->data['_url_base'] . '/add/');?>"
					data-form=".form-iqb-general"><i class="ion-plus-circled"></i> Add</a>

				<a href="javascript:;"
					title="Refresh"
					id="btn-refresh"
					data-toggle="tooltip"
					class="btn btn-primary btn-round"
					data-url="<?php echo site_url($this->data['_url_base'] . '/refresh');?>"
					data-method="html"
					data-box="#<?php echo $DOM_DataListBoxId?>"
					data-self-destruct="false"
					data-loader-box="false"
					onclick="return InsQube.load(event, this)">
						<i class="ion-refresh"></i> Refresh</a>

				<a href="<?php echo site_url( $this->data['_url_base'] . '/flush/');?>" title="Flush Cache"
						class="btn btn-warning btn-round"
						data-toggle="tooltip"
					><i class="fa fa-trash-o"></i> Flush Cache</a>

				<a href="#"
					data-toggle="tooltip"
					title="Revoke Back Date Setting from All Users"
					data-title="Revoke Back Date Setting from All Users?"
					data-confirm="true"
					class="btn btn-danger btn-round trg-dialog-action"
					data-message="Are you sure you want to do this?<br/>Users having back date settings will be reset."
					data-url="<?php echo site_url( $this->data['_url_base'] . '/revoke_all_backdate/');?>">
						<i class="fa fa-undo"></i> Back Date</a>

				<a href="#"
					data-toggle="tooltip"
					title="Force-login to all users."
					data-title="Force-login to all users?"
					data-confirm="true"
					class="btn btn-danger btn-round trg-dialog-action"
					data-message="Are you sure you want to do this?<br/>This will force all users to re-login."
					data-url="<?php echo site_url($this->data['_url_base'] . '/force_relogin_all/');?>">
						<i class="fa fa-lock"></i> Re-login All</a>

				<a href="#"
					data-toggle="tooltip"
					title="Rengerate new password and force-login to all users."
					data-title="Rengerate new password and force-login to all users?"
					data-confirm="true"
					class="btn btn-danger btn-round trg-dialog-action"
					data-message="Are you sure you want to do this?<br/>This will regenerate new passwords for all users and apply force-relogin."
					data-url="<?php echo site_url($this->data['_url_base'] . '/renew_passwords/');?>">
						<i class="fa fa-lock"></i> Renew Passwords</a>


			</div>
		</div>
	</div>
</div>
