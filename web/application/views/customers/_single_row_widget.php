<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customers:  Single Row
*/

// ------------------------------------------------------------------------------

/**
 * Extract Widget Data
 */
$widget_data 	= explode(':', $widget_reference); // target-row-id:widget_type
$target_row_id 	= $widget_data[0];
$widget_type 	= $widget_data[1];


/**
 * Build Seletable JSON based on widget type
 */
$select_json = [];
if( $widget_type === 'party' )
{
	$select_json = [
		'fields' => [
			['ref' => 'party_id', 'val' => $record->id],
		],
		'html' => [
			['ref' => '_text-ref-party', 'val' => $record->full_name_en]
		]
	];
}
else if( $widget_type === 'policy' )
{
	$select_json = [
		'fields' => [
			['id' => 'customer-id', 'val' => $record->id],
			['id' => 'customer-text', 'val' => $record->full_name_en]
		],
		'html' => [
			['id' => '_text-ref-customer', 'val' => $record->full_name_en]
		]
	];
}
?>
<tr class="selectable pointer"
	data-selectable='<?php echo json_encode($select_json)?>'
	data-target-rowid='<?php echo $target_row_id?>'
	title="Select this customer."
	data-toggle="tooltip"
	onclick="__do_select(this)"
	id="_data-row-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<?php if($record->picture):?>
			<img
				class="thumbnail"
				style="width:100px; float:left; margin-right:10px;"
				src="<?php echo site_url('static/media/customers/'.thumbnail_name($record->picture));?>"
				alt="<?php echo $record->full_name_en;?>">
		<?php endif;?>
		<strong id="_text-ref-<?php echo $record->id?>"><?php echo $record->full_name_en;?></strong>
		<br/>
		Code: <strong><?php echo $record->code;?></strong><br/>
		PAN: <strong><?php echo $record->pan;?></strong>
	</td>
	<td>
		<i class="fa fa-phone-square margin-r-5"></i><?php echo $record->mobile_identity ?>
	</td>
	<td ><?php echo address_widget(parse_address_record($record), true, true)?></td>
	<td>
		<?php echo $record->type == 'I' ? 'Individual' : 'Company';?>
		<?php if($record->type == 'C'):?>
			<br/>Company Reg. No. <strong><?php echo $record->company_reg_no?></strong>
		<?php else:?>
			<br/>ID No.: <strong><?php echo $record->identification_no?></strong><br/>
			DOB: <strong><?php echo $record->dob?></strong>
		<?php endif?>
	</td>
	<td><?php echo locked_unlocked_text($record->flag_locked);?></td>
	<td>
		<?php
		if( $record->flag_kyc_verified == IQB_FLAG_ON )
		{
			$text = '<i class="fa fa-check text-green pointer", title="Verified" data-toggle="tooltip"></i>';
		}
		else
		{
			$text = '<i class="fa fa-minus text-muted pointer", title="Not Verified" data-toggle="tooltip"></i>';
		}
		echo $text;
		?>
	</td>
</tr>