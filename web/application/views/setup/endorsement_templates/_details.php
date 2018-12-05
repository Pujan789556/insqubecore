<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Templates: Details View
*/
?>
<div class="box box-solid box-bordered">
	<div class="box-header with-border">
      	<h3 class="box-title">Template Body</h3>
    </div>
    <div class="box-body">
    	<?php echo nl2br(htmlspecialchars($record->body))?>
    </div>
</div>