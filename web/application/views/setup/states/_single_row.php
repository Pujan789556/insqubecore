<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* States:  Single Row
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->country_name ?></td>
	<td><?php echo $record->code;?></td>
	<td><a href="#" title="Edit" class="trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Edit State' data-url="<?php echo site_url('states/edit/' . $record->id);?>" data-form=".form-iqb-general" data-toggle="tooltip"><?php echo $record->name_en;?></a></td>
	<td><?php echo $record->name_np;?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit State" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Edit State Information"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit State Information'
						data-url="<?php echo site_url('states/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit State Info</span></a>
				</li>

				<?php if(safe_to_delete( 'State_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('states/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
			</ul>
		</div>
	</td>
</tr>