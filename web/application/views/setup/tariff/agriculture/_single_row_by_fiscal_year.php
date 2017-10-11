<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - Agriculture Single Row by Fiscal year
*/
?>
<tr data-name="<?php echo $record->id;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
	<td><?php echo $record->portfolio_name_en; ?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Single Tariff"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Single Tariff'
			data-url="<?php echo site_url('tariff/agriculture/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>