<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->id;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
	<td><?php echo _PO_MOTOR_ownership_dropdown(FALSE)[$record->ownership]?></td>
	<td><?php echo _PO_MOTOR_sub_portfolio_dropdown(FALSE)[$record->sub_portfolio_code]?></td>
	<td><?php echo $record->cvc_type ? _PO_MOTOR_CVC_type_dropdown(FALSE)[$record->cvc_type] : '-'?></td>
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
		<a href="#"
			data-toggle="tooltip"
			title="Edit Single Tariff"
			data-box-size="large"
			class="trg-dialog-edit action"
			data-title='<i class="fa fa-pencil-square-o"></i> Edit Single Tariff'
			data-url="<?php echo site_url('tariff/motor/edit/' . $record->id);?>"
			data-form=".form-iqb-general">
			<i class="fa fa fa-pencil-square-o"></i>
			<span class="hidden-xs">Edit</span>
		</a>
	</td>
</tr>