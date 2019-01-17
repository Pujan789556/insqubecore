<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* TMI Plan:  Single Row
*/

$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $record->id;
$_med_url 		= $this->data['_url_base'] . '/tariff/m/' . $record->id;
$_pkg_url 		= $this->data['_url_base'] . '/tariff/p/' . $record->id;
$_benefit_url 	= $this->data['_url_base'] . '/benefits/'  . $record->id;

?>
<tr data-name="<?php echo $record->name;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code;?></td>
	<td><?php echo $record->name;?></td>
	<td><?php echo $record->parent_name ?? '-';?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<a href="#"
			title="Edit TMI Plan"
			data-toggle="tooltip"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit TMI Plan'
			data-url="<?php echo site_url($_edit_url);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>

		<?php if($record->parent_id): ?>
			<a href="#"
				title="Edit Medical Tariff"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Medical Tariff - <?php echo $record->name?> (<?php echo $record->parent_name ?>)'
				data-url="<?php echo site_url($_med_url);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-list"></i>
				<span class="hidden-xs">Medical Tariff</span>
			</a>

			<a href="#"
				title="Edit Package Tariff"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Package Tariff - <?php echo $record->name?> (<?php echo $record->parent_name ?>)'
				data-url="<?php echo site_url($_pkg_url);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-list"></i>
				<span class="hidden-xs">Package Tariff</span>

			<a href="#"
				title="Edit Schedule of Benefits"
				data-toggle="tooltip"
				data-box-size="large"
				class="trg-dialog-edit action"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Schedule of Benefits - <?php echo $record->name?> (<?php echo $record->parent_name ?>)'
				data-url="<?php echo site_url($_benefit_url);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-dollar"></i>
				<span class="hidden-xs">Benefits</span>
			</a>
		<?php endif?>
	</td>
</tr>