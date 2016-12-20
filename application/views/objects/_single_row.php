<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Objects:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-object-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $record->portfolio_name;?></td>
	<td><?php echo $record->customer_name;?></td>
	<?php echo _PO_row_snippet($record);?>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php if( $this->dx_auth->is_authorized('objects', 'edit.object') ): ?>
					<li>
						<a href="#"
							title="Edit Basic Information"
							class="trg-dialog-edit"
							data-box-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information'
							data-url="<?php echo site_url('objects/edit/' . $record->id);?>"
							data-form="#_form-object">
							<i class="fa fa-pencil-square-o"></i>
							<span>Edit Object Info</span></a>
					</li>
				<?php endif;?>

				<?php if( ($this->dx_auth->is_authorized('objects', 'delete.object')) && safe_to_delete( 'Object_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('objects/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
				<?php endif?>
			</ul>
		</div>
	</td>
</tr>