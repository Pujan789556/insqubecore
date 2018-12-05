<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<a href="<?php echo site_url('companies/details/' . $record->id);?>"
						title="View company details.">
						<?php echo $record->name_en;?></a>
	</td>
	<td><?php echo $record->pan_no;?></td>
	<td><?php echo _COMPANY_type_dropdown(FALSE)[$record->type];?></td>
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
						data-url="<?php echo site_url('companies/edit/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Edit Company Info</span></a>
				</li><li class="divider"></li>

				<?php if(safe_to_delete( 'Company_model', $record->id )):?>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('companies/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li><li class="divider"></li>
				<?php endif?>

				<li>
					<a href="<?php echo site_url('companies/details/' . $record->id);?>"
						title="View company details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>