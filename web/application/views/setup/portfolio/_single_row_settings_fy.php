<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
$title = $record->portfolio_parent_name . ' - ' . $record->portfolio_name;

$_edit_url 		= $this->data['_url_base'] . '/edit_settings/'  . $record->id;
$_edit_spr_url 	= $this->data['_url_base'] . '/configure_settings_spr/'  . $record->id;
?>
<tr data-name="<?php echo $record->id;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $title ?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Settings <?php echo $title ?>"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Portfolio Settings - <?php echo $title ?>'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
		<?php if($record->flag_short_term === IQB_FLAG_YES ): ?>
			<a href="#"
				data-toggle="tooltip"
				title="Configure Short Term Policy Rate"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Configure Short Term Policy Rate - <?php echo $title ?>'
				data-url="<?php echo site_url($_edit_spr_url);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-pencil-square-o"></i>
				<span class="hidden-xs">Edit SPR</span>
			</a>
		<?php endif ?>
	</td>
</tr>