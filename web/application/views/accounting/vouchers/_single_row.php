<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Vouchers:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id;?></td>
		<?php endif;?>
	<td><?php echo anchor('ac_vouchers/details/'.$record->id, $record->voucher_code);?></td>
	<td><?php echo $record->branch_name;?></td>
	<td><?php echo $record->voucher_type_name;?></td>
	<td><?php echo $record->voucher_date;?></td>
</tr>