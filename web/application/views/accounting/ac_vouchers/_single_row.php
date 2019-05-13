<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vouchers:  Single Row
*/
// Redefine URL Base for this module as it is used by other modules
$this->data['_url_base'] = 'ac_vouchers';
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-voucher-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id;?></td>
		<?php endif;?>
	<td><?php echo anchor($this->data['_url_base'] . '/details/'.$record->id, $record->voucher_code, ['target' => '_blank']);?></td>
	<td>
		<?php
		if(isset($record->ref))
		{
			echo IQB_REL_POLICY_VOUCHER_REFERENCES[$record->ref], ' (ID: ', $record->ref_id, ')';
		}
		?>
	</td>
	<td><?php echo $record->branch_name_en;?></td>
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
				data-url="<?php echo site_url($this->data['_url_base'] . '/edit/' . $record->id);?>"
				data-form=".form-iqb-general">
				<i class="fa fa-pencil-square-o"></i>
				<span class="hidden-xs">Edit</span>
			</a>
		<?php endif;?>


		<?php
		/**
		 * Internal Voucher - Generate Invoice
		 */
		if( is_invoicable_policy_voucher($record, FALSE) ):?>
			<a href="#"
	            title="Generate Invoice"
	            data-toggle="tooltip"
	            data-confirm="true"
	            class="btn btn-sm btn-success btn-round trg-dialog-action"
	            data-message="Are you sure you want to Genrate Invoice for this policy?<br/>This will automatically generate INVOICE for this Transaction."
	            data-url="<?php echo site_url('policy_installments/invoice/' . $record->ref_id  . '/' . $record->id );?>"
	        ><i class="fa fa-list-alt"></i> Invoice</a>
		<?php endif;?>

		<?php
		/**
		 * Internal Voucher - Generate Credit Note
		 */
		if( is_creditable_policy_voucher($record, FALSE) ):?>
			<a href="#"
	            title="Generate Credit Note"
	            data-toggle="tooltip"
	            data-confirm="true"
	            class="btn btn-sm btn-success btn-round trg-dialog-action"
	            data-message="Are you sure you want to Genrate Credit Note for this policy?<br/>This will automatically generate Credit Note for this Transaction."
	            data-url="<?php echo site_url('policy_installments/credit_note/' . $record->ref_id  . '/' . $record->id );?>"
	        ><i class="fa fa-list-alt"></i> Credit Note</a>
		<?php endif;?>


	</td>
</tr>