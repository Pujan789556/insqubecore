<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Template:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->portfolio_name_en;?></td>
	<td><?php echo _ENDORSEMENT_type_text($record->endorsement_type);?></td>
	<td><?php echo $record->title; ?></td>
	<td class="ins-action">
		<?php echo anchor(
			'#',
			'<i class="fa fa-search"></i>',
			[
				'class' 		=> 'trg-dialog-popup action',
				'data-url' 		=> site_url('endorsement_templates/details/' . $record->id),
				'data-box-size' => 'large',
				'title' 		=> 'View Details',
				'data-toggle' 	=> 'tooltip'
			]);?>

		<a href="#"
			data-toggle="tooltip"
			title="Edit Template Information"
			class="action trg-dialog-edit"
			data-box-size="large"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Template Information'
			data-url="<?php echo site_url('endorsement_templates/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa-pencil-square-o"></i></a>

		<?php if(safe_to_delete( 'Endorsement_template_model', $record->id )):?>
			<a href="#"
				data-toggle="tooltip"
				title="Delete Record"
				class="action trg-row-action"
				data-confirm="true"
				data-url="<?php echo site_url('endorsement_templates/delete/' . $record->id);?>">
					<i class="fa fa-trash-o"></i></a>
		<?php endif?>
	</td>
</tr>