<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branches:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
$_del_url 		= $this->data['_url_base'] . '/delete/' . $record->id;
$_detail_url 	= $this->data['_url_base'] . '/details/' . $record->id;
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<?php
		if($record->is_head_office)
		{
			echo '<i class="fa fa-circle text-green" title="Head Office" data-toggle="tooltip"></i>&nbsp;';
		}
		?>
		<a href="#"
		data-toggle="tooltip"
		title="Edit branch"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch'
		data-url="<?php echo site_url($_edit_url);?>"
		data-form=".form-iqb-general"><?php echo $record->name_en;?></a></td>
	<td><?php echo $record->name_np;?></td>
	<td><?php echo $record->code;?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit branch"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="<?php echo site_url($_detail_url);?>"
			data-toggle="tooltip"
			title="View branch details."
			class="action">
			<i class="fa fa-building-o"></i>
			<span class="hidden-xs">Details</span>
		</a>

		<?php if(safe_to_delete( 'Branch_model', $record->id )):?>
			<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url($_del_url);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
		<?php endif?>
	</td>
</tr>