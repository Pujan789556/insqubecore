<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Group:  Single Row
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->parent_name_en ?? '-';?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit Account Group"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Account Group'
		data-url="<?php echo site_url('ac_account_groups/edit/' . $record->id);?>"
		data-form=".form-iqb-general"><?php echo $record->name_en;?></a></td>
	<td><?php echo $record->range_min;?></td>
	<td><?php echo $record->range_max;?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Account Group"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Account Group'
			data-url="<?php echo site_url('ac_account_groups/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>