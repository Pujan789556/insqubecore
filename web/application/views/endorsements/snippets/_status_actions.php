<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Endorsement : Row Actions
 */

$is_first               = _ENDORSEMENT_is_first($record->txn_type);
$is_premium_computable  = _ENDORSEMENT_is_premium_computable_by_type($record->txn_type);
$is_deletable           = _ENDORSEMENT_is_deletable_by_type($record->txn_type);
$is_policy_editable     = _ENDORSEMENT_is_policy_editable_by_type($record->txn_type);
$is_object_editable     = _ENDORSEMENT_is_object_editable_by_type($record->txn_type);
$is_customer_editable   = _ENDORSEMENT_is_customer_editable_by_type($record->txn_type);
?>
<?php
/**
 * Update Premium
 * ---------------
 * Allowed Status: draft | unverified
 */
if( _ENDORSEMENT_is_editable($record->status, $record->flag_current, FALSE) ):
    $update_premium_url = site_url('endorsements/premium/' . $record->id);
?>
    <?php
    /**
     * Updating Premium is only on Fresh/Renewal Transaction
     */
    if( $is_premium_computable ):?>
    <a href="#"
        title="Update Premium"
        data-toggle="tooltip"
        class="action trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
        data-url="<?php echo $update_premium_url;?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Premium</a>
    <?php endif?>

    <?php if($is_deletable): ?>
    <a href="#"
        title="Delete Transaction/Endorsement"
        data-toggle="tooltip"
        class="action trg-row-action"
        data-confirm="true"
        data-url="<?php echo site_url('endorsements/delete/' . $record->id);?>">
            <i class="fa fa-trash-o"></i>
            <span>Delete</span></a>
    <?php endif; ?>

    <?php if( !$is_first ): ?>
        <a href="#"
            title="Edit Transaction/Endorsement"
            data-toggle="tooltip"
            class="action trg-dialog-edit"
            data-box-size="large"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Transaction/Endorsement - <?php echo $policy_record->code?>'
            data-url="<?php echo site_url('endorsements/edit/' . $record->id);?>"
            data-form="#_form-endorsements">
            <i class="fa fa-pencil-square-o"></i> Edit</a>
    <?php endif; ?>

    <?php if($is_policy_editable): ?>
        <a href="#"
            class="action trg-dialog-edit"
            title="Edit Policy Information"
            data-toggle="tooltip"
            data-box-size="large"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Policy Info for Endorsement- <?php echo $policy_record->code?>'
            data-url="<?php echo site_url('policies/edit_endorsement/' . $record->policy_id );?>"
            data-form="#_form-policy">
            <i class="fa fa-pencil-square-o"></i> Policy</a>
    <?php endif; ?>

    <?php if($is_object_editable): ?>
        <a href="#"
                title="Edit Object Information"
                data-toggle="tooltip"
                class="action trg-dialog-edit"
                data-box-size="full-width"
                data-title='<i class="fa fa-pencil-square-o"></i> Edit Object Info for Endorsement - <?php echo $policy_record->code?>'
                data-url="<?php echo site_url('objects/edit_endorsement/' . $policy_record->id . '/' . $record->id . '/' . $policy_record->object_id);?>"
                data-form="#_form-object">
                <i class="fa fa-pencil-square-o"></i> Object</a>
    <?php endif; ?>

    <?php if($is_customer_editable): ?>
        <a href="#"
                title="Edit Customer Information"
                data-toggle="tooltip"
                class="action trg-dialog-edit"
                data-box-size="large"
                data-title='<i class="fa fa-pencil-square-o"></i> Edit Customer Info for Endorsement - <?php echo $policy_record->code?>'
                data-url="<?php echo site_url('customers/edit_endorsement/' . $policy_record->id . '/' . $record->id . '/' . $policy_record->customer_id);?>"
                data-form="#_form-customer">
                <i class="fa fa-pencil-square-o"></i> Customer</a>

    <?php endif; ?>

<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Print Endorsement
 */
if( $this->dx_auth->is_authorized('endorsements', 'print.endorsement') && !$is_first ):?>

    <a href="<?php echo site_url('endorsements/print/single/' . $record->id );?>"
            target="_blank"
            title="Print this endorsement/transaction"
            class="action"
            data-toggle="tooltip"
        ><i class="fa fa-print"></i> Print</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if( !$is_first && $record->status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED && $this->dx_auth->is_authorized('endorsements', 'status.to.draft') ): ?>
    <a href="#"
        title="Back to Draft"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit/delete</strong> this transaction."
        data-url="<?php echo site_url('endorsements/status/' . $record->id . '/' . IQB_POLICY_ENDORSEMENT_STATUS_DRAFT );?>"
    ><i class="fa fa-level-down"></i> To Draft</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "to Verified"
 */
if( !$is_first && $record->status === IQB_POLICY_ENDORSEMENT_STATUS_DRAFT && $this->dx_auth->is_authorized('endorsements', 'status.to.verified') ): ?>
    <a href="#"
        title="Verify Debit Note"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not modify this transaction anymore."
        data-url="<?php echo site_url('endorsements/status/' . $record->id . '/' . IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED );?>"
    ><i class="fa fa-check-square-o"></i> Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * RI-Aproval Required?
 */
$__flag_ri_approval_constraint = _ENDORSEMENT__ri_approval_constraint($record->status, $record->flag_ri_approval);

if(
    $record->status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED
        &&
    $__flag_ri_approval_constraint == TRUE
        &&
    $this->dx_auth->is_authorized('endorsements', 'status.to.ri.approved') ):
 ?>
    <a href="#"
        title="RI Approve"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to APPROVE the RI-Constraints?"
        data-url="<?php echo site_url('endorsements/status/' . $record->id . '/' . IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED . '/policy_tab_overview' );?>"
    ><i class="fa fa-check-square-o"></i> RI-Approve</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * RI-APPROVED OR VERIFIED(NO RI APPROVAL CONSTRAINT)
 */
if(
    $record->status === IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED
        ||
    ($record->status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED && $__flag_ri_approval_constraint == FALSE )
):

    /**
     * Let's activate the Non-transactional Endorsements
     */
    if( !_ENDORSEMENT_is_transactional_by_type((int)$record->txn_type) && $this->dx_auth->is_authorized('endorsements', 'status.to.active')): ?>
        <a href="#"
            title="Activate Transaction/Endorsement"
            data-toggle="tooltip"
            data-confirm="false"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-url="<?php echo site_url('endorsements/status/' . $record->id  . '/' . IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE );?>"
        ><i class="fa fa-check-square-o"></i> Activate</a>
    <?php endif?>
<?php endif?>

<?php
/**
 * Premium/Refind or Ownership Transfer Summary
 */
if( _ENDORSEMENT_is_transactional_by_type($record->txn_type) )
{
    echo anchor(
        '#',
        '<i class="fa fa-search"></i> Summary',
        [
            'class'         => 'trg-dialog-popup action',
            'data-toggle'   => 'tooltip',
            'title'         => 'View Premium/Refund/Transfer Information',
            'data-url'      => site_url('endorsements/premium_summary/'.$record->id),
            'data-box-size' => 'large',
        ]);
}
?>
