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
	<td><?php echo voucher_complete_flag_text($record->flag_complete);?></td>
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


		<?php
		/**
		 * Internal Voucher - Policy Voucher to Generate Invoice
		 */
		if(
			// Must be Internal
			$record->flag_internal == IQB_FLAG_ON

				&&

			// Premium Income Voucher
			$record->voucher_type_id == IQB_AC_VOUCHER_TYPE_PRI

				&&

			// Must not Be Invoiced Yet
			isset($record->flag_invoiced) && (int)$record->flag_invoiced === IQB_FLAG_OFF

				&&

			// Must have Policy Transaction ID
			isset($record->policy_txn_id) && (int)$record->policy_txn_id !== IQB_FLAG_OFF

				&&

			// Has Permission
			$this->dx_auth->is_authorized('policy_txn', 'generate.policy.invoice')

		):?>
			<a href="#"
	            title="Generate Invoice"
	            data-toggle="tooltip"
	            data-confirm="true"
	            class="btn btn-sm btn-success btn-round trg-dialog-action"
	            data-message="Are you sure you want to Genrate Invoice for this policy?<br/>This will automatically generate INVOICE for this Policy."
	            data-url="<?php echo site_url('policy_txn/invoice/' . $record->policy_txn_id );?>"
	        ><i class="fa fa-list-alt"></i> Invoice</a>
		<?php endif;?>


	</td>
</tr>