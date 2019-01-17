<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Roles:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/' . $record->id;
$_perm_url 		= $this->data['_url_base'] . '/permissions/' . $record->id;
$_del_url 		= $this->data['_url_base'] . '/delete/'  . $record->id;

?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<?php if($record->id != 2):?>
			<a 	href="#"
				title="Edit"
				class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit Role' data-url="<?php echo site_url('roles/edit/' . $record->id);?>" data-form=".form-iqb-general"><?php echo $record->name;?></a>
		<?php else:?>
			<?php echo $record->name;?>
		<?php endif;?>
	</td>
	<td><?php echo $record->description;?></td>
	<td class="ins-action">
		<?php if($record->id != 2):?>
			<a href="#"
				title="Edit"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Role'
				data-url="<?php echo site_url($_edit_url);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-pencil-square-o"></i>
				<span class="hidden-xs">Edit</span></a>

			<a href="#"
				title="Manage Role Permissions"
				data-title='<i class="fa fa-pencil-square-o"></i> Manage Role Permissions - <?php echo $record->name;?>'
				data-toggle="tooltip"
				data-box-size="large"
				data-form=".form-iqb-general"
				class="trg-dialog-edit action"
				data-url="<?php echo site_url($_perm_url);?>">
					<i class="fa fa-lock"></i>
					<span class="hidden-xs">Permission</span></a>

			<?php if(safe_to_delete( 'Role_model', $record->id )):?>
				<a href="#"
					title="Delete"
					data-toggle="tooltip"
					class="trg-row-action action"
					data-confirm="true"
					data-url="<?php echo site_url($_del_url);?>">
						<i class="fa fa-trash-o"></i>
						<span class="hidden-xs">Delete</span></a>
			<?php endif?>
		<?php endif?>
	</td>
</tr>