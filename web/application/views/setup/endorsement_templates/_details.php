<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Templates: Details View
*/
?>
<div class="row">
	<div class="col-md-12">
		<div class="box box-solid box-bordered">
			<div class="box-header with-border">
              	<h3 class="box-title">Template Body</h3>
            </div>
            <div class="box-body">
            	<p class="alert bg-gray"><?php echo nl2br(htmlspecialchars($record->body))?></p>
            </div>
		</div>
	</div>
</div>