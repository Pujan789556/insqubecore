<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Installment:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-policy_installments-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $record->policy_transaction_id;?></td>
	<td><?php echo $record->installment_date;?></td>
	<td>
		<?php if($record->flag_first == IQB_FLAG_ON): ?>
			<i class="fa fa-circle text-green" data-toggle="tooltip" title="First Installment for Transaction ID - <?php echo $record->policy_transaction_id;?>"></i>
		<?php
		else:
			echo '-';
		endif;?>


	</td>
	<td><?php echo $record->percent;?>%</td>
	<td><?php echo number_format( (float)$record->amt_total_premium, 2, '.', '');?></td>

	<td><?php echo _POLICY_INSTALLMENT_status_text($record->status, TRUE);?></td>
	<td class="ins-action">
		<?php
		/**
		 * Editable, Status Action
		 * ----------------------------
		 */
		$this->load->view('policy_installments/snippets/_status_actions', ['record' => $record, 'policy_record' => $policy_record]);
		?>
	</td>
</tr>