<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claim Schemes:  Single Row
*/
$_edit_url 	= $this->data['_url_base'] . '/edit/'  . $record->id;
$_del_url 	= $this->data['_url_base'] . '/delete/' . $record->id;
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit department"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Claim Scheme'
		data-url="<?php echo site_url($_edit_url);?>"
		data-form=".form-iqb-general"><?php echo $record->name;?></a></td>
	<td data-toggle="tooltip" data-html="true" title="<?php echo nl2br($record->description)?>"><?php echo substr($record->description, 0, 50) , '...';?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit department"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Claim Scheme'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<?php if(safe_to_delete( 'Claim_scheme_model', $record->id )):?>
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