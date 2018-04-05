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
 * Allowed Status: draft
 */
if( _POLICY_is_editable($record->status, FALSE) ):
    $update_premium_url = site_url('endorsements/premium/' . $txn_record->id);
?>
    <a href="#"
        title="Update Premium"
        class="btn btn-success btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $record->code?>'
        data-url="<?php echo $update_premium_url;?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Update Premium</a>

<?php
endif;

// ------------------------------------------------------------------------------

/**
 * Status "Back to Draft"
 */
if(
    $record->status === IQB_POLICY_STATUS_VERIFIED
        &&
    $this->dx_auth->is_authorized('policies', 'status.to.draft')
        &&
    $txn_record->status === IQB_POLICY_TXN_STATUS_VERIFIED
): ?>
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
 * Status "to Verified"
 *
 *  1. Lock flag to "ON" from Customer and Object Record
 *  2. Status to "VERIFIED" from Policy Record
 *  3. Status to "VERIFIED" from Endorsement Record
 */
if( $record->status === IQB_POLICY_STATUS_DRAFT && $this->dx_auth->is_authorized('policies', 'status.to.verified') ): ?>
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

/**
 * RI-Aproval Required?
 * ----------------------------
 * If this policy needs to be approved by RI, here it is.
 * The following tasks are carried out:
 *
 *  1. Update Txn Status to IQB_POLICY_TXN_STATUS_RI_APPROVED
 */
$__flag_ri_approval_constraint = _ENDORSEMENT__ri_approval_constraint($txn_record->status, $txn_record->flag_ri_approval);
if( $record->status === IQB_POLICY_STATUS_VERIFIED && $__flag_ri_approval_constraint == TRUE && $this->dx_auth->is_authorized('endorsements', 'status.to.ri.approved') ):
 ?>
    <a href="#"
        title="RI Approve"
        data-toggle="tooltip"
        data-confirm="true"
        class="btn btn-danger btn-round trg-dialog-action"
        data-message="Are you sure you want to APPROVE the RI-Constraints?"
        data-url="<?php echo site_url('endorsements/status/' . $txn_record->id . '/' . IQB_POLICY_TXN_STATUS_RI_APPROVED . '/tab-policy-overview' );?>"
    ><i class="fa fa-check-square-o"></i> RI-Approve</a>
<?php
endif;
?>





