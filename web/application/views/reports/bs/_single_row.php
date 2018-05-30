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
	<td><?php echo IQB_BS_REPORT_CATEGORIES[$record->category];?></td>
	<td><?php echo IQB_BS_REPORT_TYPES[$record->type];?></td>
	<td><?php echo $record->fy_code_np, ' (', $record->fy_code_en, ')';?></td>
	<td><?php echo $record->type === IQB_BS_REPORT_TYPE_MONTHLY
						? nepali_month_dropdown(FALSE)[$record->fy_quarter_month]
						: fiscal_year_quarters_dropdown(false)[$record->fy_quarter_month];?></td>
	<td>
		<?php if($record->status): ?>
			<a href="<?php echo site_url('bs_reports/download/'. $record->id) ?>" target="_blank"> <i class="fa fa-download"></i> Download</a>
		<?php else: ?>
			<span data-toggle="tooltip" title="Request queued for report generation.">Queued</span>
		<?php endif; ?>
	</td>

	<td class="ins-action">
		<?php if(!$record->status): ?>

			<?php if($this->dx_auth->is_authorized('bs_reports', 'edit.bs.report')): ?>
				<a href="#"
					data-toggle="tooltip"
					title="Edit report"
					class="trg-dialog-edit action"
					data-title='<i class="fa fa-pencil-square-o"></i> Edit Beema Samiti Report'
					data-url="<?php echo site_url('bs_reports/edit/' . $record->id);?>"
					data-form=".form-iqb-general">
					<i class="fa fa-pencil-square-o"></i>
					<span class="hidden-xs">Edit</span>
				</a>
			<?php endif ?>

			<?php if(safe_to_delete( 'Bs_report_model', $record->id ) && $this->dx_auth->is_authorized('bs_reports', 'delete.bs.report') ):?>
				<a href="#"
					title="Delete"
					data-toggle="tooltip"
					class="trg-row-action action"
					data-confirm="true"
					data-url="<?php echo site_url('bs_reports/delete/' . $record->id);?>">
						<i class="fa fa-trash-o"></i>
						<span class="hidden-xs">Delete</span>
				</a>
			<?php endif?>
		<?php endif?>
	</td>
</tr>