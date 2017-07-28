<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->fiscal_yr_id;?>" class="searchable" data-id="<?php echo $record->fiscal_yr_id; ?>" id="_data-row-<?php echo $record->fiscal_yr_id;?>">
	<td><a href="#"
		title="Edit Portfolio Settings"
		class="trg-dialog-edit"
		data-box-size="large"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Settings'
		data-url="<?php echo site_url('portfolio/edit_settings/' . $record->fiscal_yr_id);?>"
		data-form=".form-iqb-general"><?php echo $record->code_np . " ({$record->code_en})";?></a></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Portfolio Settings"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Settings'
			data-url="<?php echo site_url('portfolio/edit_settings/' . $record->fiscal_yr_id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Import Missing Portfolio Settings for This Fiscal Year"
			data-box-size="large"
			class="trg-row-action action"
			data-url="<?php echo site_url('portfolio/import_missing_settings/' . $record->fiscal_yr_id);?>">
			<i class="fa fa-plus-square-o"></i>
			<span class="hidden-xs">Import Missing</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Duplicate Portfolio Settings"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Duplicate Portfolio Settings'
			data-url="<?php echo site_url('portfolio/duplicate_settings/' . $record->fiscal_yr_id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-copy"></i>
			<span class="hidden-xs">Duplicate</span>
		</a>
	</td>
</tr>