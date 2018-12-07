<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Claim Note Print
 */
$print_date = "Print Date: " . date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <?php
    /**
     * Load Styles (inline)
     */
    $this->load->view('print/style/schedule');
    /**
     * Header & Footer
     */
    $header_footer = '<htmlpageheader name="myheader" value="on">
                        <table class="table no-border">
                            <tr>
                                <td align="left"><img style="margin-bottom: 20px;" src="'. logo_path() .'" alt="'.$this->settings->orgn_name_en.'" width="200"></td>
                                <td align="right"><h2>CLAIM NOTE</h2><h3>CLAIM # ' . $record->claim_code . '</h3></td>
                            </tr>
                        </table>
                    </htmlpageheader>
                    <sethtmlpageheader name="myheader" show-this-page="1" value="on" />
                    <htmlpagefooter name="myfooter" value="on">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t" align="left">'. $this->settings->orgn_name_en  . '</td>
                                <td class="border-t" align="right">CLAIM NO. '. $record->claim_code . ', ' . $print_date . '</td>
                            </tr>
                        </table>
                    </htmlpagefooter>
                    <sethtmlpagefooter name="myfooter" show-this-page="1" value="on" />';
    ?>
    </head>
    <body>
        <!--mpdf
            <?php echo $header_footer;?>
        mpdf-->

        <table>
            <tr>
                <td width="50%">
                    <table class="no-border">
                        <tbody>
                            <tr>
                                <th width="30%" align="left">Policy #</th>
                                <td><?php echo $record->policy_code;?></td>
                            </tr>
                            <tr>
                                <th align="left">Policy Period:</th>
                                <td><?php echo $policy_record->start_date, ' to ', $policy_record->end_date; ?></td>
                            </tr>
                            <tr>
                                <th align="left">Insured Party:</th>
                                <td><?php echo $policy_record->customer_name_en;?></td>
                            </tr>
                            <?php
                            $financer_info = [];
                            foreach($creditors as $single)
                            {
                                $financer_info[] = $single->name_en . ', ' . $single->branch_name_en;
                            }
                            if($financer_info):
                            ?>
                                <tr>
                                    <th align="left">Financed By:</th>
                                    <td><?php echo implode('<br/>', $financer_info) ?></td>
                                </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                </td>
                <td>
                    <h3>@TODO: Load Object Summary - claim_object_summary($policy_object)</h3>
                    <p>
                        Define on each portfolio helper function and extract some basic information:<br>
                        Sum Insured:<br>
                        Other Details if you can extract any
                    </p>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <table class="no-border">
                        <tbody>
                            <tr><th colspan="2" class="border-b">CLAIM INTIMATION DETAILS</th></tr>
                            <tr>
                                <th width="30%" align="left">Name:</th>
                                <td><?php echo $record->intimation_name;?></td>
                            </tr>
                            <tr>
                                <th align="left">Address:</th>
                                <td><?php echo nl2br(htmlspecialchars($record->initimation_address));?></td>
                            </tr>
                            <tr>
                                <th align="left">Contact No.:</th>
                                <td><?php echo $record->initimation_contact;?></td>
                            </tr>
                            <tr>
                                <th align="left">Date:</th>
                                <td><?php echo $record->intimation_date;?></td>
                            </tr>
                            <tr>
                                <th align="left">Nature of Loss:</th>
                                <td align="left"><?php echo $record->loss_nature;?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="no-border">
                        <tbody>
                            <tr><th colspan="2" class="border-b">CLAIM INCIDENT DETAILS</th></tr>
                            <tr>
                                <th align="left">Claim Scheme:</th>
                                <td align="left"><?php echo $record->claim_scheme_name;?></td>
                            </tr>
                            <tr>
                                <th width="30%" align="left">Risks:</th>
                                <td><?php echo IQB_CLAIM_CATEGORIES[$record->category];?></td>
                            </tr>
                            <tr>
                                <th align="left">Date & Time:</th>
                                <td><?php echo $record->accident_date, ' ', $record->accident_time;?></td>
                            </tr>
                            <tr>
                                <th align="left">Location:</th>
                                <td><?php echo nl2br(htmlspecialchars($record->accident_location));?></td>
                            </tr>
                            <tr>
                                <th align="left">Details:</th>
                                <td><?php echo nl2br(htmlspecialchars($record->accident_details));?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <strong>Estimated Claim Amount (Rs.)</strong>: <?php echo number_format($record->estimated_claim_amount, 2);?>
                    <!-- <table class="table">
                        <thead>
                            <tr>
                                <td>S.N.</td>
                                <td>Loss/Damage</td>
                                <td>Details</td>
                                <td align="right">Estimated Amount (Rs.)</td>
                            </tr>
                        </thead>
                        <tbody>

                            <tr>
                                <td>1.</td>
                                <td>Insured Property</td>
                                <td><?php echo nl2br(htmlspecialchars($record->loss_details_ip));?></td>
                                <td align="right"><?php echo number_format($record->loss_amount_ip, 2);?></td>
                            </tr>

                            <tr>
                                <td>2.</td>
                                <td>Third Party Property</td>
                                <td><?php echo nl2br(htmlspecialchars($record->loss_details_tpp));?></td>
                                <td align="right"><?php echo number_format($record->loss_amount_tpp, 2);?></td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Estimated Claim Amount (Rs.)</strong></td>
                                <td align="right"><strong><?php echo number_format($record->estimated_claim_amount, 2);?></strong></td>
                            </tr>
                        </tbody>
                    </table> -->
                </td>
            </tr>


            <tr>
                <td>
                    <table class="no-border">
                        <tbody>
                            <tr>
                                <td>
                                    <p><strong>Assessment Brief:</strong></p>
                                    <p><?php echo nl2br(htmlspecialchars($record->assessment_brief));?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <h3>@TODO: RI Recovery Distribution for Claim</h3>
                </td>
            </tr>

            <tr>
                <td>
                    <table class="no-border">
                        <tbody>
                            <tr><th class="border-b">VERIFIED DOCUMENTS</th></tr>
                            <tr>
                                <td>
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
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="no-border">
                        <tbody>
                            <tr><th class="border-b">SURVEYOR DETAILS</th></tr>
                            <tr>
                                <td class="no-padding">
                                    <table>
                                        <tr>
                                            <th class="border-b">#</th>
                                            <th align="left" class="border-b" >Name</th>
                                            <th class="border-b" align="right">Fee</th>
                                        </tr>
                                        <?php
                                        $sn = 1;
                                        $grand_total = 0.00;
                                        foreach($surveyors as $single):
                                            $single_total = bcsub(
                                                ac_bcsum([$single->surveyor_fee, $single->other_fee, $single->vat_amount ?? 0.00], IQB_AC_DECIMAL_PRECISION),
                                                $single->tds_amount ?? 0.00,
                                                IQB_AC_DECIMAL_PRECISION
                                            );
                                            $grand_total = bcadd($grand_total, $single_total, IQB_AC_DECIMAL_PRECISION);
                                         ?>
                                            <tr>
                                                <td align="center"><?php echo $sn++ ?></td>
                                                <td><?php echo  $single->surveyor_name?></td>
                                                <td align="right"><?php echo number_format($single_total, 2) ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        <tr>
                                            <td colspan="2"><strong>Total Surveyor Fee:</strong></td>
                                            <td align="right"><strong><?php echo number_format($grand_total, 2) ?></strong></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <table>
                        <tbody>
                            <tr><th>CLAIM SETTLEMENT DETAILS</th></tr>
                            <tr>
                                <td class="no-padding">
                                    <table>
                                        <thead>
                                            <tr>
                                                <td>#</td>
                                                <td>Title</td>
                                                <td align="right">Claimed (Rs.)</td>
                                                <td align="right">Assessed (Rs.)</td>
                                                <td align="right">Recommended (Rs.)</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $surveyors_vat_total = CLAIM__surveyors_vat_total($surveyors);

                                            $gross_total_claim_amount = ac_bcsum(
                                                [$record->net_amt_payable_insured, $record->gross_amt_surveyor_fee],
                                                IQB_AC_DECIMAL_PRECISION
                                            );
                                            $grand_total_claim_amount = bcadd($gross_total_claim_amount, $surveyors_vat_total, IQB_AC_DECIMAL_PRECISION);

                                            $i = 1;
                                            foreach($settlements as $single):
                                            ?>
                                                <tr>
                                                    <td align="center"><?php echo $i++; ?></td>
                                                    <td><?php echo htmlspecialchars($single->title) ?></td>
                                                    <td align="right"><?php echo number_format($single->claimed_amount, 2) ?></td>
                                                    <td align="right"><?php echo number_format($single->assessed_amount, 2) ?></td>
                                                    <td align="right"><?php echo number_format($single->recommended_amount, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td align="left" colspan="4">Amount Payable to Insured Party (Rs.)</td>
                                                <td align="right"><?php echo number_format($record->net_amt_payable_insured, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td align="left" colspan="4">Surveyor Fee (Rs.)</td>
                                                <td align="right"><?php echo number_format($record->gross_amt_surveyor_fee, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold" align="left" colspan="4">Gross Total (Rs.)</td>
                                                <td class="bold" align="right"><?php echo number_format($gross_total_claim_amount, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold" align="left" colspan="4">Surveyor VAT (Rs.)</td>
                                                <td class="bold" align="right"><?php echo number_format($surveyors_vat_total, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold" align="left" colspan="4">Grand Total (Rs.)</td>
                                                <td class="bold" align="right"><?php echo number_format($grand_total_claim_amount, 2) ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        <table class="no-border">
            <tr><td style="font-size: 9pt"><?php echo htmlspecialchars($record->status_remarks); ?></td></tr>
        </table>
        <br>
        <table class="no-border">
            <tr>
                <td align="left">
                    <table class="no-border">
                        <tr><th align="left">Prepared By:</th></tr>
                        <tr><td>Signature:</td></tr>
                        <tr><td>Name:</td></tr>
                        <tr><td>Designation:</td></tr>
                    </table>
                </td>
                <td align="left">
                    <table class="no-border">
                        <tr><th align="left">Recommended By:</th></tr>
                        <tr><td>Signature:</td></tr>
                        <tr><td>Name:</td></tr>
                        <tr><td>Designation:</td></tr>
                    </table>
                </td>
                <td align="left">
                    <table class="no-border">
                        <tr><th align="left">Approved By:</th></tr>
                        <tr><td>Signature:</td></tr>
                        <tr><td>Name:</td></tr>
                        <tr><td>Designation:</td></tr>
                    </table>
                </td>
            </tr>
        </table>

    </body>
</html>