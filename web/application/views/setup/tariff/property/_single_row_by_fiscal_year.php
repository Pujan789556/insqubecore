<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff Property:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
$_tariff_url 	= $this->data['_url_base'] . '/tariff/'  . $record->id;
$_risk_url 		= $this->data['_url_base'] . '/risks/'  . $record->id;
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
	<td><?php echo $record->code;?></td>
	<td><?php echo $record->name_en;?></td>
	<td><?php echo $record->name_np;?></td>

	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Property Risk Category"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Property Risk Category'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<a href="#"
			title="Edit Tariff"
			data-toggle="tooltip"
			data-box-size="full-width"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Tariff - <?php echo $record->name_en?> (<?php echo $record->name_np ?>)'
			data-url="<?php echo site_url($_tariff_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-list"></i>
			<span class="hidden-xs">Tariff</span>
		</a>

		<a href="#"
			title="Edit Risks"
			data-toggle="tooltip"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Risks - <?php echo $record->name_en?> (<?php echo $record->name_np ?>)'
			data-url="<?php echo site_url($_risk_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-list"></i>
			<span class="hidden-xs">Risks</span>
		</a>
	</td>
</tr>