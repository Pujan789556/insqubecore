<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-endorsements-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<?php if($record->flag_current == IQB_FLAG_ON): ?>
			<i class="fa fa-circle text-green" data-toggle="tooltip" title="Current Endorsement"></i>
		<?php endif;?>
		<?php echo _ENDORSEMENT_type_text($record->txn_type);?>
	</td>
	<td><?php echo $record->sold_by_username; ?></td>
	<td><?php echo $record->agent_name; ?></td>
	<td><?php echo active_inactive_text($record->flag_ri_approval);?></td>
	<td><?php echo _ENDORSEMENT_status_text($record->status);?></td>
	<td class="ins-action">
		<?php
		/**
		 * Editable, Status Action
		 * ----------------------------
		 */
		$this->load->view('endorsements/snippets/_status_actions', ['record' => $record]);
		?>
	</td>
</tr>