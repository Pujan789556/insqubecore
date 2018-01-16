<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI Transactions:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-invoice-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id ;?></td>
		<?php endif;?>
	<td><?php echo $record->policy_code;?></td>
	<td><?php echo $record->treaty_type_name;?></td>
	<td class="ins-action">
		<?php echo anchor(
						'#',
						'<i class="fa fa-th-list"></i> Details',
						[
							'class' 		=> 'trg-dialog-popup action',
							'data-url' 		=> site_url('ri_transactions/details/'.$record->id),
							'data-box-size' => 'large',
						]);?>
	</td>
</tr>