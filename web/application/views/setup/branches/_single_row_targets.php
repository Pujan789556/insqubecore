<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->fiscal_yr_id;?>" class="searchable" data-id="<?php echo $record->fiscal_yr_id; ?>" id="_data-row-<?php echo $record->fiscal_yr_id;?>">
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit branch target"
		class="trg-dialog-edit"
		data-box-size="large"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch Target'
		data-url="<?php echo site_url('branches/edit_targets/' . $record->fiscal_yr_id);?>"
		data-form=".form-iqb-general"><?php echo $record->code_np . " ({$record->code_en})";?></a></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit branch target"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch Target'
			data-url="<?php echo site_url('branches/edit_targets/' . $record->fiscal_yr_id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<a href="<?php echo site_url('branches/target_details/' . $record->fiscal_yr_id);?>"
			data-toggle="tooltip"
			title="Manage target details."
			class="action">
			<i class="fa fa-list"></i>
			<span class="hidden-xs">Details</span>
		</a>

		<?php if(safe_to_delete( 'Branch_target_model', $record->fiscal_yr_id )):?>
			<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('branches/delete_targets/' . $record->fiscal_yr_id);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
		<?php endif?>
	</td>
</tr>