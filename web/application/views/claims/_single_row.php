<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI Transactions:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-claims-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id ;?></td>
	<?php endif;?>
	<td><?php echo anchor('claims/details/' . $record->id, $record->claim_code, ['target' => '_blank']);?></td>
	<td><?php echo anchor('policies/details/' . $record->policy_id, $record->policy_code, ['target' => '_blank']);?></td>
	<td><?php echo $record->accident_date;?></td>
	<td><?php echo $record->intimation_date;?></td>
	<td><?php echo CLAIM__status_dropdown(FALSE)[$record->status];?></td>
	<td><?php echo yes_no_text_formatted($record->flag_paid);?></td>

	<td class="ins-action">
		<?php
		/**
		 * Editable, Status Action
		 * ----------------------------
		 */
		$this->load->view('claims/_status_actions', ['record' => $record, 'ref' => 'l']);
		?>
	</td>
</tr>