<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Actions
 */
?>
<?php if( $record->status === IQB_POLICY_STATUS_DRAFT && $this->dx_auth->is_authorized('policies', 'status.to.unverified') ): ?>

    <a href="#"
        title="Update Premium"
        class="btn btn-success btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $record->code?>'
        data-url="<?php echo site_url('policies/update_premium/' . $record->id);?>"
        data-form="#_form-policy">
        <i class="fa fa-dollar"></i> Update Premium</a>

    <a href="#"
        title="Send to Verify"
        data-confirm="true"
        class="btn btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not edit this record if you do not have upper level permissions."
        data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_UNVERIFIED );?>"
    ><i class="fa fa-level-up"></i> Send to Verify</a>
<?php endif?>