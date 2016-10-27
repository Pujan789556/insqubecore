<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Company: Content Header
 */
?>
<div class="row">
	<div class="col-sm-8">
		<h1 style="margin:0; font-size:24px;"><?php echo $content_header; ?></h1>
	</div>
	<div class="col-sm-4 master-actions text-right">
		<a href="#" 
			title="Add New Company"
			data-toggle="tooltip"
			class="btn btn-success btn-round trg-dialog-edit" 
			data-size="large"
			data-title='<i class="fa fa-pencil-square-o"></i> Add New Company'
			data-url="<?php echo site_url('companies/add/');?>" 
			data-form=".form-iqb-general"
		><i class="ion-plus-circled"></i> Add</a>

		<a href="javascript:;" 
			title="Refresh"
			id="btn-refresh"
			data-toggle="tooltip"
			class="btn btn-primary btn-round" 
			data-url="<?php echo site_url($this->router->class);?>/refresh"
			data-method="html"
			data-box="#iqb-data-list"
			data-self-destruct="false"
			data-loader-box="false"
			onclick="return InsQube.load(event, this)"
		><i class="ion-refresh"></i> Refresh</a>						
	</div>
</div>
	