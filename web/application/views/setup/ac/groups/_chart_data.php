<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Groups:  Data List
*/
?>
<table class="table table-hover no-border table-condensed" id="live-searchable" style="margin-left:20px; margin-bottom: 20px;">
	<thead>
		<tr>
			<th>Name</th>
		</tr>
	</thead>

	<tbody>
		<?php
		/**
		 * Render each record
		 */
		foreach($records as $record):?>
			<tr>
				<td style="padding-top:0">
					<?php echo $record->name;?>
				</td>
			</tr>
		<?php endforeach?>
	</tbody>

</table>