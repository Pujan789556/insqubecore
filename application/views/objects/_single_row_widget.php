<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customers:  Single Row
*/
?>
<tr class="selectable pointer"
	data-id="<?php echo $record->id?>"
	data-id-box="#object-id"
	data-text="<?php echo $record->portfolio_name?>"
	data-text-box="#object-text"
	data-text-box-ref="#_text-ref-<?php echo $record->id?>"
	title="Select this object."
	onclick="__do_select(this)"
	id="_data-row-object-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $record->portfolio_name;?></td>
	<td><?php echo $record->customer_name;?></td>
	<?php echo _PO_row_snippet($record);?>
</tr>