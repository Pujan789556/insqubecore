<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Beema Samiti - Agro Categories:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $portfolio->id;
$_detail_url 	= $this->data['_url_base'] . '/details/'  . $portfolio->id;
?>
<tr data-name="<?php echo $portfolio->name_en;?>" class="searchable" data-id="<?php echo $portfolio->id; ?>" id="_data-row-<?php echo $portfolio->id;?>">
	<td><?php echo $portfolio->id;?></td>
	<td><a href="<?php echo site_url($_detail_url);?>"
						title="View heading details."><?php echo $portfolio->name_en;?></a></td>

	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Headings" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Manage Beema Samiti - Agro Categories"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> BS Agro Categories - <?php echo $portfolio->name_en ?>'
						data-url="<?php echo site_url($_edit_url);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Manage Categories</span></a>
				</li><li class="divider"></li>

				<li>
					<a href="<?php echo site_url($_detail_url);?>"
						title="View heading details.">
						<i class="fa fa-list-alt"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>