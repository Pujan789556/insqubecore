<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
$title = $record->portfolio_parent_name . ' - ' . $record->portfolio_name;
?>
<tr data-name="<?php echo $record->id;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $title ?></td>
	<td class="ins-action">
		<?php if($record->flag_short_term === IQB_FLAG_YES ): ?>
			<a href="#"
				data-toggle="tooltip"
				title="Configure Short Term Policy Rate"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Configure Short Term Policy Rate - <?php echo $title ?>'
				data-url="<?php echo site_url('portfolio/configure_settings_spr/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-pencil-square-o"></i>
				<span class="hidden-xs">Edit SPR</span>
			</a>
		<?php endif ?>
	</td>
</tr>