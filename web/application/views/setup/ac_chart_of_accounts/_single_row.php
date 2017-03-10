<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agents:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->account_group_name;?></td>
	<td><?php echo $record->parent_name ?? '-';?></td>
	<td><?php echo $record->ac_number;?></td>
	<td><?php echo $record->name;?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Record" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Edit Account Heading"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Account Heading'
						data-url="<?php echo site_url('ac_chart_of_accounts/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Account Heading</span></a>
				</li>

				<?php if(safe_to_delete( 'Ac_chart_of_account_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('ac_chart_of_accounts/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
			</ul>
		</div>











	</td>
</tr>