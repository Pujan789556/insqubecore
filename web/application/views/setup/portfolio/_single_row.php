<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio:  Single Row
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->name_en;?></td>
	<td><?php echo $record->name_np;?></td>
	<td><?php echo $record->code;?></td>
	<td><?php echo $record->parent_name ?? '-';?></td>
	<td>
		<?php
		if( $record->file_toc )
		{
			echo anchor('downloads/get/portfolio/' . $record->file_toc, '<i class="fa fa-fw fa-download"></i> Download', 'target="_blank" title="Download terms & conditions document"');
		}
		else
		{
			echo '-';
		}
		?>
	</td>
	<td class="ins-action">
		<a href="#"
			title="Edit portfolio"
			data-toggle="tooltip"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio'
			data-url="<?php echo site_url('portfolio/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<?php if($record->parent_id): ?>
			<a href="#"
				title="Edit Portfolio Specific Default Internal Accounts"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Specific Internal Accounts - <?php echo $record->name_en?>'
				data-url="<?php echo site_url('portfolio/accounts/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-dollar"></i>
				<span class="hidden-xs">Accounts</span>
			</a>

			<a href="#"
				title="Edit Portfolio Specific Risks"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Specific Risks - <?php echo $record->name_en?>'
				data-url="<?php echo site_url('portfolio/risks/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-flag"></i>
				<span class="hidden-xs">Risks</span>
			</a>

			<a href="#"
				title="Edit Portfolio Specific Risks"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Specific Risks - <?php echo $record->name_en?>'
				data-url="<?php echo site_url('portfolio/risks_json/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-flag"></i>
				<span class="hidden-xs">Risks JSON</span>
			</a>

			<a href="#"
				title="Edit Portfolio Specific Beema Samiti Report Heading Type"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Specific Beema Samiti Report Heading Type - <?php echo $record->name_en?>'
				data-url="<?php echo site_url('portfolio/bsrs_headings/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-th-large"></i>
				<span class="hidden-xs">BS Headings</span>
			</a>
		<?php endif?>

		<?php if(safe_to_delete( 'Portfolio_model', $record->id )):?>
			<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('portfolio/delete/' . $record->id);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
		<?php endif?>
	</td>
</tr>