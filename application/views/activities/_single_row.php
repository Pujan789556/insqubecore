<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Single Row
*/
$activity = $this->activity->initialize($record);
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->user->username . ' ' . $activity->statement();?></td>
	<td><?php echo $record->created_at;?></td>
	<td><?php echo $record->created_by;?></td>
	
</tr>