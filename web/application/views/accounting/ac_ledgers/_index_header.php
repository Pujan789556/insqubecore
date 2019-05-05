<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ledgers: Content Header
 */
?>
<div class="box no-margin">
	<div class="box-header no-border gray">
		<div class="row">
			<div class="col-sm-8">
				<h1 style="margin:0; font-size:24px;"><?php echo $content_header; ?></h1>
			</div>
			<div class="col-sm-4 master-actions text-right">
				<a href="<?php echo site_url($this->router->class);?>"
					title="Refresh"
					data-toggle="tooltip"
					class="btn btn-primary btn-round"
				><i class="ion-refresh"></i> Refresh</a>
			</div>
		</div>
	</div>
</div>
