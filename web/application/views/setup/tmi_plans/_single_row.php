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
	</td>
</tr>