<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->id;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
	<td><?php echo _OBJ_MOTOR_ownership_dropdown(FALSE)[$record->ownership]?></td>
	<td>
		<?php
		echo $record->portfolio_name_en;
		echo $record->cvc_type ? '<br/><small>' . _OBJ_MOTOR_CVC_type_dropdown(FALSE)[$record->cvc_type] . '</small>' : '';
		?>
	</td>
	<td><?php echo  active_inactive_text($record->active);?></td>
	<td class="ins-action">
		<a href="#"
			data-toggle="tooltip"
			title="Edit Single Tariff"
			data-box-size="full-width"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Single Tariff'
			data-url="<?php echo site_url('tariff/motor/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>