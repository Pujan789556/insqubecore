<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pool Treaty : Snippet - Basic View
*/
?>
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><?php echo $record->name;?></h3>
	</div>
	<div class="box-body">
		<table class="table table-condensed table-responsive no-border">
			<tr>
				<th>Fiscal Year</th>
				<td class="text-right"><?php echo $record->fy_code_np . " ({$record->fy_code_en})"?></td>
			</tr>
		</table>
	</div>
</div>