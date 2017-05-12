<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Account:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<?php
		$path_str = [];
		if( count($record->acg_path) > 2 )
		{
			array_shift($record->acg_path); // Remove "Chart of Account"
			foreach($record->acg_path as $path)
			{
				$path_str[]=$path->name;
			}
		}
		else
		{
			$path_str[] = $record->group_name;
		}

		echo implode('<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>', $path_str);
		?>
	</td>
	<td><?php echo $record->name;?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Record" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Edit Account"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Account'
						data-url="<?php echo site_url('ac_accounts/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Account</span></a>
				</li>

				<?php if(safe_to_delete( 'Ac_account_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('ac_accounts/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
			</ul>
		</div>
	</td>
</tr>