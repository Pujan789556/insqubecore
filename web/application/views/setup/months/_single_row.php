<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Duties & Tax:  Single Row
*/
?>
<tr data-name="<?php echo $record->name_en;?>" class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->name_en;?></td>
	<td><?php echo $record->name_np;?></td>
</tr>