<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Actions
 */
// <i class="icon fa fa-warning"></i> This Policy is not <strong></strong>.
if( $record->status === IQB_POLICY_STATUS_UNVERIFIED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>UNVERIFIED</strong>.';
    $css_class  = 'text-red';
}
else if( $record->status === IQB_POLICY_STATUS_VERIFIED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>VERIFIED</strong>.';
    $css_class  = 'text-purple';
}
else if( $record->status === IQB_POLICY_STATUS_APPROVED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>Approved</strong>.';
    $css_class  = 'text-green';
}
else if( $record->status === IQB_POLICY_STATUS_INVOICED )
{
    $status_sentence   = '<i class="fa fa-warning margin-r-5"></i>This Policy is <strong>INVOICED</strong>.';
    $css_class  = 'text-green';
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
?>
<p class="<?php echo $css_class?>"><?php echo $status_sentence?></p>