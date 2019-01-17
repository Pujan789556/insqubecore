<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - MISC (Banker's Blanket) -  Single Row
*/

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->id == $record->fiscal_yr_id;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';

$_edit_url 			= $this->data['_url_base'] . '/misc_bb/edit/'  . $record->id;
$_duplicate_url 	= $this->data['_url_base'] . '/misc_bb/duplicate/'  . $record->id;
?>
<tr data-name="<?php echo $record->id;?>"
	class="searchable <?php echo $current_class;?>"
	title="<?php echo $current_title?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $record->id; ?>"
	id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->code_np . " ({$record->code_en})";?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Tariff"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Tariff'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Duplicate Tarrif"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Duplicate Tarrif'
			data-url="<?php echo site_url($_duplicate_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-copy"></i>
			<span class="hidden-xs">Duplicate</span>
		</a>
	</td>
</tr>