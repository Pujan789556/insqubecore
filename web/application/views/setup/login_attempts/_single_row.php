<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Departments:  Single Row
*/
?>
<tr data-name="<?php echo $record->ip_address;?>" class="searchable" data-id="<?php echo $record->ip_address; ?>" id="_data-row-<?php echo ip2long($record->ip_address);?>">

	<td><?php echo $record->ip_address;?></td>
	<td><?php echo $record->attemtps;?></td>
	<td class="ins-action">
		<a href="#"
				title="Delete"
				data-toggle="tooltip"
				class="trg-row-action action"
				data-confirm="true"
				data-url="<?php echo site_url('login_attempts/delete/' . $record->ip_address);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
	</td>
</tr>