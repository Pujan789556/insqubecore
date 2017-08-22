<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Risk:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit Risk"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Risk'
		data-url="<?php echo site_url('risks/edit/' . $record->id);?>"
		data-form=".form-iqb-general"><?php echo $record->name;?></a></td>
	<td><?php echo risk_type_dropdown(FALSE)[$record->type];?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Risk"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Risk'
			data-url="<?php echo site_url('risks/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<?php if(safe_to_delete( 'Risk_model', $record->id )):?>
			<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('risks/delete/' . $record->id);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
		<?php endif?>
	</td>
</tr>