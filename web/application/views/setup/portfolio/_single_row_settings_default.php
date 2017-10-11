<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* POrtfolio Settings:  Single Row
*/

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->id == $record->fiscal_yr_id;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';
?>

<tr
	data-name="<?php echo $record->fiscal_yr_id;?>"
	class="searchable <?php echo $current_class;?>"
	title="<?php echo $current_title?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $record->fiscal_yr_id; ?>"
	id="_data-row-<?php echo $record->fiscal_yr_id;?>">
	<td><?php echo $record->code_np . " ({$record->code_en})";?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Portfolio Settings"
			data-box-size="full-width"
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
		<a href="<?php echo site_url('portfolio/settings/fy/' . $record->fiscal_yr_id);?>"
			class="action"
			data-toggle="tooltip"
			title="List all Portfolio Settings for this Fiscal Year">
			<i class="fa fa-list"></i>
			<span class="hidden-xs">List All</span>
		</a>
	</td>
</tr>