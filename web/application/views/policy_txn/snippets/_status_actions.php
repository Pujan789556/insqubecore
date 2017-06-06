<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy Transaction : Row Actions
 */
?>
<?php
/**
 * Update Premium
 * ---------------
 * Allowed Status: draft | unverified
 */
if( is_policy_txn_editable($record->status, $record->flag_current, FALSE) ):
    $update_premium_url = 'policy_txn/premium/' . $record->txn_type . '/' . $record->policy_id;
?>
    <a href="#"
        title="Update Premium"
        data-toggle="tooltip"
        class="btn btn-sm btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
        data-url="<?php echo site_url($update_premium_url);?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Premium</a>

    <a href="#"
        title="Edit Transaction/Endorsement"
        data-toggle="tooltip"
        class="btn btn-sm btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Edit Transaction/Endorsement - <?php echo $policy_record->code?>'
        data-url="<?php echo site_url('policy_txn/edit/' . $record->id);?>"
        data-form="#_form-policy_txn">
        <i class="fa fa-dollar"></i> Edit</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if( $record->status === IQB_POLICY_TXN_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.draft') ): ?>
    <a href="#"
        title="Back to Draft"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit/delete</strong> this transaction."
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_DRAFT );?>"
    ><i class="fa fa-level-down"></i> To Draft</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Send to Verify"
 */
if( $record->status === IQB_POLICY_TXN_STATUS_DRAFT && $this->dx_auth->is_authorized('policy_txn', 'status.to.unverified') ): ?>
    <a href="#"
        title="Send to Verify"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not edit this record if you do not have upper level permissions."
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_UNVERIFIED );?>"
    ><i class="fa fa-level-up"></i> To Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Un-verified"
 */
if( $record->status === IQB_POLICY_TXN_STATUS_VERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.unverified') ): ?>
    <a href="#"
        title="Back to Un-verified"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit</strong> this transaction."
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_UNVERIFIED );?>"
    ><i class="fa fa-level-down"></i> Un-verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "to Verified"
 */
if( $record->status === IQB_POLICY_TXN_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.verified') ): ?>
    <a href="#"
        title="Verify Debit Note"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not modify this transaction anymore."
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_VERIFIED );?>"
    ><i class="fa fa-check-square-o"></i> Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

$__flag_ri_approval_constraint = _POLICY__ri_approval_constraint($record);

/**
 * Actions on "Verified" Status
 */
if( $record->status === IQB_POLICY_TXN_STATUS_VERIFIED && $__flag_ri_approval_constraint == FALSE && $this->dx_auth->is_authorized('policy_txn', 'status.to.approved') ): ?>
    <a href="#"
            data-toggle="tooltip"
            title="Approve Debit Note. This will approve the debit note and generate transaction code."
            data-confirm="true"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to APPROVE this debit note?"
            data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_APPROVED );?>"
        ><i class="fa fa-check-square-o"></i> Approve</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * RI-Aproval Required?
 */
if( $record->status === IQB_POLICY_TXN_STATUS_VERIFIED && $__flag_ri_approval_constraint == TRUE && $this->dx_auth->is_authorized('policy_txn', 'status.to.ri.approved') ):
 ?>
    <a href="#"
        title="RI Approve"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to APPROVE the RI-Constraints?"
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_RI_APPROVED . '/policy_tab_overview' );?>"
    ><i class="fa fa-check-square-o"></i> RI-Approve</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Actions on "Approved" Status
 */
if( $record->status === IQB_POLICY_TXN_STATUS_APPROVED ): ?>
    <?php
    /**
     * If transaction is related to financial txn (new, renewal, txnal), We generate Voucher
     */
    if( $record->txn_type !== IQB_POLICY_TXN_TYPE_EG &&  $this->dx_auth->is_authorized('policy_txn', 'generate.transaction.voucher') ): ?>
        <a href="#"
            title="Generate Voucher"
            data-toggle="tooltip"
            data-confirm="true"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to do this?<br/>This will automatically generate VOUCHER for this transaction."
            data-url="<?php echo site_url('policy_txn/voucher/' . $record->id );?>"
        ><i class="fa fa-money"></i> Voucher</a>
    <?php elseif($this->dx_auth->is_authorized('policy_txn', 'status.to.active')): ?>
        <a href="#"
            title="Activate Transaction/Endorsement"
            data-toggle="tooltip"
            data-confirm="false"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-url="<?php echo site_url('policy_txn/status/' . $record->id  . '/' . IQB_POLICY_TXN_STATUS_ACTIVE );?>"
        ><i class="fa fa-check-square-o"></i> Activate</a>
    <?php endif?>
<?php
endif;