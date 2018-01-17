<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Template:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<a href="<?php echo site_url('endorsement_templates/details/' . $record->id);?>"
						title="View agent details.">
						<?php echo $record->portfolio_name_en;?></a>
	</td>
	<td><?php echo get_policy_transaction_type_text($record->endorsement_type);?></td>
	<td><?php echo $record->title; ?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Edit Template Information"
						class="trg-dialog-edit"
						data-box-size="large"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Template Information'
						data-url="<?php echo site_url('endorsement_templates/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Endorsement Info</span></a>
				</li><li class="divider"></li>
				<?php if(safe_to_delete( 'Endorsement_template_model', $record->id )):?>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('endorsement_templates/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li><li class="divider"></li>
				<?php endif?>

				<li>
					<a href="<?php echo site_url('endorsement_templates/details/' . $record->id);?>"
						title="View agent details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>