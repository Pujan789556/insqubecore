<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Credit Note:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-credit_note-<?php echo $record->id;?>">
	<td><?php echo anchor('ac_credit_notes/details/'.$record->id, $record->id, ['target' => '_blank']);?></td>
	<td>
		<?php
		echo IQB_REL_POLICY_VOUCHER_REFERENCES[$record->ref] , ' ID: ' , $record->ref_id,
			 '<br/>',
			 "Voucher ID: " , $record->voucher_id;
		?>
	</td>
	<td><?php echo $record->branch_name;?></td>
	<td><?php echo $record->credit_note_date;?></td>
	<td><?php echo credit_note_complete_flag_text($record->flag_complete);?></td>
	<td><?php echo credit_note_flag_on_off_text($record->flag_paid);?></td>
	<td><?php echo credit_note_flag_on_off_text($record->flag_printed);?></td>
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
		                    title="Print Policy Schedule"
		                    target="_blank"
		                    data-toggle="tooltip">
		                    <i class="fa fa-print"></i> Print Policy Schedule
		                </a>
	                </li><li class="divider"></li>
				<?php
				endif;

				/**
				 * Internal Voucher - Policy Voucher to Generate Credit Note
				 */
				if(

					// Must be Complete
					$record->flag_complete == IQB_FLAG_ON

						&&

					// Must not Be Paid Yet
					(int)$record->flag_paid === IQB_FLAG_OFF

						&&

					// Must have Endorsement ID
					// isset($record->policy_installment_id) && (int)$record->policy_installment_id !== IQB_FLAG_OFF

					// Must have Policy Installment ID
					isset($record->ref) && $record->ref === IQB_REL_POLICY_VOUCHER_REF_PI && (int)$record->ref_id !== IQB_FLAG_OFF

						&&

					// Has Permission
					$this->dx_auth->is_authorized('policy_installments', 'make.policy.refund')

				):?>
					<li>
						<a href="#"
				            title="Make Refund"
				            data-toggle="tooltip"
				            class="trg-dialog-edit"
				            data-form="#_form-refund"
				            data-box-size="large"
				            data-title='<i class="fa fa-pencil-square-o"></i> Complete Refund'
				            data-url="<?php echo site_url('policy_installments/refund/' . $record->ref_id  . '/' . $record->id );?>"
				        ><i class="fa fa-list-alt"></i> Refund</a>
			        </li><li class="divider"></li>
				<?php endif;?>


				<?php if( $this->dx_auth->is_authorized('ac_credit_notes', 'print.credit_note') ): ?>
					<li>
		                <a href="<?php echo site_url('ac_credit_notes/print/' . $record->id)?>"
		                    title="Print Credit Note"
		                    target="_blank"
		                    data-toggle="tooltip">
		                    <i class="fa fa-print"></i> Print Credit Note
		                </a>
	                </li>

	                <?php if($record->flag_printed == IQB_FLAG_OFF):?>
		                <li>
			                <a href="#"
			                	data-toggle="tooltip"
								title="Mark as Credit Note Printed"
								class="trg-row-action"
								data-confirm="true"
								data-message="Are you sure you want to do this? <br/>Once you do this, printing credit_note gives a copy."
								data-url="<?php echo site_url('ac_credit_notes/printed/credit_note/' . $record->id);?>">
									<i class="fa fa-check-square-o"></i> Mark as Printed</a>
						</li>
					<?php endif;?><li class="divider"></li>
				<?php endif?>

		        <?php if( $this->dx_auth->is_authorized('ac_credit_notes', 'explore.credit_note') ): ?>
		        	<li>
						<a href="<?php echo site_url('ac_credit_notes/details/' . $record->id);?>"
							data-toggle="tooltip" title="View credit_note details.">
							<i class="fa fa-list-alt"></i>
							<span>View Details</span></a>
					</li>
				<?php endif?>
			</ul>
	</td>
</tr>