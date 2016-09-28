<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit Role' data-url="<?php echo site_url('users/edit/' . $record->id);?>" data-form=".form-iqb-general"><?php echo $record->username;?></a></td>
	<td></td>
	<td class="ins-action">
		<a href="#" 
			title="Edit" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Role' 
			data-url="<?php echo site_url('users/edit/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<a href="#" 
			title="Manage Role Permissions" 
			data-title='<i class="fa fa-pencil-square-o"></i> Manage Role Permissions - <?php echo $record->username;?>' 
			data-toggle="tooltip"
			data-box-size="large"
			data-form=".form-iqb-general"
			class="trg-dialog-edit action"
			data-url="<?php echo site_url('users/permissions/' . $record->id);?>">
				<i class="fa fa-lock"></i>
				<span class="hidden-xs">Permission</span>
		</a>

		<?php // disable for Admin Role ?>
		<a href="#" 
			title="Delete" 
			data-toggle="tooltip"
			class="trg-row-action action"
			data-confirm="true"
			data-url="<?php echo site_url('users/delete/' . $record->id);?>">
				<i class="fa fa-trash-o"></i>
				<span class="hidden-xs">Delete</span>
		</a>
	</td>
</tr>