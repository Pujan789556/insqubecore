<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Districts:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_dst-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code;?></td>
	<td><?php echo $record->state_name_en;?></td>
	<td><?php echo $record->region_name_en;?></td>
	<td><a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit District' data-url="<?php echo site_url($_edit_url);?>" data-form=".form-iqb-general" data-toggle="tooltip"><?php echo $record->name_en;?></a></td>
	<td><?php echo $record->name_np;?></td>
	<td class="ins-action">
		<a href="#" title="Edit" class="trg-dialog-edit action" data-title='<i class="fa fa-pencil-square-o"></i> Edit District' data-url="<?php echo site_url($_edit_url);?>" data-form=".form-iqb-general" data-toggle="tooltip">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>