<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->fiscal_yr_id;?>" class="searchable" data-id="<?php echo $record->fiscal_yr_id; ?>" id="_data-row-<?php echo $record->fiscal_yr_id;?>">
	<td><?php echo $record->code_np . " ({$record->code_en})";?></td>
	<td class="ins-action">
		<a href="<?php echo site_url('tariff/motor/details/' . $record->fiscal_yr_id);?>"
			data-toggle="tooltip"
			title="Details"
			class="action">
			<i class="fa fa-th-list"></i>
			<span class="hidden-xs">Details</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Duplicate Motor Tarrif"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Duplicate Motor Tarrif'
			data-url="<?php echo site_url('tariff/motor/duplicate/' . $record->fiscal_yr_id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-copy"></i>
			<span class="hidden-xs">Duplicate</span>
		</a>
	</td>
</tr>