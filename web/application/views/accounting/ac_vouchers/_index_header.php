<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Vouchers: Content Header
 */
?>
<div class="box no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;"><?php echo $content_header; ?></h1>
			</div>
			<div class="col-sm-4 master-actions text-right">
				<?php if( $this->dx_auth->is_authorized($this->router->class, 'add.voucher') ): ?>
					<a href="#"
						title="Add New Voucher"
						data-toggle="tooltip"
						class="btn btn-success btn-round trg-dialog-edit"
						data-box-size="full-width"
						data-title='<i class="fa fa-pencil-square-o"></i> Add New Voucher'
						data-url="<?php echo site_url($this->data['_url_base'] . '/add/');?>"
						data-form="#__form-ac-voucher"
					><i class="ion-plus-circled"></i> Add</a>
				<?php endif;?>

				<a href="javascript:;"
					title="Refresh"
					id="btn-refresh"
					data-toggle="tooltip"
					class="btn btn-primary btn-round"
					data-url="<?php echo site_url($this->data['_url_base']);?>/refresh"
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
