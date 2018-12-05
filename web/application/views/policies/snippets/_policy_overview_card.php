<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Overview Card
*/
?>
<div class="box box-bordered box-success">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
            <span class="pull-left">Policy Details - <small><?php echo $record->code ?></small></span>
            <span class="pull-right">
                <?php if( _POLICY_is_editable($record->status, FALSE) ): ?>
                        <a href="#"
                            class="trg-dialog-edit btn btn-primary btn-sm"
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

    <div class="box-body bg-gray-light no-padding-b">
        <div class="box no-border">
            <div class="box-body no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <td class="text-bold">Policy Category</td>
                            <td><?php echo IQB_POLICY_CATEGORIES[$record->category]?></td>
                        </tr>
                        <?php if($record->category != IQB_POLICY_CATEGORY_REGULAR): ?>
                            <tr>
                                <td class="text-bold">FAC/CO-in From (Insurance Company)</td>
                                <td>
                                    <?php
                                    echo '<span class="margin-r-5">', htmlspecialchars($record->insurance_company_name_en), '</span>';
                                    echo anchor(
                                                site_url('companies/details/' . $record->insurance_company_id),
                                                '<i class="fa fa-external-link small"></i>',
                                                ['target' => '_blank', 'title' => 'View Company Details']);?>
                                </td>
                            </tr>
                        <?php endif ?>

                        <tr>
                            <td class="text-bold">Policy Code</td>
                            <td><?php echo $record->code?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Risk District/Region/State</td>
                            <td><?php echo $record->district_name, ', ', $record->region_name, ', ', $record->state_name?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Portfolio</td>
                            <td><?php echo $record->portfolio_name_en?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Policy Package</td>
                            <td><?php echo _OBJ_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
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
                            <td class="text-bold">Insured Party(Customer)</td>
                            <td>
                                <?php
                                echo '<span class="margin-r-5">', $this->security->xss_clean($record->customer_name_en), '</span>';
                                echo anchor(
                                            site_url('customers/details/' . $record->customer_id),
                                            '<i class="fa fa-external-link small"></i>',
                                            ['target' => '_blank', 'title' => 'View Customer Details']);?>


                            </td>
                        </tr>
                        <tr>
                            <td class="text-bold">Object on Loan/Financed?</td>
                            <td><?php echo $record->flag_on_credit === 'Y' ? 'Yes' : 'No';?></td>
                        </tr>

                        <tr>
                                <td class="text-bold">Care of</td>
                                <td><?php echo nl2br($this->security->xss_clean($record->care_of));?></td>
                            </tr>
                        <tr>
                            <td class="text-bold">Policy Issued Date</td>
                            <td><?php echo $record->issued_date?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Policy Start Date</td>
                            <td><?php echo $record->start_date?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Policy End Date</td>
                            <td><?php echo $record->end_date ?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Policy Duration</td>
                            <td><?php echo _POLICY_duration_formatted($record->start_date, $record->end_date); ?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">is Short Term?</td>
                            <td><?php echo _FLAG_yes_no_dropdown(FALSE)[$record->flag_short_term]?></td>
                        </tr>

                        <tr>
                            <td class="text-bold">Agent</td>
                            <td><?php echo $record->agent_id ? anchor('agents/details/'. $record->agent_id, $record->agent_name . ' <i class="fa fa fa-external-link"></i>', ['target' => '_blank']) : '';?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Tags</td>
                            <td><?php echo _POLICY_tags_text($record->tags ?? []); ?></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Status</td>
                            <td><?php echo _POLICY_status_text($record->status, true);?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="box-body bg-gray-light">
        <?php
        /**
         * Sales Staff, Created By, Verified By, Approved By
         */
        $this->load->view('policies/snippets/_policy_user_card', ['record' => $record]);
        ?>
    </div>
</div>

<?php if( $record->flag_on_credit === IQB_FLAG_YES ): ?>
    <div class="box box-bordered box-warning">
        <div class="box-header with-border border-dark">
            <h4 class="no-margin">
                <span class="pull-left">Creditor (Bank/Financial Institution) Information</span>
                <span class="pull-right">
                    <?php if( _POLICY_is_editable($record->status, FALSE) ): ?>
                            <a href="#"
                                class="trg-dialog-edit btn btn-primary btn-sm"
                                title="Add Bank/Financial Institution"
                                data-toggle="tooltip"
                                data-box-size="large"
                                data-title='<i class="fa fa-pencil-square-o"></i> Add Bank/Financial Institution - <?php echo $record->code?>'
                                data-url="<?php echo site_url('policies/save_creditor/' . $record->id);?>"
                                data-form="#_form-policy">
                                <i class="fa fa-plus"></i>
                            </a>
                    <?php endif?>
                </span>
            </h4>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Branch</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="policy-creditor-list">
                <?php
                /**
                 * Bank Rows
                 */
                $this->load->view('policies/_rows_creditor', ['policy_record' => $record, 'creditors' => $creditors]);
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>