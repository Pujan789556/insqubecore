<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties:  Single Row
*/
$select_json = [
	'fields' => [
		['ref' => 'party_id', 'val' => $record->id],
	],
	'html' => [
		['ref' => '_text-ref-party', 'val' => $record->full_name]
	]
];
?>
<tr class="selectable pointer"
	data-selectable='<?php echo json_encode($select_json)?>'
	data-target-rowid='<?php echo $widget_reference?>'
	title="Select this party."
	data-toggle="tooltip"
	onclick="__do_select(this)"
	id="_data-row-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<strong id="_text-ref-<?php echo $record->id?>"><?php echo $record->full_name;?></strong>
		<br/>
		PAN: <strong><?php echo $record->pan;?></strong>
	</td>
	<td ><?php echo get_contact_widget($record->contact, true, true)?></td>
	<td>
		<?php echo $record->type == 'I' ? 'Individual' : 'Company';?>
		<?php if($record->type == 'C'):?>
			<br/>Company Reg. No. <strong><?php echo $record->company_reg_no?></strong>
		<?php else:?>
			<br/>Citizenship No. <strong><?php echo $record->citizenship_no?></strong><br/>
			Passport No. <strong><?php echo $record->passport_no?></strong>
		<?php endif?>
	</td>
</tr>