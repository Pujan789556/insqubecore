<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Single Row
*/
?>
<tr class="searchable"
	data-id="<?php echo $record->id; ?>"
	id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->name;?></td>
</tr>