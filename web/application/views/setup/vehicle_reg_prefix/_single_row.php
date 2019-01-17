<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vehicle Registration Prefix:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
$_del_url 		= $this->data['_url_base'] . '/delete/' . $record->id;
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
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span>Edit</span></a>
		<?php if(safe_to_delete( 'Vehicle_reg_prefix_model', $record->id )):?>
			<a href="#"
				title="Delete"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url($_del_url);?>">
					<i class="fa fa-trash-o"></i>
					<span>Delete</span></a>
		<?php endif?>
	</td>
</tr>