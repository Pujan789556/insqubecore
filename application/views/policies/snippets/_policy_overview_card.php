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
            <?php if( is_policy_editable($record->status, FALSE) ): ?>
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
                    <td class="text-bold">Sub Portfolio</td>
                    <td><?php echo $record->sub_portfolio_name?></td>
                </tr>
                <tr>
                    <td class="text-bold">Policy Package</td>
                    <td><?php echo _PO_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
                </tr>
                <tr>
                    <td class="text-bold">Proposed By</td>
                    <td><?php echo nl2br($this->security->xss_clean($record->proposer));?></td>
                </tr>

                <tr>
                    <td class="text-bold">Policy Proposed Date</td>
                    <td><?php echo $record->proposed_date?></td>
                </tr>
                <tr>
                    <td class="text-bold">Object on Loan/Financed?</td>
                    <td><?php echo $record->flag_on_credit === 'Y' ? 'Yes' : 'No';?></td>
                </tr>
                <?php if($record->flag_on_credit === 'Y'):?>
                    <tr>
                        <td class="text-bold">Insured Party</td>
                        <td><?php echo $this->security->xss_clean($record->creditor_name);?>, <?php echo $this->security->xss_clean($record->creditor_branch_name);?></td>
                    </tr>
                    <tr>
                        <td class="text-bold">Account Party</td>
                        <td><?php echo $this->security->xss_clean($record->customer_name);?></td>
                    </tr>
                     <tr>
                        <td class="text-bold">Care Of</td>
                        <td><?php echo nl2br($this->security->xss_clean($record->care_of));?></td>
                    </tr>
                <?php else:?>
                    <tr>
                        <td class="text-bold">Insured Party</td>
                        <td><?php echo $this->security->xss_clean($record->customer_name);?></td>
                    </tr>
                <?php endif?>
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
                    <td class="text-bold">is Short Term?</td>
                    <td><?php echo _FLAG_yes_no_dropdwon(FALSE)[$record->flag_short_term]?></td>
                </tr>
                <tr>
                    <td class="text-bold">Sales Staff</td>
                    <td><?php echo $record->sales_staff_username;?></td>
                </tr>

                <tr>
                    <td class="text-bold">Agent</td>
                    <td><?php echo $record->agent_name;?></td>
                </tr>
                <tr>
                    <td class="text-bold">Status</td>
                    <td><?php echo get_policy_status_text($record->status, true);?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>