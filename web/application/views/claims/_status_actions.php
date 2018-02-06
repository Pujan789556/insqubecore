<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy Transaction : Row Actions
 */

/**
 * Update Premium
 * ---------------
 * Allowed Status: draft | unverified
 */
if( CLAIM__is_editable($record->status, FALSE) ):
?>

    <?php if($record->status == IQB_CLAIM_STATUS_DRAFT && $this->dx_auth->is_authorized('claims', 'delete.claim.draft')): ?>
        <a href="#"
            title="Delete Claim Draft"
            data-toggle="tooltip"
            class="trg-row-action margin-r-5"
            data-confirm="true"
            data-url="<?php echo site_url('claims/delete/' . $record->id);?>">
                <i class="fa fa-trash-o"></i>
                <span>Delete</span></a>
        <?php endif; ?>

        <a href="#"
            title="Edit Claim Draft"
            data-toggle="tooltip"
            class="trg-dialog-edit margin-r-5"
            data-box-size="full-width"
            data-title='<i class="fa fa-pencil-square-o"></i> Edit Claim Draft - <?php echo $record->policy_code?>'
            data-url="<?php echo site_url('claims/edit_draft/' . $record->id);?>"
            data-form="#_form-claims">
            <i class="fa fa-pencil-square-o"></i> Edit</a>

<?php endif; ?>

<a href="<?php echo site_url('claims/details/' . $record->id);?>"
    title="View Details"
    data-toggle="tooltip"
    class="margin-r-5">
    <i class="fa fa-list-alt"></i> Details</a>

<?php

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if( $record->status === IQB_CLAIM_STATUS_VERIFIED && $this->dx_auth->is_authorized('claims', 'status.to.draft') ): ?>
    <a href="#"
        title="Back to Draft"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit/delete</strong> this transaction."
        data-url="<?php echo site_url('claims/to_draft/' . $record->id );?>"
    ><i class="fa fa-level-down"></i> To Draft</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "to Verified"
 */
if( $record->status === IQB_CLAIM_STATUS_DRAFT && $this->dx_auth->is_authorized('claims', 'status.to.verified') ): ?>
    <a href="#"
        title="Verify Claim"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not modify this claim anymore."
        data-url="<?php echo site_url('claims/verify/' . $record->id  );?>"
    ><i class="fa fa-check-square-o"></i> Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status to Approve
 *
 *  Prerequisite
 *  --------------
 *  1. settlement_claim_amount, settlement_amount_breakdown must be set
 *  2. claim_scheme_id must be set
 *  3. assessment_brief must be set
 *
 *
 */
// $flag_eligible_to_approve = _POLICY_TRANSACTION__ri_approval_constraint($record->status, $record->flag_ri_approval);
$flag_eligible_to_approve = TRUE;
if(
    $record->status === IQB_CLAIM_STATUS_VERIFIED
        &&
    $flag_eligible_to_approve == TRUE
        &&
    $this->dx_auth->is_authorized('claims', 'status.to.approved') ):
 ?>
    <a href="#"
        title="Approve"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to APPROVE this claim?"
        data-url="<?php echo site_url('claims/status/' . $record->id . '/' . IQB_CLAIM_STATUS_APPROVED  );?>"
    ><i class="fa fa-check-square-o"></i> Approve</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Let's Settle this Claim
 */
if( $record->status === IQB_CLAIM_STATUS_APPROVED ): ?>
    <a href="#"
        title="Generate Voucher"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-sm btn-success btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>This will automatically generate VOUCHER for this transaction."
        data-url="<?php echo site_url('claims/voucher/' . $record->id );?>"
    ><i class="fa fa-money"></i> Voucher</a>
<?php
endif;

/**
 * Settlement Process:
 *
 */

?>
