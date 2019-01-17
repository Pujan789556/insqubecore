<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year Months:  Single Row
*/

// --------------------------------------------------------------

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->id == $fy_record->id;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';

$_edit_url 		= $this->data['_url_base'] . '/edit/'  . $fy_record->id;
$_detail_url 	= $this->data['_url_base'] . '/details/' . $fy_record->id;
?>
<tr data-name="<?php echo $fy_record->code_np;?>"
	class="searchable <?php echo $current_class;?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $fy_record->id; ?>"
	id="_data-row-<?php echo $fy_record->id;?>">
	<td><?php echo $fy_record->id;?></td>
	<td><?php echo $fy_record->code_np;?></td>

	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit Headings" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>
					<a href="#"
						title="Fiscal Year Months"
						class="trg-dialog-edit"
						data-title='<i class="fa fa-pencil-square-o"></i> Fiscal Year Months - <?php echo $fy_record->code_np ?>'
						data-url="<?php echo site_url($_edit_url);?>"
						data-form=".form-iqb-general">
						<i class="fa fa-pencil-square-o"></i>
						<span>Manage Months</span></a>
				</li><li class="divider"></li>

				<li>
					<a href="#"
						class="action trg-dialog-popup"
						data-toggle="tooltip"
						data-box-size="large"
						data-url="<?php echo site_url($_detail_url); ?>"
						title="View FAC Distribution"><i class="fa fa-search"></i> <span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>