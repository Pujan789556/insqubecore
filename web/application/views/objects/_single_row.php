<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Objects:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-object-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>

	<td>
		<?php echo $record->portfolio_name;?><br/>
		<em class="text-bold" data-toggle="tooltip" title="Sum Insured Amount">RS. <?php echo $record->amt_sum_insured;?></em>
	</td>

	<td><?php echo $record->customer_name;?></td>

	<?php echo _OBJ_row_snippet($record);?>

	<td class="ins-action" width="20%">
		<?php if( (int)$record->flag_locked === IQB_FLAG_UNLOCKED ):?>

			<?php if( $this->dx_auth->is_authorized('objects', 'edit.object') ): ?>
				<a href="#"
					title="Edit Basic Information"
					class="trg-dialog-edit action"
					data-box-size="full-width"
					data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information'
					data-url="<?php echo site_url('objects/edit/' . $record->id);?>"
					data-form="#_form-object">
					<i class="fa fa-pencil-square-o"></i>
					<span>Edit</span></a>
			<?php endif;?>

			<?php if( ($this->dx_auth->is_authorized('objects', 'delete.object')) && safe_to_delete( 'Object_model', $record->id )):?>
				<a href="#"
						title="Delete"
						class="trg-row-action action"
						data-confirm="true"
						data-url="<?php echo site_url('objects/delete/' . $record->id);?>">
							<i class="fa fa-trash-o"></i>
							<span>Delete</span></a>
			<?php endif?>

		<?php endif?>
	</td>
</tr>