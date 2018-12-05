<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit Beema Samiti Report Setup - Heading Type"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Beema Samiti Report Setup - Heading Type'
		data-url="<?php echo site_url('bsrs_heading_types/edit/' . $record->id);?>"
		data-form=".form-iqb-general"><?php echo $record->name_en;?></a></td>
	<td><?php echo $record->name_np;?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Beema Samiti Report Setup - Heading Type"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Beema Samiti Report Setup - Heading Type'
			data-url="<?php echo site_url('bsrs_heading_types/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<?php if(safe_to_delete( 'Bsrs_heading_type_model', $record->id )):?>
			<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('bsrs_heading_types/delete/' . $record->id);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
		<?php endif?>
	</td>
</tr>