<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->username;?></td>
	<td><?php echo $record->role->name;?></td>
	<td><?php echo $record->branch->name;?></td>
	<td>
		<?php
		$profile = $record->profile ? json_decode($record->profile) : NULL;
		 echo $profile ? $profile->name : '';?>
	</td>
	<td class="ins-action">
		<a href="#" 
			title="Edit Basic Information" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information' 
			data-url="<?php echo site_url('users/edit/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Basic</span></a>
		<a href="#" 
			title="Edit Contact" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Contact' 
			data-url="<?php echo site_url('users/update_contact/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Contact</span></a>

		<a href="#" 
			title="Edit Profile" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Profile' 
			data-url="<?php echo site_url('users/update_profile/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Profile</span></a>
		<a href="#" 
			title="Change Password" 
			class="trg-dialog-edit action" 
			data-title='<i class="fa fa-pencil-square-o"></i> Change Password' 
			data-url="<?php echo site_url('users/change_password/' . $record->id);?>" 
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Password</span></a>

		<a href="<?php echo site_url('users/details/' . $record->id);?>" 
			data-toggle="tooltip"
			title="View user details." 
			class="action">
			<i class="fa fa-user"></i>
			<span>Details</span></a>

		<?php // disable for Admin Role ?>
		<a href="#" 
			title="Delete" 
			data-toggle="tooltip"
			class="trg-row-action action"
			data-confirm="true"
			data-url="<?php echo site_url('users/delete/' . $record->id);?>">
				<i class="fa fa-trash-o"></i>
				<span>Delete</span></a>
	</td>
</tr>