<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer Branch:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-company-branch-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->name;?></td>
	<td><?php $this->load->view('templates/_common/_widget_contact_snippet', ['contact' => json_decode($record->contact)]);?></td>
	<td class="ins-action">
		<?php if( $this->dx_auth->is_authorized('companies', 'edit.company.branch') ): ?>
			<a href="#"
				title="Edit Branch Information"
				class="trg-dialog-edit action"
				data-box-size="large"
				data-title='<i class="fa fa-pencil-square-o"></i> Edit Branch Information'
				data-url="<?php echo site_url('companies/branch/edit/' . $record->company_id . '/' . $record->id);?>"
				data-form="#_form-company-branch">
				<i class="fa fa-pencil-square-o"></i>
				<span>Edit</span></a>
		<?php endif;?>

		<?php if( ($this->dx_auth->is_authorized('companies', 'delete.company.branch')) && safe_to_delete( 'Company_branch_model', $record->id )):?>
			<a href="#"
					title="Delete"
					class="trg-row-action action"
					data-confirm="true"
					data-url="<?php echo site_url('companies/branch/delete/' . $record->company_id . '/' . $record->id);?>">
						<i class="fa fa-trash-o"></i>
						<span>Delete</span></a>
		<?php endif?>
	</td>
</tr>