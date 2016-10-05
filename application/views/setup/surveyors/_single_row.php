<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyors:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<a href="<?php echo site_url('surveyors/details/' . $record->id);?>" 
						title="View agent details.">
						<?php echo $record->name;?></a>
	</td>
	<td><?php echo $record->type;?></td>
	<td>
		<?php
		if($record->active)
		{
			$active_str = '<i class="fa fa-circle text-green" title="Active" data-toggle="tooltip"></i>';
		}
		else
		{
			$active_str = '<i class="fa fa-circle-thin" title="Not Active" data-toggle="tooltip"></i>';
		}
		echo $active_str;
		?>
	</td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#" 
						title="Edit Basic Information" 
						class="trg-dialog-edit" 
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information' 
						data-url="<?php echo site_url('surveyors/edit/' . $record->id);?>" 
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Surveyor Info</span></a>
				</li>
				

				<?php if(safe_to_delete( 'Surveyor_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#" 
							title="Delete" 						
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('surveyors/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
					<li class="divider"></li>
				<?php endif?>				
				
				<li>
					<a href="<?php echo site_url('surveyors/details/' . $record->id);?>" 
						title="View surveyor details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>

		
		

		

		

		

		
	</td>
</tr>