<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Transaction:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-policy_txn-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo get_policy_txn_type_text($record->txn_type);?></td>
	<td><?php echo active_inactive_text($record->flag_ri_approval);?></td>
	<td><?php echo get_policy_txn_status_text($record->status);?></td>
	<td class="ins-action">
		<?php
		/**
		 * Editable, Status Action
		 * ----------------------------
		 */
		$this->load->view('policy_txn/snippets/_status_actions', ['record' => $record, 'policy_record' => $policy_record]);
		?>
	</td>
</tr>