<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pools :  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<a href="<?php echo site_url('ri_setup_pools/details/' . $record->id);?>" title="View Pool Treaty Details">
			<?php echo $record->name;?>
		</a>
	</td>
	<td><?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Record" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Edit Pool Treaty"
						class="trg-dialog-edit"
						data-box-size="large"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Pool Treaty'
						data-url="<?php echo site_url('ri_setup_pools/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Pool Treaty</span></a>
				</li>

				<?php if(safe_to_delete( 'Ri_setup_pool_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('ri_setup_pools/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
				<li class="divider"></li>
				<li>
					<a href="<?php echo site_url('ri_setup_pools/details/' . $record->id);?>"
						title="View details.">
						<i class="fa fa-list-alt"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>