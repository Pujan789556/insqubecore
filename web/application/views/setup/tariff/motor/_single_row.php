<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff:  Motor - Single Row
*/

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->id == $record->fiscal_yr_id;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';

$_duplicate_url 	= $this->data['_url_base'] . '/motor/duplicate/' . $record->fiscal_yr_id;
$_detail_url 		= $this->data['_url_base'] . '/motor/details/' . $record->fiscal_yr_id;
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
		<a href="<?php echo site_url($_detail_url);?>"
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
			data-url="<?php echo site_url($_duplicate_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-copy"></i>
			<span class="hidden-xs">Duplicate</span>
		</a>
	</td>
</tr>