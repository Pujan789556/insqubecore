<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI Transactions:  Single Row
*/
?>
<tr class="searchable" data-id="<?php echo $record->id; ?>" id="_data-row-invoice-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id ;?></td>
		<?php endif;?>
	<td><?php echo anchor('policies/details/' . $record->policy_id, $record->policy_code, ['target' => '_blank']);?></td>
	<td><?php echo _ENDORSEMENT_type_dropdown(false, false)[$record->txn_type];?></td>
	<td><?php echo $record->treaty_type_name;?></td>
	<td><?php echo IQB_RI_TXN_FOR_TYPES[$record->ri_txn_for];?></td>
	<td class="ins-action">
		<?php if($record->flag_has_fac): ?>

			<?php if( $this->dx_auth->is_authorized('ri_transactions', 'register.fac') && belong_to_current_fy_quarter($record->fiscal_yr_id, $record->fy_quarter)):?>
				<a href="#"
					class="action trg-dialog-edit"
					data-title='<i class="fa fa-pencil-square-o"></i> Edit FAC Registration'
					data-box-size="full-width"
					data-form="#__form-fac-registration"
					data-url="<?php echo site_url($this->data['_url_base'] . '/register_fac/'.$record->id); ?>"
					data-toggle="tooltip"
					title="Register FAC"><i class="fa fa-pencil-square-o"></i></a>
			<?php endif ?>

			<a href="#"
				class="action trg-dialog-popup"
				data-toggle="tooltip"
				data-box-size="large"
				data-url="<?php echo site_url($this->data['_url_base'] . '/preview_fac/'.$record->id); ?>"
				title="View FAC Distribution"><i class="fa fa-search"></i></a>
		<?php endif ?>
	</td>
	<td class="ins-action">
		<?php echo anchor(
						'#',
						'<i class="fa fa-search"></i> Details',
						[
							'class' 		=> 'trg-dialog-popup action',
							'data-url' 		=> site_url($this->data['_url_base'] . '/details/'.$record->id),
							'data-box-size' => 'large',
						]);?>
	</td>
</tr>