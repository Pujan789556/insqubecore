<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* TMI Plan:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code;?></td>
	<td><?php echo $record->name;?></td>
	<td><?php echo $record->parent_name ?? '-';?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<a href="#"
			title="Edit TMI Plan"
			data-toggle="tooltip"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit TMI Plan'
			data-url="<?php echo site_url($this->router->class . '/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<?php if($record->parent_id): ?>
			<a href="#"
				title="Edit Medical Tariff"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Medical Tariff - <?php echo $record->name?> (<?php echo $record->parent_name ?>)'
				data-url="<?php echo site_url($this->router->class . '/tariff/m/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-list"></i>
				<span class="hidden-xs">Medical Tariff</span>
			</a>

			<a href="#"
				title="Edit Package Tariff"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Package Tariff - <?php echo $record->name?> (<?php echo $record->parent_name ?>)'
				data-url="<?php echo site_url($this->router->class . '/tariff/p/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-list"></i>
				<span class="hidden-xs">Package Tariff</span>
			</a>
		<?php endif?>
	</td>
</tr>