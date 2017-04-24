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
?>
    <a href="#"
        title="Update Premium"
        class="btn btn-success btn-round trg-dialog-edit"
        data-box-size="large"
        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $record->code?>'
        data-url="<?php echo site_url('policy_txn/rebuild/1/' . $record->id);?>"
        data-form="#_form-premium">
        <i class="fa fa-dollar"></i> Update Premium</a>
<?php endif?>

<?php
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
<?php endif?>

<?php
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
<?php endif?>

<?php
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
<?php endif?>


<?php
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
<?php endif?>

<?php
/**
 * Actions on "Verified" Status
 * ----------------------------
 *  1. Upgrade Status to "Approved"
 */
if( $record->status === IQB_POLICY_STATUS_VERIFIED ): ?>
    <?php if( $this->dx_auth->is_authorized('policies', 'make.policy.payment') ): ?>
        <a href="#"
            title="Approve Debit Note"
            data-confirm="true"
            class="btn btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to APPROVE this debit note?"
            data-url="<?php echo site_url('policies/status/' . $record->id . '/' . IQB_POLICY_STATUS_APPROVED );?>"
        ><i class="fa fa-check-square-o"></i> Make a Payment</a>
    <?php endif?>
<?php endif?>

<?php
/**
 * Actions on "Approved" Status
 * ----------------------------
 *  1. Upgrade status to "Paid"
 *  2. Generate all Accounting Transactions (Premium Vouchers, RI Distribution Vouchers)
 */
if( $record->status === IQB_POLICY_STATUS_APPROVED ): ?>
    <?php if( $this->dx_auth->is_authorized('policies', 'make.policy.payment') ): ?>
        <a href="#"
            title="Make a Payment"
            data-confirm="true"
            class="btn btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to MAKE PAYMENT for this policy?<br/>This will automatically perform accounting transactions (Policy Premiums, RI Distribution); which CAN NOT be reverted."
            data-url="<?php echo site_url('policies/payment/' . $record->id );?>"
        ><i class="fa fa-money"></i> Make a Payment</a>
    <?php endif?>
<?php endif?>


<?php
/**
 * Actions on "Paid" Status
 * ----------------------------
 *  1. Activate Policy
 *  2. Generate Original(Fresh Policy Schedule) PDF and store on InsQube Media Storage
 *  2. Generate Invoice
 *  3. Generate Receipt
 *
 *  After this action, you can :
 *      - download/print invoice from invoice tab
 *      - download/print receipt from receipt tab
 *      - update invoice/receipt print flag
 */
if( $record->status === IQB_POLICY_STATUS_PAID ): ?>
    <?php if( $this->dx_auth->is_authorized('policies', 'status.to.active') ): ?>
        <a href="#"
            title="Issue Policy"
            data-confirm="true"
            class="btn btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to do this? This will now activate policy, generate invoice and receipt."
            data-url="<?php echo site_url('policies/activate/' . $record->id );?>"
        ><i class="fa fa-check-square"></i> Issue Policy</a>
    <?php endif?>
<?php endif?>












