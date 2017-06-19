<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Invoice:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-invoice-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id;?></td>
		<?php endif;?>
	<td><?php echo anchor('ac_invoices/details/'.$record->id, $record->invoice_code, ['target' => '_blank']);?></td>
	<td><?php echo $record->branch_name;?></td>
	<td><?php echo $record->invoice_date;?></td>
	<td><?php echo invoice_complete_flag_text($record->flag_complete);?></td>
	<td class="ins-action">
		<?php
		/**
		 * Internal Voucher - Policy Voucher to Generate Invoice
		 */
		if(

			// Must be Complete
			$record->flag_complete == IQB_FLAG_ON

				&&

			// Must not Be Paid Yet
			(int)$record->flag_paid === IQB_FLAG_OFF

				&&

			// Must have Policy Transaction ID
			isset($record->policy_txn_id) && (int)$record->policy_txn_id !== IQB_FLAG_OFF

				&&

			// Has Permission
			$this->dx_auth->is_authorized('policy_txn', 'make.policy.payment')

		):?>
			<a href="#"
	            title="Make Payment"
	            data-toggle="tooltip"
	            data-confirm="true"
	            class="btn btn-sm btn-success btn-round trg-dialog-action"
	            data-message="Are you sure you want to Make Payment for this Invoice?"
	            data-url="<?php echo site_url('policy_txn/payment/' . $record->policy_txn_id  . '/' . $record->id );?>"
	        ><i class="fa fa-list-alt"></i> Payment</a>
		<?php endif;?>

		<?php if( $this->dx_auth->is_authorized('ac_invoices', 'print.invoice') ): ?>
                <a href="<?php echo site_url('ac_invoices/print/' . $record->id)?>"
                    title="Print Invoice"
                    class="btn btn-sm bg-navy btn-round"
                    target="_blank"
                    data-toggle="tooltip">
                    <i class="fa fa-print"></i> Print
                </a>
        <?php endif?>
	</td>
</tr>