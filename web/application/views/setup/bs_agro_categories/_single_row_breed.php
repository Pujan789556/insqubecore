<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Beema Samiti - Agro Categories:  Single Row By Portfolio
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code;?></td>
	<td><a href="<?php echo site_url('bs_agro_categories/breeds/' . $record->id);?>"
						title="View Breed Details."><?php echo $record->name_en;?></a></td>

	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Headings" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Manage Breeds"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> Manage Breeds - <?php echo $record->name_en, '(', $record->code, ')'; ?>'
						data-url="<?php echo site_url('bs_agro_categories/edit_breed/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Manage Breeds</span></a>
				</li><li class="divider"></li>

				<li>
					<a href="#"
						class="action trg-dialog-popup"
						data-toggle="tooltip"
						data-box-size="large"
						data-url="<?php echo site_url('bs_agro_categories/breeds/' . $record->id); ?>"
						title="View FAC Distribution"><i class="fa fa-search"></i> <span>View Breed Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>