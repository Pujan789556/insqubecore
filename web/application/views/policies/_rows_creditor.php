<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy:  Creditors List per Policy
*/
foreach($creditors as $single):

	$row_id = '_policy-creditor-' . $single->policy_id . '-' . $single->creditor_id . '-' . $single->creditor_branch_id;
?>
	<tr class="searchable" id="<?php echo $row_id ?>">

		<td>
			<a href="<?php echo site_url('companies/details/' . $single->creditor_id);?>"
				target="_blank"
				title="View company details.">
				<?php echo $single->name_en;?></a>
		</td>
		<td><?php echo $single->branch_name;?></td>
		<td class="ins-action">
			<?php if( _POLICY_is_editable($policy_record->status, FALSE) ): ?>
				<a href="#"
					title="Edit Creditor Information"
					class="trg-dialog-edit action"
					data-box-size="large"
					data-title='<i class="fa fa-pencil-square-o"></i> Edit Creditor Information - <?php echo $policy_record->code?>'
					data-url="<?php echo site_url('policies/save_creditor/' . $single->policy_id . '/' . $single->creditor_id . '/' . $single->creditor_branch_id);?>"
					data-form="#_form-policy">
					<i class="fa fa-pencil-square-o"></i>
					<span>Edit</span></a>

				<a href="#"
					title="Delete"
					class="trg-row-action action"
					data-confirm="true"
					data-url="<?php echo site_url('policies/delete_creditor/' . $single->policy_id . '/' . $single->creditor_id . '/' . $single->creditor_branch_id);?>">
						<i class="fa fa-trash-o"></i>
						<span>Remove</span></a>
			<?php endif;?>
		</td>
	</tr>
<?php endforeach; ?>