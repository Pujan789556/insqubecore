<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<a href="<?php echo site_url('users/details/' . $record->id);?>" 
						title="View user details.">
						<?php echo $record->username;?></a>
	</td>
	<td><?php echo $record->role_name;?></td>
	<td><?php echo $record->department_name;?></td>
	<td><?php echo $record->branch_name;?></td>
	<td>
		<?php
		$profile = $record->profile ? json_decode($record->profile) : NULL;
		 echo $profile ? $profile->name : '';?>
	</td>
	<td>
		<?php
		if($record->banned)
		{
			$banned_str = '<i class="fa fa-circle text-red" title="Banned" data-toggle="tooltip"></i>';
		}
		else
		{
			$banned_str = '<i class="fa fa-circle-thin" title="Not Banned" data-toggle="tooltip"></i>';
		}
		echo $banned_str;
		?>
	</td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#" 
						title="Edit Basic Information" 
						class="trg-dialog-edit" 
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information' 
						data-url="<?php echo site_url('users/edit/' . $record->id);?>" 
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Basic Info</span></a>
				</li>
				<li>
					<a href="#" 
						title="Edit Contact" 
						class="trg-dialog-edit" 
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Contact' 
						data-url="<?php echo site_url('users/update_contact/' . $record->id);?>" 
						data-form=".form-iqb-general">
						<i class="fa fa-map-marker"></i>
						<span>Edit Contact</span></a>
				</li>
				<li>
					<a href="#" 
						title="Edit Profile" 
						class="trg-dialog-edit" 
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Profile' 
						data-url="<?php echo site_url('users/update_profile/' . $record->id);?>" 
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Profile</span></a>
				</li>

				<li class="divider"></li>
				<li>
					<a href="#" 
						title="Change Password" 
						class="trg-dialog-edit" 
						data-title='<i class="fa fa-pencil-square-o"></i> Change Password' 
						data-url="<?php echo site_url('users/change_password/' . $record->id);?>" 
						data-form=".form-iqb-general">
						<i class="fa fa-lock"></i>
						<span>Change Password</span></a>
				</li>
				<li class="divider"></li>

				<?php if(safe_to_delete( 'User_model', $record->id )):?>
					<li>
						<?php
						$ban_unban_uri = $record->banned ? 'users/unban/' : 'users/ban/';
						$ban_unban_title = $record->banned ? 'Unban User' : 'Ban User';
						$ban_unban_icon = $record->banned ? 'fa-eye-slash' : 'fa-eye';
						?>
						<a href="#" 
							title="<?php echo $ban_unban_title;?>" 						
							class="trg-row-action"
							data-confirm="true"
							data-message="Are you sure you want to perform this action?"
							data-url="<?php echo site_url($ban_unban_uri . $record->id);?>">
								<i class="fa <?php echo $ban_unban_icon;?>"></i>
								<span><?php echo $ban_unban_title;?></span></a>
					</li>
					<li>
						<a href="#" 
							title="Delete" 						
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('users/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
					<li class="divider"></li>
				<?php endif?>				
				
				<li>
					<a href="<?php echo site_url('users/details/' . $record->id);?>" 
						title="View user details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>

		
		

		

		

		

		
	</td>
</tr>