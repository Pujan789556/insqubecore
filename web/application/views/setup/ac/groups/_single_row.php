<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Group:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->parent_name ?? '-';?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit Account Group"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Account Group'
		data-url="<?php echo site_url('ac_account_groups/edit/' . $record->id);?>"
		data-form=".form-iqb-general"><?php echo $record->name;?></a></td>
	<td><?php echo $record->lft;?></td>
	<td><?php echo $record->rgt;?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Account Group"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Account Group'
			data-url="<?php echo site_url('ac_account_groups/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="#"
			data-toggle="tooltip"
			title="Move Account Group"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Move Account Group - <?php echo $record->name?>'
			data-url="<?php echo site_url('ac_account_groups/move/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-long-arrow-up"></i>
			<i class="fa fa-long-arrow-down"></i>
			<span class="hidden-xs">Move</span>
		</a>
		<?php if(safe_to_delete( 'Ac_account_group_model', $record->id )):?>
			<?php /*
			<a href="#"
				data-toggle="tooltip"
				title="Order Account Group"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Order Account Group - <?php echo $record->name?>'
				data-url="<?php echo site_url('ac_account_groups/order/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-exchange"></i>
				<span class="hidden-xs">Order</span>
			</a>*/?>

			<a href="#"
				title="Delete Single"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('ac_account_groups/delete/node/' . $record->id);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete Node</span>
			</a>
			<a href="#"
				title="Delete Entire Subtree"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('ac_account_groups/delete/subtree/' . $record->id);?>">
					<i class="fa fa-trash"></i>
					<span class="hidden-xs">Delete Sub-tree</span>
			</a>
		<?php endif?>
	</td>
</tr>