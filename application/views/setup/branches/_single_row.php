<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#" 
		data-toggle="tooltip"
		title="Edit branch" 
		class="trg-dialog-edit" 
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch' 
		data-url="<?php echo site_url('branches/edit/' . $record->id);?>" 
		data-form=".form-iqb-general"><?php echo $record->name;?></a></td>
	<td><?php echo $record->code;?></td>
	<td class="ins-action">
		<a href="#" 
			data-toggle="tooltip"
			title="Edit branch" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch' 
			data-url="<?php echo site_url('branches/edit/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>	
		<a href="<?php echo site_url('branches/details/' . $record->id);?>" 
			data-toggle="tooltip"
			title="View branch details." 
			class="action">
			<i class="fa fa-building-o"></i>
			<span class="hidden-xs">Details</span>
		</a>		
		<a href="#" 
			title="Delete" 
			data-toggle="tooltip"
			class="trg-row-delete action"
			data-url="<?php echo site_url('branches/delete/' . $record->id);?>">
				<i class="fa fa-trash-o"></i>
				<span class="hidden-xs">Delete</span>
		</a>
	</td>
</tr>