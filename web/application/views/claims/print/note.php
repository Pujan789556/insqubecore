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

        <table class="outer-table">
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

                            <tr>
                                <td colspan="2">
                                    <?php
                                    /**
                                     * Invoice, Receipt Info
                                     */
                                    $this->load->view('policies/print/_snippet_invoice_info', ['lang' => 'en', 'first_invoice' => $first_invoice]);
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="no-border">
                        <tbody>
                            <tr>
                                <th width="30%" align="left">Portfolio</th>
                                <td><?php echo $record->portfolio_name_en;?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    /**
                                     * Object Snippet
                                     */
                                    echo _OBJ_select_text($object_record);?>
                                </td>
                            </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="outer-table">
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
        </table>
        <table class="outer-table">
            <thead>
                <tr><td colspan="3" class="bold">LOSS/DAMAGE DETAILS</td></tr>
                <tr>
                    <td>Loss/Damage</td>
                    <td>Details</td>
                    <td>Estimated Amount (Rs.)</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1. Insured Property</td>
                    <td><?php echo nl2br(htmlspecialchars($record->loss_details_ip));?></td>
                    <td class="text-right"><?php echo number_format($record->amt_estimated_loss_ip, 2);?></td>
                </tr>

                <tr>
                    <td>2. Third Party Property</td>
                    <td><?php echo nl2br(htmlspecialchars($record->loss_details_tpp));?></td>
                    <td class="text-right"><?php echo number_format($record->amt_estimated_loss_tpp, 2);?></td>
                </tr>

                <tr>
                    <td colspan="2" class="text-right bold">Total Estimated Amount (Rs.)</td>
                    <td class="text-right bold"><?php echo number_format(CLAIM__total_estimated_amount($record), 2); ?></td>
                </tr>
            </tbody>
        </table>

        <table class="outer-table">
            <tr>
                <td width="60%">
                    <table class="no-border">
                        <tbody>
                            <tr><th class="border-b">CLAIM SETTLEMENT DETAILS</th></tr>
                            <tr>
                                <td class="no-padding">
                                    <table>
                                        <tr>
                                            <th class="border-b" width="5%">#</th>
                                            <th class="border-b" align="left">Title</th>
                                            <th class="border-b" align="right">Amount (Rs.)</th>
                                        </tr>
                                        <tbody>
                                            <?php
                                            /**
                                             * !!!NOTE!!!
                                             *
                                             * If claim is closed or withdrawn, only surveyor fee is payable.
                                             */
                                            $flag_surveyor_only = CLAIM__is_widthdrawn($record->status) || CLAIM__is_closed($record->status);
                                            $payable_to_insured     = CLAIM__net_total_payable_insured($record->id);
                                            $surveyor_gorss_total   = CLAIM__surveyor_gross_total_fee_by_claim($record->id);
                                            $surveyors_vat_total    = CLAIM__surveyor_vat_total_by_claim($record->id);
                                            $claim_gorss_total      = CLAIM__gross_total($record->id, $flag_surveyor_only);
                                            $claim_net_total        = CLAIM__net_total($record->id, $flag_surveyor_only);

                                            if( !$flag_surveyor_only ):
                                                $i = 1;
                                                foreach($settlements as $single):
                                                ?>
                                                    <tr>
                                                        <td align="center"><?php echo $i++; ?></td>
                                                        <td><?php echo htmlspecialchars($single->title) ?></td>
                                                        <td align="right">
                                                            <?php
                                                            $value = number_format($single->recommended_amount, 2);
                                                            if($single->category == 'ED')
                                                            {
                                                                $value = "({$value})";
                                                            }
                                                            echo $value;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif ?>
                                        </tbody>

                                        <tfoot>
                                            <?php if( !$flag_surveyor_only ): ?>
                                                <tr>
                                                    <td class="border-t" align="left" colspan="2">Payable to Insured Party (Rs.)</td>
                                                    <td class="border-t" align="right"><?php echo number_format($payable_to_insured, 2) ?></td>
                                                </tr>
                                            <?php endif ?>
                                            <tr>
                                                <td align="left" colspan="2">Surveyor Fee (Rs.)</td>
                                                <td align="right"><?php echo number_format($surveyor_gorss_total, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold border-t" align="left" colspan="2">Gross Total (Rs.)</td>
                                                <td class="bold border-t" align="right"><?php echo number_format($claim_gorss_total, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold" align="left" colspan="2">Surveyor VAT (Rs.)</td>
                                                <td class="bold" align="right"><?php echo number_format($surveyors_vat_total, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold" align="left" colspan="2">Grand Total (Rs.)</td>
                                                <td class="bold" align="right"><?php echo number_format($claim_net_total, 2) ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="40%">
                    <table class="no-border">
                        <tbody>
                            <tr><th class="border-b" colspan="2">CLAIM RECOVERY</th></tr>
                            <?php
                            $claim_ri_data = CLAIM__ri_breakdown($record, TRUE);
                            foreach($claim_ri_data as $label => $value): ?>
                                <tr>
                                    <td align="left"><?php echo $label ?> (Rs.)</td>
                                    <td align="right"><?php echo $value ? number_format($value, 2) : '';?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <table class="outer-table">
            <tr>
                <td width="50%">
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
                                            <th class="border-b" align="right">Gross Fee (Rs.)</th>
                                        </tr>
                                        <?php
                                        $sn = 1;
                                        foreach($surveyors as $single):
                                         ?>
                                            <tr>
                                                <td align="center"><?php echo $sn++ ?></td>
                                                <td><?php echo  $single->surveyor_name?></td>
                                                <td align="right"><?php echo number_format(CLAIM__surveyor_gross_total_fee($single), 2) ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        <tr>
                                            <td colspan="2"><strong>Total Surveyor Fee:</strong></td>
                                            <td align="right"><strong><?php echo number_format(CLAIM__surveyor_gross_total_fee_by_claim($record->id), 2) ?></strong></td>
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
                    <p><strong>Assessment Note:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($record->assessment_note));?></p>
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