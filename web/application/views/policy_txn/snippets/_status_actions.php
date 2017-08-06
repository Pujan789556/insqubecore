<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy Transaction : Row Actions
 */

$flag__fresh_or_renewal = in_array($record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]);
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
    <?php
    /**
     * Updating Premium is only on Fresh/Renewal Transaction
     */
    if( $flag__fresh_or_renewal ):?>
    <a href="#"
        title="Update Premium"
        data-toggle="tooltip"
        class="btn btn-sm btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
        data-url="<?php echo site_url($update_premium_url);?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Premium</a>
    <?php endif?>

    <?php
    /**
     * Can't Edit Fresh/New Policy Transaction
     */
    if( !$flag__fresh_or_renewal ):?>
        <a href="#"
            title="Edit Transaction/Endorsement"
            data-toggle="tooltip"
            class="action trg-dialog-edit"
            data-box-size="large"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Transaction/Endorsement - <?php echo $policy_record->code?>'
            data-url="<?php echo site_url('policy_txn/edit_endorsement/' . $record->id);?>"
            data-form="#_form-policy_txn">
            <i class="fa fa-pencil-square-o"></i> Edit</a>
    <?php endif?>

    <a href="#"
        class="action trg-dialog-edit"
        title="Edit Policy Information"
        data-toggle="tooltip"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Edit Policy Info for Endorsement- <?php echo $policy_record->code?>'
        data-url="<?php echo site_url('policies/edit_endorsement/' . $record->policy_id );?>"
        data-form="#_form-policy">
        <i class="fa fa-pencil-square-o"></i> Policy</a>

    <a href="#"
            title="Edit Object Information"
            data-toggle="tooltip"
            class="action trg-dialog-edit"
            data-box-size="large"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Object Info for Endorsement - <?php echo $policy_record->code?>'
            data-url="<?php echo site_url('objects/edit_endorsement/' . $policy_record->id . '/' . $record->id . '/' . $policy_record->object_id);?>"
            data-form="#_form-object">
            <i class="fa fa-pencil-square-o"></i> Object</a>

    <a href="#"
            title="Edit Customer Information"
            data-toggle="tooltip"
            class="action trg-dialog-edit"
            data-box-size="large"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Customer Info for Endorsement - <?php echo $policy_record->code?>'
            data-url="<?php echo site_url('customers/edit_endorsement/' . $policy_record->customer_id);?>"
            data-form="#_form-policy_txn">
            <i class="fa fa-pencil-square-o"></i> Customer</a>
&nbsp;
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if( !$flag__fresh_or_renewal && $record->status === IQB_POLICY_TXN_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.draft') ): ?>
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
if( !$flag__fresh_or_renewal && $record->status === IQB_POLICY_TXN_STATUS_DRAFT && $this->dx_auth->is_authorized('policy_txn', 'status.to.unverified') ): ?>
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
if( !$flag__fresh_or_renewal && $record->status === IQB_POLICY_TXN_STATUS_VERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.unverified') ): ?>
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
if( !$flag__fresh_or_renewal && $record->status === IQB_POLICY_TXN_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policy_txn', 'status.to.verified') ): ?>
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
if(
    !$flag__fresh_or_renewal
        &&
    $record->status === IQB_POLICY_TXN_STATUS_VERIFIED
        &&
    $__flag_ri_approval_constraint == FALSE
        &&
    $this->dx_auth->is_authorized('policy_txn', 'status.to.approved') ): ?>
    <a href="#"
            data-toggle="tooltip"
            title="Approve The Transaction/Endorsement."
            data-confirm="true"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to APPROVE this Transaction/Endorsement?"
            data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_APPROVED );?>"
        ><i class="fa fa-check-square-o"></i> Approve</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * RI-Aproval Required?
 */
if(
    $record->status === IQB_POLICY_TXN_STATUS_VERIFIED
        &&
    $__flag_ri_approval_constraint == TRUE
        &&
    $this->dx_auth->is_authorized('policy_txn', 'status.to.ri.approved') ):
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
    if( (int)$record->txn_type !== IQB_POLICY_TXN_TYPE_EG &&  $this->dx_auth->is_authorized('policy_txn', 'generate.transaction.voucher') ): ?>
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