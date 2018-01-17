<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement Template:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->exchange_date; ?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Duplicate Forex"
						class="trg-dialog-edit"
						data-box-size="large"
						data-title='<i class="fa fa-pencil-square-o"></i> Edit Template Information'
						data-url="<?php echo site_url('forex/duplicate/' . $record->id);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-files-o"></i>
						<span>Duplicate Forex</span></a>
				</li><li class="divider"></li>
				<li>
					<?php echo anchor(
						'#',
						'<i class="fa fa-th-list"></i> Details',
						[
							'class' 		=> 'trg-dialog-popup',
							'data-url' 		=> site_url('forex/details/'.$record->id),
							'data-box-size' => 'large',
						]);?>
				</li>
			</ul>
		</div>
	</td>
</tr>