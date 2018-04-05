<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy Installment : Row Actions
 */

// ------------------------------------------------------------------------------

/**
 * VOUCHER the Installment
 * -----------------------
 *
 * Logic:
 *
 *  Case 1: First Installment
 *      The first installment is only eligible for voucher if Endorsement record is eligible
 *      i.e. either ri_approved or no ri_approval constraint with verified status
 *
 *  Case 2: Other installmemnts
 *      The first installment of this transaction record has to be paid.
 */
if( _POLICY_INSTALLMENT__voucher_constraint($record) ):
    /**
     * Let's allow to generate voucher if one has permission.
     */
    if( $this->dx_auth->is_authorized('policy_installments', 'generate.policy.voucher') ): ?>
        <a href="#"
            title="Generate Voucher"
            data-toggle="tooltip"
            data-confirm="true"
            class="btn btn-sm btn-success btn-round trg-dialog-action"
            data-message="Are you sure you want to do this?<br/>This will automatically generate VOUCHER for this transaction."
            data-url="<?php echo site_url('policy_installments/voucher/' . $record->id );?>"
        ><i class="fa fa-money"></i> Voucher</a>
    <?php endif?>
<?php endif?>
