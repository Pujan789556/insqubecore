<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Actions
 */
if( $record->status === IQB_POLICY_STATUS_VERIFIED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>VERIFIED</strong>.';
    $css_class  = 'text-green';

    // Transaction Status
    if($endorsement_record->status === IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED || $endorsement_record->status === IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED )
    {
        $status_sentence .= "<br/>Please <strong>Generate Policy Voucher</strong> from <strong>Transactions</strong> Tab.";
    }
    else if( $endorsement_record->status == IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED )
    {
        $status_sentence .= "<br/>Please <strong>Generate Policy Invoice</strong> from <strong>Vouchers</strong> Tab.";
    }
    else if( $endorsement_record->status === IQB_POLICY_ENDORSEMENT_STATUS_INVOICED )
    {
        $status_sentence .= "<br/>Please <strong>Make Payment</strong> for this policy from <strong>Invoices</strong> Tab.";
    }
}
else if($record->status === IQB_POLICY_STATUS_ACTIVE )
{
    $status_sentence   = '<i class="fa fa-check-square-o margin-r-5"></i>This Policy is <strong>ISSUED</strong>.';
    $css_class  = 'text-green';
}
else if( $record->status === IQB_POLICY_STATUS_CANCELED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>CANCELED</strong>.';
    $css_class  = 'text-muted';
}
else if( $record->status === IQB_POLICY_STATUS_EXPIRED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>EXPIRED</strong>.';
    $css_class  = 'text-warning';
}
else
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>DRAFT</strong>.';
    $css_class  = 'text-red';
}


$__flag_ri_approval_constraint = _ENDORSEMENT__ri_approval_constraint($endorsement_record->status, $endorsement_record->flag_ri_approval);
if($record->status === IQB_POLICY_STATUS_VERIFIED && $__flag_ri_approval_constraint == TRUE )
{
    echo '<div class="alert alert-danger"><h4><i class="fa fa-warning margin-r-5"> Pending RI-Approval.</h4></div>';
}
?>
<p class="<?php echo $css_class?>"><?php echo $status_sentence?></p>