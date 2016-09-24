<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code_np;?></td>
	<td><?php echo $record->code_en;?></td>

	<td><?php echo str_to_nepdate($record->starts_at_np);?></td>
	<td><?php echo str_to_nepdate($record->ends_at_np);?></td>

	<td><?php echo $record->starts_at_en;?></td>
	<td><?php echo $record->ends_at_en;?></td>
	
</tr>