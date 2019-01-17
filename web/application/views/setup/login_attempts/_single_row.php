<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Login Attempts:  Single Row
*/
$_del_url 		= $this->data['_url_base'] . '/delete/'  . $record->ip_address;
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
				data-url="<?php echo site_url($_del_url);?>">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-xs">Delete</span>
			</a>
	</td>
</tr>