<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer Branch:  Single Row
*/
$_edit_url 		= $this->data['_url_base'] . '/branch/edit/'  . $record->company_id . '/' . $record->id;
$_del_url 		= $this->data['_url_base'] . '/branch/delete/' . $record->company_id . '/' . $record->id;
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-company-branch-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td>
		<?php
		if($record->is_head_office)
		{
			echo '<i class="fa fa-circle text-green" title="Head Office" data-toggle="tooltip"></i>&nbsp;';
		}
		echo $record->name_en, ' (', $record->name_np, ')';
		?>
	</td>
	<td><?php echo address_widget( parse_address_record($record), true)?></td>
	<td class="ins-action">
		<?php if( $this->dx_auth->is_authorized('companies', 'edit.company.branch') ): ?>
			<a href="#"
				title="Edit Branch Information"
				class="trg-dialog-edit action"
				data-box-size="large"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch Information'
				data-url="<?php echo site_url($_edit_url);?>"
				data-form="#_form-company-branch">
				<i class="fa fa-pencil-square-o"></i>
				<span>Edit</span></a>
		<?php endif;?>

		<?php if( ($this->dx_auth->is_authorized('companies', 'delete.company.branch')) && safe_to_delete( 'Company_branch_model', $record->id )):?>
			<a href="#"
					title="Delete"
					class="trg-row-action action"
					data-confirm="true"
					data-url="<?php echo site_url($_del_url);?>">
						<i class="fa fa-trash-o"></i>
						<span>Delete</span></a>
		<?php endif?>
	</td>
</tr>