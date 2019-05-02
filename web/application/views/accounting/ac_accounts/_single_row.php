<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounts:  Single Row
*/

$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
$_del_url 		= $this->data['_url_base'] . '/delete/' . $record->id;
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td> <?php echo ac_account_group_path_formatted($record->acg_path);?> </td>
	<td><?php echo $record->name;?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Record" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">

				<?php if( $this->dx_auth->is_authorized('ac_accounts', 'edit.account') ): ?>
					<li>
						<a href="#"
							title="Edit Account"
							class="trg-dialog-edit"
							data-title='<i class="fa fa-pencil-square-o"></i> Edit Account'
							data-url="<?php echo site_url($_edit_url);?>"
							data-form=".form-iqb-general">
							<i class="fa fa-pencil-square-o"></i>
							<span>Edit Account</span></a>
					</li>
				<?php endif;?>

				<?php if($this->dx_auth->is_authorized('ac_accounts', 'delete.account') && safe_to_delete( 'Ac_account_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url($_del_url);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
			</ul>
		</div>
	</td>
</tr>