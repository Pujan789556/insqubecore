<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Duties & Tax:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><a href="#"
		data-toggle="tooltip"
		title="Edit department"
		class="trg-dialog-edit"
		data-title='<i class="fa fa-pencil-square-o"></i> Edit Duties & Tax'
		data-url="<?php echo site_url('ac_duties_and_tax/edit/' . $record->id);?>"
		data-form=".form-iqb-general"><?php echo $record->name;?></a></td>
	<td><?php echo $record->rate;?>%</td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit department"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Duties & Tax'
			data-url="<?php echo site_url('ac_duties_and_tax/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>