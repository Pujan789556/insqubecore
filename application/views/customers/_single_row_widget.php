<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customers:  Single Row
*/
?>
<tr class="selectable pointer"
	data-id="<?php echo $record->id?>"
	data-id-box="#customer-id"
	data-text="<?php echo $record->full_name?>"
	data-text-box="#customer-text"
	title="Select this customer."
	onclick="__do_select(this)"
	id="_data-row-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<strong><?php echo $record->full_name;?></strong>
		<br/>
		Code: <strong><?php echo $record->code;?></strong><br/>
		PAN: <strong><?php echo $record->pan;?></strong>
	</td>
	<td ><?php $this->load->view('templates/_common/_widget_contact_snippet', ['contact' => json_decode($record->contact)]);?></td>
	<td>
		<?php echo $record->type == 'I' ? 'Individual' : 'Company';?>
		<?php if($record->type == 'C'):?>
			<br/>Company Reg. No. <strong><?php echo $record->company_reg_no?></strong>
		<?php else:?>
			<br/>Citizenship No. <strong><?php echo $record->citizenship_no?></strong><br/>
			Passport No. <strong><?php echo $record->passport_no?></strong>
		<?php endif?>
	</td>
	<td><?php echo $record->profession;?></td>
</tr>