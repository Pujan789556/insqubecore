<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agents:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>

	<td><?php echo $record->name_en;?></td>
	<td><?php echo $record->name_np;?></td>
	<td>
		<?php
		$types = ['1' => 'Old', '2' => 'New 4-Wheeler', '3' => 'New 2-Wheeler'];
		echo $types[$record->type];
		?>
	</td>
	<td class="ins-action">
		<a href="#"
			title="Edit"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit'
			data-url="<?php echo site_url('vehicle_reg_prefix/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Edit</span></a>
		<?php if(safe_to_delete( 'Vehicle_reg_prefix_model', $record->id )):?>
			<a href="#"
				title="Delete"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('vehicle_reg_prefix/delete/' . $record->id);?>">
					<i class="fa fa-trash-o"></i>
					<span>Delete</span></a>
		<?php endif?>
	</td>
</tr>