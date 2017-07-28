<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Actions
 */
?>
<a title="Print Policy Schedule"
        class="btn bg-navy btn-round"
        href="<?php echo site_url('policies/schedule/' . $record->id  );?>"
        target="_blank"
    ><i class="fa fa-print"></i> Schedule</a>


<?php
/**
 * Update Premium
 * ---------------
 * Allowed Status: draft | unverified
 */
if( is_policy_editable($record->status, FALSE) ):
    $txn_type = $record->ancestor_id ? IQB_POLICY_TXN_TYPE_RENEWAL:   IQB_POLICY_TXN_TYPE_FRESH;
    $update_premium_url = 'policy_txn/premium/' . $txn_type . '/' . $record->id;
?>
    <a href="#"
        title="Update Premium"
        class="btn btn-success btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $record->code?>'
        data-url="<?php echo site_url($update_premium_url);?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Update Premium</a>

<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if( $record->status === IQB_POLICY_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policies', 'status.to.draft') ): ?>
    <a href="#"
        title="Back to Draft"
        data-confirm="true"
        class="btn bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit/delete</strong> this policy."
        data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_DRAFT );?>"
    ><i class="fa fa-level-down"></i> To Draft</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Send to Verify"
 */
if( $record->status === IQB_POLICY_STATUS_DRAFT && $this->dx_auth->is_authorized('policies', 'status.to.unverified') ): ?>
    <a href="#"
        title="Send to Verify"
        data-confirm="true"
        class="btn bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not edit this record if you do not have upper level permissions."
        data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_UNVERIFIED );?>"
    ><i class="fa fa-level-up"></i> To Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Un-verified"
 *
 *  1. Lock flag to "OFF" from Customer and Object Record
 *  2. Status to "DRAFT" from Policy Transaction Record
 */
if( $record->status === IQB_POLICY_STATUS_VERIFIED && $this->dx_auth->is_authorized('policies', 'status.to.unverified') ): ?>
    <a href="#"
        title="Back to Un-verified"
        data-confirm="true"
        class="btn bg-maroon btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit</strong> this policy."
        data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_UNVERIFIED );?>"
    ><i class="fa fa-level-down"></i> Un-verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "to Verified"
 *
 *  1. Lock flag to "ON" from Customer and Object Record
 *  2. Status to "VERIFIED" from Policy Record
 *  3. Status to "VERIFIED" from Policy Transaction Record
 */
if( $record->status === IQB_POLICY_STATUS_UNVERIFIED && $this->dx_auth->is_authorized('policies', 'status.to.verified') ): ?>
    <a href="#"
        title="Verify Debit Note"
        data-confirm="true"
        class="btn bg-orange btn-round trg-dialog-action"
        data-message="Are you sure you want to do this?<br/>You can not modify this policy anymore."
        data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_VERIFIED );?>"
    ><i class="fa fa-check-square-o"></i> Verify</a>
<?php
endif;

// ------------------------------------------------------------------------------

$__flag_ri_approval_constraint = _POLICY__ri_approval_constraint($record->id);

/**
 * Actions on "Verified" Status
 * ----------------------------
 * Upon Approval, the Debit note becomes a "Policy". To do so, we perform
 * the following tasks:
 *
 *  1. Generate a Policy Number and assigned to it.
 *  2. Update Status to "Approved"
 */
if( $record->status === IQB_POLICY_STATUS_VERIFIED && $__flag_ri_approval_constraint == FALSE && $this->dx_auth->is_authorized('policies', 'status.to.approved') ): ?>
    <a href="#"
            data-toggle="tooltip"
            title="Approve Debit Note. This will approve the debit note and generate policy code."
            data-confirm="true"
            class="btn btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to APPROVE this debit note?"
            data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_APPROVED );?>"
        ><i class="fa fa-check-square-o"></i> Approve Debit Note</a>
<?php
endif;

// ------------------------------------------------------------------------------

/**
 * RI-Aproval Required?
 * ----------------------------
 * If this policy needs to be approved by RI, here it is.
 * The following tasks are carried out:
 *
 *  1. Update Txn Status to IQB_POLICY_TXN_STATUS_RI_APPROVED
 */
if( $record->status === IQB_POLICY_STATUS_VERIFIED && $__flag_ri_approval_constraint == TRUE && $this->dx_auth->is_authorized('policy_txn', 'status.to.ri.approved') ):
 ?>
    <a href="#"
        title="RI Approve"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to APPROVE the RI-Constraints?"
        data-url="<?php echo site_url('policy_txn/status/' . $record->id . '/' . IQB_POLICY_TXN_STATUS_RI_APPROVED . '/policy_tab_overview' );?>"
    ><i class="fa fa-check-square-o"></i> RI-Approve</a>
<?php
endif;
?>





