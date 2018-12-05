<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-policy-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
		<td><?php echo branch_dropdown('en', FALSE)[$record->branch_id] ?></td>
	<?php endif?>
	<td>
		<a href="<?php echo site_url('policies/details/' . $record->id);?>"
			target="_blank"
			title="View policy details.">
			<?php echo $record->code;?></a>
	</td>
	<td><?php echo $record->customer_name_en;?></td>
	<td><?php echo $record->portfolio_name;?></td>

	<td><?php echo $record->start_date . ' - ' . $record->end_date;?></td>
	<td><?php echo $record->created_at; ?></td>
	<td><?php echo $record->updated_at; ?></td>
	<td><?php echo _POLICY_status_icon($record->status) . ' ' ._POLICY_status_text($record->status);?></td>

	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php if( $record->status === IQB_POLICY_STATUS_DRAFT && $this->dx_auth->is_authorized('policies', 'edit.draft.policy') ): ?>
					<li>
						<a href="#"
							title="Edit Basic Information"
							class="trg-dialog-edit"
							data-box-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Edit Policy - <?php echo $record->code?>'
							data-url="<?php echo site_url('policies/edit/' . $record->id);?>"
							data-form="#_form-policy">
							<i class="fa fa-pencil-square-o"></i>
							<span>Edit Policy Info</span></a>
					</li>
				<?php endif;?>

				<?php if( $record->status === IQB_POLICY_STATUS_DRAFT && $this->dx_auth->is_authorized('policies', 'delete.policy') && safe_to_delete( 'Policy_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('policies/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
					<li class="divider"></li>
				<?php endif?>

				<li>
					<a href="<?php echo site_url('policies/details/' . $record->id);?>"
						target="_blank"
						title="View policy details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>