<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Beema Samiti Report Setup - Headings:  Single Row
*/
?>
<tr data-name="<?php echo $portfolio->name;?>" class="searchable" data-id="<?php echo $portfolio->id; ?>" id="_data-row-<?php echo $portfolio->id;?>">
	<td><?php echo $portfolio->id;?></td>
	<td><a href="<?php echo site_url('bsrs_headings/details/' . $portfolio->id);?>"
						title="View heading details."><?php echo $portfolio->name;?></a></td>

	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Headings" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php foreach($heading_types as $type=>$type_name): ?>
					<li>
						<a href="#"
							title="Manage Beema Samiti Report Headings"
							class="trg-dialog-edit"
							data-title='<i class="fa fa-pencil-square-o"></i> BS Report Headings - <?php echo $portfolio->name , ' - ', $type_name ?>'
							data-url="<?php echo site_url('bsrs_headings/edit/' . $portfolio->id . '/'. $type);?>"
							data-form=".form-iqb-general">
							<i class="fa fa-pencil-square-o"></i>
							<span><?php echo $type_name ?></span></a>
					</li><li class="divider"></li>
				<?php endforeach; ?>

				<li>
					<a href="<?php echo site_url('bsrs_headings/details/' . $portfolio->id);?>"
						title="View heading details.">
						<i class="fa fa-list-alt"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>