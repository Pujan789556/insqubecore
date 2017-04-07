<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : Snippet - Basic View
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
			<tr>
				<th>Treaty Type</th>
				<td class="text-right"><?php echo $record->treaty_type_name ;?></td>
			</tr>
			<tr>
				<th>Estimated Premium Income</th>
				<td class="text-right"><?php echo $record->estimated_premium_income ;?></td>
			</tr>
			<tr>
				<th>Treaty Effective Date</th>
				<td class="text-right"><?php echo $record->treaty_effective_date ;?></td>
			</tr>
		</table>
	</div>
</div>