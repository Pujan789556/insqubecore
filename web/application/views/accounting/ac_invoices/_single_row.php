<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Invoice:  Single Row
*/

// Redefine URL Base for this module as it is used by other modules
$this->data['_url_base'] = 'ac_invoices';
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-invoice-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id;?></td>
		<?php endif;?>
	<td><?php echo anchor($this->data['_url_base'] .'/details/'.$record->id, $record->invoice_code, ['target' => '_blank']);?></td>
	<td>
		<?php
		echo IQB_REL_POLICY_VOUCHER_REFERENCES[$record->ref] , ' ID: ' , $record->ref_id,
			 '<br/>',
			 "Voucher ID: " , $record->voucher_id;
		?>
	</td>
	<td><?php echo $record->branch_name_en;?></td>
	<td><?php echo $record->invoice_date;?></td>
	<td><?php echo invoice_flag_on_off_text($record->flag_paid);?></td>
	<td><?php echo invoice_flag_on_off_text($record->flag_printed);?></td>
	<td><?php echo invoice_flag_on_off_text($record->receipt_flag_printed);?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php
				$policy_id = $record->policy_id ?? ( $policy_id ?? NULL );
				if($policy_id):?>
					<li>
		                <a href="<?php echo site_url('policies/schedule/' . $policy_id  );?>"
		                    title="Print Schedule"
		                    target="_blank"
		                    data-toggle="tooltip">
		                    <i class="fa fa-print"></i> Print Schedule
		                </a>
	                </li><li class="divider"></li>
				<?php
				endif;

				/**
				 * Internal Voucher - Policy Voucher to Generate Invoice
				 */
				if(

					// Must not Be Paid Yet
					(int)$record->flag_paid === IQB_FLAG_OFF

						&&

					// Must have Endorsement ID
					// isset($record->policy_installment_id) && (int)$record->policy_installment_id !== IQB_FLAG_OFF

					// Must have Policy Installment ID
					isset($record->ref) && $record->ref === IQB_REL_POLICY_VOUCHER_REF_PI && (int)$record->ref_id !== IQB_FLAG_OFF

						&&

					// Has Permission
					$this->dx_auth->is_authorized('policy_installments', 'make.policy.payment')

				):?>
					<li>
						<a href="#"
				            title="Make Payment"
				            data-toggle="tooltip"
				            class="trg-dialog-edit"
				            data-form="#_form-payment"
				            data-box-size="large"
				            data-title='<i class="fa fa-pencil-square-o"></i> Make a Payment'
				            data-url="<?php echo site_url('policy_installments/payment/' . $record->ref_id  . '/' . $record->id );?>"
				        ><i class="fa fa-list-alt"></i> Make Payment</a>
			        </li><li class="divider"></li>
				<?php endif;?>


				<?php if( $this->dx_auth->is_authorized('ac_invoices', 'print.invoice') ): ?>
					<li>
		                <a href="<?php echo site_url($this->data['_url_base'] .'/print/invoice/' . $record->id)?>"
		                    title="Print Invoice"
		                    target="_blank"
		                    data-toggle="tooltip">
		                    <i class="fa fa-print"></i> Print Invoice
		                </a>
	                </li>

	                <?php if($record->flag_printed == IQB_FLAG_OFF):?>
		                <li>
			                <a href="#"
			                	data-toggle="tooltip"
								title="Mark as Invoice Printed"
								class="trg-row-action"
								data-confirm="true"
								data-message="Are you sure you want to do this? <br/>Once you do this, printing invoice gives a copy."
								data-url="<?php echo site_url($this->data['_url_base'] .'/printed/invoice/' . $record->id);?>">
									<i class="fa fa-check-square-o"></i> Mark as Invoice Printed</a>
						</li>
					<?php elseif($this->dx_auth->is_admin()): ?>
						<li>
			                <a href="#"
			                	data-toggle="tooltip"
								title="Remove Invoice Printed Flag"
								class="trg-row-action text-red"
								data-confirm="true"
								data-message="Are you sure you want to do this?"
								data-url="<?php echo site_url($this->data['_url_base'] .'/unprinted/invoice/' . $record->id);?>">
									<i class="fa fa-close"></i> Remove Invoice Printed Flag</a>
						</li>
					<?php endif;?><li class="divider"></li>
				<?php endif?>

				<?php if( (int)$record->flag_paid === IQB_FLAG_ON && $this->dx_auth->is_authorized('ac_invoices', 'print.receipt') ): ?>
					<li>
		                <a href="<?php echo site_url($this->data['_url_base'] .'/print/receipt/' . $record->id)?>"
		                    title="Print Receipt"
		                    target="_blank"
		                    data-toggle="tooltip">
		                    <i class="fa fa-print"></i> Print Receipt
		                </a>
	                </li>

	                <?php if( $record->receipt_flag_printed == IQB_FLAG_OFF):?>
		                <li>
			                <a href="#"
			                	data-toggle="tooltip"
								title="Mark as Receipt Printed"
								class="trg-row-action"
								data-confirm="true"
								data-message="Are you sure you want to do this? <br/>Once you do this, printing receipt gives a copy."
								data-url="<?php echo site_url($this->data['_url_base'] .'/printed/receipt/' . $record->id);?>">
									<i class="fa fa-check-square-o"></i> Mark as Receipt Printed</a>
						</li>
					<?php elseif($this->dx_auth->is_admin()): ?>
						<li>
			                <a href="#"
			                	data-toggle="tooltip"
								title="Remove Receipt Printed Flag"
								class="trg-row-action text-red"
								data-confirm="true"
								data-message="Are you sure you want to do this?"
								data-url="<?php echo site_url($this->data['_url_base'] .'/unprinted/receipt/' . $record->id);?>">
									<i class="fa fa-close"></i> Remove Receipt Printed Flag</a>
						</li>
					<?php endif?><li class="divider"></li>
		        <?php endif?>

		        <?php if( $this->dx_auth->is_authorized('ac_invoices', 'explore.invoice') ): ?>
		        	<li>
						<a href="<?php echo site_url($this->data['_url_base'] .'/details/' . $record->id);?>"
							data-toggle="tooltip" title="View invoice details.">
							<i class="fa fa-list-alt"></i>
							<span>View Details</span></a>
					</li>
				<?php endif?>
			</ul>
	</td>
</tr>