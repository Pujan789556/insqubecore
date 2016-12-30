<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Overview Card
*/
?>
<div class="box box-bordered box-success">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">Policy Details</span>
        <span class="pull-right">
            <?php if( in_array($record->status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED]) && $this->dx_auth->is_authorized_any('policies', ['edit.draft.policy', 'edit.unverified.policy']) ): ?>
                    <span class="action divider"></span>
                    <a href="#"
                        class="action trg-dialog-edit"
                        title="Edit Policy Information"
                        data-toggle="tooltip"
                        data-box-size="large"
                        data-title='<i class="fa fa-pencil-square-o"></i> Edit Policy - <?php echo $record->code?>'
                        data-url="<?php echo site_url('policies/edit/' . $record->id . '/y');?>"
                        data-form="#_form-policy">
                        <i class="fa fa-pencil-square-o"></i>
                    </a>
            <?php endif?>
        </span>
        </h3>
    </div>
    <div class="box-body">
        <table class="table no-margin no-border">
            <tbody>
                <tr>
                    <td class="text-bold">Policy Code</td>
                    <td><?php echo $record->code?></td>
                </tr>
                <tr>
                    <td class="text-bold">Portfolio</td>
                    <td><?php echo $record->portfolio_name?></td>
                </tr>
                <tr>
                    <td class="text-bold">Policy Package</td>
                    <td><?php echo _PO_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
                </tr>
                <tr>
                    <td class="text-bold">Policy Issue Date</td>
                    <td><?php echo $record->issue_date?></td>
                </tr>
                <tr>
                    <td class="text-bold">Policy Start Date</td>
                    <td><?php echo $record->start_date?></td>
                </tr>
                <tr>
                    <td class="text-bold">Policy End Date</td>
                    <td><?php echo $record->end_date?></td>
                </tr>
                <tr>
                    <td class="text-bold">Status</td>
                    <td><?php echo get_policy_status_text($record->status, true);?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>