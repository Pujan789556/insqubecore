<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff - Property Single Row
*/

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->id == $record->fiscal_yr_id;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';

$_edit_url 			= $this->data['_url_base'] . '/edit_fy/'  . $record->fiscal_yr_id;
$_duplicate_url 	= $this->data['_url_base'] . '/duplicate/'  . $record->fiscal_yr_id;
$_detail_url 		= $this->data['_url_base'] . '/details/' . $record->fiscal_yr_id;
?>
<tr data-name="<?php echo $record->fiscal_yr_id;?>"
	class="searchable <?php echo $current_class;?>"
	title="<?php echo $current_title?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $record->fiscal_yr_id; ?>"
	id="_data-row-<?php echo $record->fiscal_yr_id;?>">
	<td><?php echo $record->code_np . " ({$record->code_en})";?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Risk Categories for this Fiscal Year"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Risk Categories - <?php echo $record->code_np . " ({$record->code_en})" ?>'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<a href="<?php echo site_url($_detail_url);?>"
			data-toggle="tooltip"
			title="Details"
			class="action">
			<i class="fa fa-th-list"></i>
			<span class="hidden-xs">Details</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Duplicate Property Tarrif"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Duplicate Property Tarrif'
			data-url="<?php echo site_url($_duplicate_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-copy"></i>
			<span class="hidden-xs">Duplicate</span>
		</a>
	</td>
</tr>