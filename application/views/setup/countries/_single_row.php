<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Countries:  Single Row
*/
?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit Country' data-url="<?php echo site_url('countries/edit/' . $record->id);?>" data-form=".form-iqb-general" data-toggle="tooltip"><?php echo $record->name;?></a></td>
	<td><?php echo $record->alpha2;?></td>
	<td><?php echo $record->alpha3;?></td>
	<td><?php echo $record->dial_code;?></td>
	<td><?php echo $record->currency_code;?></td>
	<td><?php echo $record->currency_name;?></td>
	<td>
		<a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit Country' data-url="<?php echo site_url('countries/edit/' . $record->id);?>" data-form=".form-iqb-general" data-toggle="tooltip">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>