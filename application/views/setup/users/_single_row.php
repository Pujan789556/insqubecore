<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information' data-url="<?php echo site_url('users/edit/' . $record->id);?>" data-form=".form-iqb-general"><?php echo $record->username;?></a></td>
	<td></td>
	<td class="ins-action">
		<a href="#" 
			title="Edit Basic Information" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information' 
			data-url="<?php echo site_url('users/edit/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="#" 
			title="Edit Contact" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Contact' 
			data-url="<?php echo site_url('users/update_contact/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Contact</span>
		</a>
		<a href="#" 
			title="Edit Profile" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Profile' 
			data-url="<?php echo site_url('users/update_profile/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Profile</span>
		</a>

		<a href="#" 
			title="Change Password" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Change Password' 
			data-url="<?php echo site_url('users/change_password/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Password</span>
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