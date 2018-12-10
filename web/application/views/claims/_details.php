<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div id="claim-details">
    <div class="box box-bordered box-default">
        <div class="box-header with-border">
            <h3 class="no-margin">
                Claim Information
                <span class="pull-right">
                    <?php
                    /**
                     * Editable, Status Action
                     * ----------------------------
                     */
                    $this->load->view('claims/_status_actions', ['record' => $record, 'ref' => 'd']);
                    ?>
                </span>
            </h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="alert alert-warning">
                        <h4><i class="icon fa fa-warning"></i> Progress Remarks</h4>
                        <?php echo nl2br(htmlspecialchars($record->progress_remarks));?>
                    </div>

                    <div class="box box-bordered box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">Basic Information</h4>
                        </div>
                        <table class="table table-bordered table-responsive table-condensed">
                            <tbody>
                                <tr>
                                    <th>Portfolio</th>
                                    <td><?php echo $record->portfolio_name_en;?></td>
                                </tr>
                                <tr>
                                    <th width="30%">Policy Code</th>
                                    <td><?php echo anchor('policies/details/' . $record->policy_id, $record->policy_code, ['target' => '_blank']);?></td>
                                </tr>
                                <tr>
                                    <th>Policy Object</th>
                                    <td>
                                        <?php echo anchor(
                                            '#',
                                            '<i class="fa fa-search"></i> View Details',
                                            [
                                                'class'         => 'trg-dialog-popup action',
                                                'data-url'      => site_url('objects/popup/'.$record->id),
                                                'data-box-size' => 'large',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Claim Code</th>
                                    <td><?php echo $record->claim_code;?></td>
                                </tr>
                                <tr>
                                    <th>Fiscal Year</th>
                                    <td><?php echo $record->fy_code_np, ' (', $record->fy_code_en, ')';?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td class="text-bold"><?php echo CLAIM__status_dropdown(FALSE)[$record->status];?></td>
                                </tr>
                                <tr>
                                    <th>Status Remarks</th>
                                    <td><?php echo nl2br(htmlspecialchars($record->status_remarks));?></td>
                                </tr>
                                <tr>
                                    <th>Nature of Loss</th>
                                    <td><?php echo $record->loss_nature;?></td>
                                </tr>

                                <tr>
                                    <th>Claim Scheme</th>
                                    <td><?php echo $record->claim_scheme_name;?></td>
                                </tr>
                                <tr>
                                    <th>Settlement Date</th>
                                    <td><strong class="text-green"><?php echo $record->settlement_date;?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="box box-bordered box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">Incident Details</h4>
                        </div>
                        <table class="table table-responsive table-condensed">
                            <tbody>
                                <tr>
                                    <th width="30%">Settlement From</th>
                                    <td><?php echo IQB_CLAIM_CATEGORIES[$record->category];?></td>
                                </tr>
                                <tr>
                                    <th width="30%">Incident Date & Time</th>
                                    <td><?php echo $record->accident_date, ' ', $record->accident_time;?></td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td><?php echo nl2br(htmlspecialchars($record->accident_location));?></td>
                                </tr>
                                <tr>
                                    <th>Details</th>
                                    <td><?php echo nl2br(htmlspecialchars($record->accident_details));?></td>
                                </tr>
                                <tr>
                                    <th>Intimation File</th>
                                    <td>
                                        <?php
                                        if($record->file_intimation)
                                        {
                                            echo anchor('claims/download/'.$record->file_intimation, '<i class="fa fa-download"></i> Download', ['target' => '_blank']);
                                        }
                                         ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="box box-bordered box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">Intimation Details</h4>
                        </div>
                        <table class="table table-responsive table-condensed">
                            <tbody>
                                <tr>
                                    <th width="30%">Name</th>
                                    <td><?php echo $record->intimation_name;?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td><?php echo nl2br(htmlspecialchars($record->initimation_address));?></td>
                                </tr>
                                <tr>
                                    <th>Contact No.</th>
                                    <td><?php echo $record->initimation_contact;?></td>
                                </tr>
                                <tr>
                                    <th>Intimation Date</th>
                                    <td><?php echo $record->intimation_date;?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <div class="row">

        <div class="col-sm-6">
            <div class="box box-bordered box-default">
                <div class="box-header with-border">
                    <h4 class="box-title">Loss/Damage Details</h4>
                </div>
                <table class="table table-responsive table-condensed">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Loss/Damage</th>
                            <th>Details</th>
                            <th>Estimated Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>1.</td>
                            <td>Insured Property</td>
                            <td><?php echo nl2br(htmlspecialchars($record->loss_details_ip));?></td>
                            <td class="text-right"><?php echo number_format($record->amt_estimated_loss_ip, 2);?></td>
                        </tr>

                        <tr>
                            <td>2.</td>
                            <td>Third Party Property</td>
                            <td><?php echo nl2br(htmlspecialchars($record->loss_details_tpp));?></td>
                            <td class="text-right"><?php echo number_format($record->amt_estimated_loss_tpp, 2);?></td>
                        </tr>

                        <tr>
                            <th colspan="3">Total Estimated Amount (Rs.)</th>
                            <th class="text-right"><?php echo number_format(CLAIM__total_estimated_amount($record), 2); ?></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-sm-6">
            <?php
            /**
             * Claim Recovery
             */
            $this->load->view('claims/_snippet_claim_recovery_estimated');
            ?>
        </div>
    </div>

    <?php
    $death_injured  = json_decode($record->death_injured ?? '[]');
    $section_elements = $draft_elements['death_injured_details'];
    ?>
    <div class="box box-bordered box-default">
        <div class="box-header with-border">
            <h4 class="box-title">Death/Injured Information</h4>
        </div>
        <table class="table table-responsive table-condensed">
            <thead>
                <tr>
                    <?php foreach($section_elements as $elem): ?>
                        <th><?php echo $elem['label'] ?></th>
                    <?php endforeach ?>
                </tr>
            </thead>

            <tbody>
                <?php
                if($death_injured):
                    foreach ($death_injured as $single):?>
                        <tr>
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $value = $single->{$elem['_key']} ?? '';
                                    if(isset($elem['_data'])) $value = $elem['_data'][$value];
                                    echo $value;
                                    ?>
                                </td>
                            <?php
                            endforeach;?>
                        </tr>
                    <?php
                    endforeach;
                endif?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="box box-bordered box-default">
                <div class="box-header with-border">
                    <h3 class="no-margin">
                        Claim Assessment
                        <span class="pull-right">
                            <?php if($record->status === IQB_CLAIM_STATUS_VERIFIED && $this->dx_auth->is_authorized('claims', 'update.claim.assessment')): ?>
                                <a href="#"
                                        title="Update Claim Assessment"
                                        data-toggle="tooltip"
                                        class="trg-dialog-edit btn btn-primary btn-sm"
                                        data-title='<i class="fa fa-pencil-square-o"></i> Update Claim Assessment - <?php echo $record->claim_code?>'
                                        data-url="<?php echo site_url('claims/assessment/' . $record->id . '/d');?>"
                                        data-box-size="large"
                                        data-form="#_form-claims">
                                        <i class="fa fa-pencil-square-o"></i></a>
                            <?php endif;?>
                        </span>
                    </h3>
                </div>
                <div class="box-body">
                    <h4 class="box-title page-header no-margin-b">Assessment Brief</h4>
                    <p><?php echo $record->assessment_brief ? nl2br(htmlspecialchars($record->assessment_brief)) : '<small>Not Available!</small>';?></p><br>

                    <h4 class="box-title page-header no-margin-b">Other Information</h4>
                    <p><?php echo $record->other_info ? nl2br(htmlspecialchars($record->other_info)) : '<small>Not Available!</small>';?></p><br>

                    <h4 class="box-title page-header no-margin-b">Supporting Documents</h4>
                    <?php
                    $supporting_docs = array_filter(explode(',', $record->supporting_docs));
                    $doc_reference = CLAIM__supporting_docs_dropdown($record->portfolio_id, FALSE);

                    echo '<ol>';
                        foreach($supporting_docs as $key)
                        {
                            echo '<li>', $doc_reference[$key], '</li>';
                        }
                    echo '</ol>';
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <?php
            /**
             * Beema Samit Report Information
             */
            $this->load->view('claims/_claim_bsrs_headings');

            /**
             * Claim Recovery
             */
            $this->load->view('claims/_snippet_claim_recovery');
             ?>
        </div>
    </div>

    <div class="box box-bordered box-default">
        <div class="box-header with-border">
            <h4 class="no-margin">
                <span class="pull-left">Manage Surveyors</span>
                <span class="pull-right">
                    <?php if($record->status === IQB_CLAIM_STATUS_VERIFIED && $this->dx_auth->is_authorized('claims', 'assign.claim.surveyors')): ?>
                        <a href="#"
                                title="Manage Surveyors"
                                data-toggle="tooltip"
                                class="trg-dialog-edit btn btn-primary btn-sm"
                                data-title='<i class="fa fa-pencil-square-o"></i> Manage Surveyors - <?php echo $record->claim_code?>'
                                data-url="<?php echo site_url('claims/surveyors/' . $record->id . '/d');?>"
                                data-box-size="full-width"
                                data-form="#_form-claims">
                                <i class="fa fa-pencil-square-o"></i></a>
                    <?php endif;?>
                </span>
            </h4>
        </div>

        <?php
        /**
         * Load Rows & Next Link (if any)
         */
        $this->load->view('claims/_list_surveyors', ['records' => $surveyors]);
        ?>

    </div>

    <div class="box box-bordered box-default">
        <div class="box-header with-border">
            <h3 class="no-margin">
                Claim Settlement
                <span class="pull-right">
                    <?php if($record->status === IQB_CLAIM_STATUS_VERIFIED && $this->dx_auth->is_authorized('claims', 'update.claim.settlement')): ?>
                        <a href="#"
                                title="Update Claim Settlement"
                                data-toggle="tooltip"
                                class="trg-dialog-edit btn btn-primary btn-sm"
                                data-title='<i class="fa fa-pencil-square-o"></i> Update Claim Settlement - <?php echo $record->claim_code?>'
                                data-url="<?php echo site_url('claims/settlement/' . $record->id . '/d');?>"
                                data-box-size="full-width"
                                data-form="#_form-claims">
                                <i class="fa fa-pencil-square-o"></i></a>
                    <?php endif;?>
                </span>
            </h3>
        </div>
        <div class="box-body" style="overflow-x: scroll;">
            <table class="table table-responsive table-condensed table-hover">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        <th>Title</th>
                        <th class="text-right">Claimed Amt (Rs.)</th>
                        <th class="text-right">Assessed Amt (Rs.)</th>
                        <th class="text-right">Recommended Amt (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $payable_to_insured     = CLAIM__net_total_payable_insured($record->id);
                    $surveyor_gorss_total   = CLAIM__surveyor_gross_total_fee_by_claim($record->id);
                    $surveyors_vat_total    = CLAIM__surveyor_vat_total_by_claim($record->id);
                    $claim_gorss_total      = CLAIM__gross_total($record->id);
                    $claim_net_total        = CLAIM__net_total($record->id);
                    $i = 1;
                    foreach($settlements as $single):
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo CLAIM__settlement_category_dropdown(FALSE)[$single->category] ?></td>
                            <td><?php echo CLAIM__settlement_subcategory_dropdown(FALSE)[$single->sub_category] ?></td>
                            <td><?php echo htmlspecialchars($single->title) ?></td>
                            <td class="text-right"><?php echo number_format($single->claimed_amount, 2) ?></td>
                            <td class="text-right"><?php echo number_format($single->assessed_amount, 2) ?></td>
                            <td class="text-right"><?php echo number_format($single->recommended_amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td class="border-t-thick" colspan="6">Amount Payable to Insured Party (Rs.)</td>
                        <td class="text-right border-t-thick"><?php echo number_format($payable_to_insured, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="6">Surveyor Fee (Rs.)</td>
                        <td class="text-right"><?php echo number_format($surveyor_gorss_total, 2) ?></td>
                    </tr>
                    <tr>
                        <th colspan="6" class="border-t-thick">Gross Total (Rs.)</th>
                        <th class="text-right border-t-thick"><?php echo number_format($claim_gorss_total, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="6">Surveyor VAT (Rs.)</th>
                        <th class="text-right"><?php echo number_format($surveyors_vat_total, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="6">Grand Total (Rs.)</th>
                        <th class="text-right"><?php echo number_format($claim_net_total, 2) ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


</div>


