<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vouchers:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id;?></td>
		<?php endif;?>
	<td><?php echo anchor('ac_vouchers/details/'.$record->id, $record->voucher_code);?></td>
	<td><?php echo $record->branch_name;?></td>
	<td><?php echo $record->voucher_type_name;?></td>
	<td><?php echo $record->voucher_date;?></td>
	<td class="ins-action">
		<?php if(

			// Permission?
			$this->dx_auth->is_authorized('ac_vouchers', 'edit.voucher')

			&&

			// Belongs to me?
			belongs_to_me($record->branch_id, FALSE)

			&&

			// Editable?
			is_voucher_editable($record, FALSE)

			):?>
			<a href="#"
				data-toggle="tooltip"
				title="Edit Voucher"
				class="trg-dialog-edit action"
				data-box-size="full-width"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Voucher'
				data-url="<?php echo site_url('ac_vouchers/edit/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-pencil-square-o"></i>
				<span class="hidden-xs">Edit</span>
			</a>
		<?php endif;?>
	</td>
</tr>