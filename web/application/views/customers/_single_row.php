<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customers:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-customer-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<?php if($record->picture):?>
			<img class="thumbnail ins-img-ip" style="width:100px; float:left; margin-right:10px;" src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo thumbnail_name($record->picture);?>" alt="<?php echo $record->full_name;?>" data-src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo $record->picture?>"
              	onclick="InsQube.imagePopup(this, 'Customer Picture')" title="Click here to view large" data-toggle="tooltip">
		<?php endif;?>
		<a href="<?php echo site_url('customers/details/' . $record->id);?>"
						title="View customer details.">
						<?php echo $record->full_name;?></a>
		<br/>
		Code: <strong><?php echo $record->code;?></strong><br/>
		PAN: <strong><?php echo $record->pan;?></strong>
	</td>
	<td ><?php echo get_contact_widget($record->contact, true)?></td>
	<td>
		<?php echo $record->type == 'I' ? 'Individual' : 'Company';?>
		<?php if($record->type == 'C'):?>
			<br/>Company Reg. No. <strong><?php echo $record->company_reg_no?></strong>
		<?php else:?>
			<br/>Citizenship No. <strong><?php echo $record->citizenship_no?></strong><br/>
			Passport No. <strong><?php echo $record->passport_no?></strong>
		<?php endif?>
	</td>
	<td><?php echo $record->profession;?></td>
	<td class="ins-action">
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
			<i class="fa fa-pencil-square-o margin-r-5"></i><i class="fa fa-caret-down"></i></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<?php if( $this->dx_auth->is_authorized('customers', 'edit.customer') ): ?>
					<li>
						<a href="#"
							title="Edit Basic Information"
							class="trg-dialog-edit"
							data-box-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Edit Basic Information'
							data-url="<?php echo site_url('customers/edit/' . $record->id);?>"
							data-form="#_form-customer">
							<i class="fa fa-pencil-square-o"></i>
							<span>Edit Customer Info</span></a>
					</li>
				<?php endif;?>

				<?php if( ($this->dx_auth->is_authorized('customers', 'delete.customer')) && safe_to_delete( 'Customer_model', $record->id )):?>
					<li class="divider"></li>
					<li>
						<a href="#"
							title="Delete"
							class="trg-row-action"
							data-confirm="true"
							data-url="<?php echo site_url('customers/delete/' . $record->id);?>">
								<i class="fa fa-trash-o"></i>
								<span>Delete</span></a>
					</li>
					<li class="divider"></li>
				<?php endif?>

				<li>
					<a href="<?php echo site_url('customers/details/' . $record->id);?>"
						title="View customer details.">
						<i class="fa fa-user"></i>
						<span>View Details</span></a>
				</li>
			</ul>
		</div>
	</td>
</tr>