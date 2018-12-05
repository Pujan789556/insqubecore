<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Beema Samiti - Agro Categories - Breeds - Preview
*/
?>
<?php if($records): ?>
	<table class="table table-condensed table-bordered table-responsive">
		<thead>
			<tr>
				<th>ID</th>
				<th>Beema Samiti Code</th>
				<th>Name (EN)</th>
				<th>Name (NP)</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($records as $single): ?>
				<tr>
					<td><?php echo $single->id ?></td>
					<td><?php echo $single->code ?></td>
					<td><?php echo $single->name_en ?></td>
					<td><?php echo $single->name_np ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
<?php else: ?>
	<p class="text-muted small">No data found!</p>
<?php endif ?>