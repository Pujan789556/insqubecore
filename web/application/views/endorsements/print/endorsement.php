<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Endorsement Print
 */
$schedule_table_title = [
    'np' => "सम्पुष्टि विवरण",
    'en' => "ENDORSEMENT"
];

$orgn_name = [
    'np' => $this->settings->orgn_name_np,
    'en' => $this->settings->orgn_name_en,
];

$endorsement_title = [
    'np' => 'सम्पुष्टी वाक्यंश',
    'en' => 'MEMORANDUM'
];

// echo '<pre>'; print_r($records);exit;

$labels = [
    'assigntopolicy' => [
        'en' => 'Attaching to & forming parto fo policy no.',
        'np' => 'संग संलग्न र सोको अभिन्न अंगको रुपमा रहेको छ'
    ],
    'endorsement_no' => ['en' => 'Endorsement Number', 'np' => 'सम्पुष्टी नं'],
    'policy_no' => ['en' => 'Policy Number', 'np' => 'बीमालेख नं'],
    'agent' => ['en' => 'Agent', 'np' => 'अभिकर्ता'],
    'issued_at' => ['en' => 'Issued At', 'np' => 'जारी स्थान'],
    'issued_date' => ['en' => 'Issued Date', 'np' => 'जारी मिती'],
    'duration' => ['en' => 'Policy Duration', 'np' => 'बीमा अवधी'],
    'from' => ['en' => 'From', 'np' => 'देखी'],
    'to' => ['en' => 'To', 'np' => 'सम्म'],
    'till_midnight' => ['en' => 'till midnight.', 'np' => 'रातको १२ बजेसम्म'],
    'old_si' => ['en' => 'Old Sum Insured (Rs.)', 'np' => 'पुरानो बीमांक (रु)'],
    'added_si' => ['en' => 'Added Sum Insured (Rs.)', 'np' => 'थपिएको बीमांक (रु)'],
    'new_si' => ['en' => 'Total Sum Insured (Rs.)', 'np' => 'जम्मा बीमांक (रु)'],
    'premium' => ['en' => 'Premium', 'np' => 'बीमा शुल्क'],
    'title' => ['en' => 'Title', 'np' => 'शिर्षक'],
    'charge' => ['en' => 'Charge', 'np' => 'थप'],
    'refund' => ['en' => 'Refund', 'np' => 'फिर्ता'],
    'additional_premium' => ['en' => 'ADDITIONAL PREMIUM', 'np' => 'थप बीमांक'],
    'basic_premium' => ['en' => 'Basic Premium (Rs)', 'np' => 'आधारभुत बीमा शुल्क (रु)'],
    'pool_premium' => ['en' => 'RSTMDST (Rs.)', 'np' => 'हुल्दंगा हडताल र द्वेश(रिसइवी) पूर्ण कार्य (रु)'],
    'ownership_transfer_fee' => ['en' => 'Ownership Transfer Fee (Rs)', 'np' => 'नामसारी शुल्क (रु)'],
    'ownership_transfer_ncd' => ['en' => 'No Claim Discount (Rs)', 'np' => 'दावी नगरे वापत छुट (रु)'],
    'stamp_duty' => ['en' => 'Stamp Duty (Rs)', 'np' => 'टिकट दस्तुर (रु)'],
    'vat' => ['en' => 'VAT', 'np' => 'मु अ कर (रु)'],
    'total_premium' => ['en' => 'Total (Rs)', 'np' => 'जम्मा (रु)'],
];

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
                                <td align="right"><h2>'.$schedule_table_title[$lang].'</h2></td>
                            </tr>
                        </table>
                    </htmlpageheader>
                    <sethtmlpageheader name="myheader" show-this-page="1" value="on" />
                    <htmlpagefooter name="myfooter" value="on">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t" align="left">'. $this->settings->orgn_name_en .'</td>
                                <td class="border-t" align="right">'. $this->settings->website .'</td>
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
        <?php
        $count = count($records);
        $i = 1;
        foreach($records as $record):?>
            <table class="table" width="100%" >
                <thead><tr><td colspan="2" align="center"><h3 style="margin:0"><?php echo $schedule_table_title[$lang]?></h3></td></tr></thead>
                <tbody>
                    <?php if($lang == 'en'): ?>
                        <tr>
                            <td colspan="2">Attaching to & forming parto fo policy no. <?php echo $record->policy_code; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td width="50%">
                            <table class="no-border">
                                <tr>
                                    <td><?php echo $labels['endorsement_no'][$lang]; ?>:</td>
                                    <td><?php echo $record->id; ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $labels['policy_no'][$lang]; ?>:</td>
                                    <td>
                                        <?php echo $record->policy_code; ?>
                                        <?php
                                        if($lang == 'np')
                                        {
                                            echo $labels['assigntopolicy'][$lang];
                                        }
                                        ?>

                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border-top:1px solid #333333 !important">
                                        <?php
                                        /**
                                         * Insured Party, Financer, Other Financer, Careof
                                         */
                                        $this->load->view('endorsements/print/_endorsement_insured_party', ['lang' => $lang, 'record' => $record]);
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table class="no-border">
                                <tr>
                                    <td width="20%"><?php echo $labels['agent'][$lang]; ?>:</td>
                                    <td>
                                        <?php
                                        /**
                                         * Agent Details
                                         */
                                        $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                                        echo $agent_text;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $labels['issued_at'][$lang]; ?>:</td>
                                    <td>
                                        <?php
                                        $col_name = 'branch_name_' . $lang;
                                        echo $record->{$col_name};
                                        ?>

                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $labels['issued_date'][$lang]; ?>:</td>
                                    <td><?php echo $record->issued_date; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><?php echo $labels['duration'][$lang]; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right"><?php echo $labels['from'][$lang]; ?>:</td>
                                    <td><?php echo $record->start_date; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right"><?php echo $labels['to'][$lang]; ?>:</td>
                                    <td><?php echo $record->end_date; ?> <?php echo $labels['till_midnight'][$lang]; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <?php
                            $old_si     = $record->amt_sum_insured_object - $record->amt_sum_insured_net;
                            $added_si   = $record->amt_sum_insured_net;
                            $new_si     = $record->amt_sum_insured_object
                            ?>
                            <table class="no-border">
                                <tr>
                                    <td><?php echo $labels['old_si'][$lang]; ?> :</td>
                                    <td class="text-right"><?php echo number_format($old_si, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $labels['added_si'][$lang]; ?>:</td>
                                    <td class="text-right"><?php echo number_format($added_si, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $labels['new_si'][$lang]; ?> :</td>
                                    <td class="text-right"><?php echo number_format($new_si, 2); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>

                            <?php
                            /**
                             * Premium Refund Computation
                             */
                            // echo '<pre>'; print_r($record);exit;
                            $basic_premium  = (float)$record->net_amt_basic_premium;
                            $pool_premium   = (float)$record->net_amt_pool_premium;
                            $transfer_fee   = (float)$record->net_amt_transfer_fee;
                            $transfer_ncd   = (float)$record->net_amt_transfer_ncd;
                            $stamp_duty = (float)$record->net_amt_stamp_duty;
                            $vat            = (float)$record->net_amt_vat;
                            $premium_table = [];
                            if($record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER)
                            {
                                $premium_table [ $labels['ownership_transfer_fee'][$lang] ]  = $transfer_fee;

                                if($transfer_ncd)
                                {
                                    $premium_table [ $labels['ownership_transfer_ncd'][$lang] ]  = $transfer_ncd;
                                }
                                $total_premium  = bcadd($transfer_fee, $transfer_ncd, IQB_AC_DECIMAL_PRECISION);
                            }
                            else
                            {
                                $premium_table  = [
                                    $labels['basic_premium'][$lang]             => $basic_premium,
                                    $labels['pool_premium'][$lang]              => $pool_premium
                                ];

                                $total_premium  = bcadd($basic_premium, $pool_premium, IQB_AC_DECIMAL_PRECISION);
                            }

                            // Stamp Duty
                            if($stamp_duty)
                            {
                                $premium_table[ $labels['stamp_duty'][$lang] ] = $stamp_duty;
                                $total_premium = bcadd($total_premium, $stamp_duty, IQB_AC_DECIMAL_PRECISION);
                            }

                            // VAT
                            if($vat)
                            {
                                $premium_table[ $labels['vat'][$lang] ] = $vat;

                                $total_premium = bcadd($total_premium, $vat, IQB_AC_DECIMAL_PRECISION);
                            }



                            if(_ENDORSEMENT_is_transactional($record) ):
                            ?>
                                <h3 style="margin:0"><?php echo $labels['additional_premium'][$lang]; ?></h3>

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td><?php echo $labels['title'][$lang]; ?></td>
                                            <td><?php echo $labels['charge'][$lang]; ?></td>
                                            <td><?php echo $labels['refund'][$lang]; ?></td>
                                        </tr>
                                    </thead>
                                    <?php
                                    foreach($premium_table as $title => $value):
                                        if( _ENDORSEMENT_is_refundable($record->txn_type) )
                                        {
                                            $row = '<td>'.$title . '</td>' .
                                                    '<td class="text-right">0.00</td>' .
                                                    '<td class="text-right">' . ac_format_number($value, 2) . '</td>';
                                        }
                                        else
                                        {
                                            $row = '<td>'.$title . '</td>' .
                                                    '<td class="text-right">' . ac_format_number($value, 2) . '</td>'.
                                                    '<td class="text-right">0.00</td>';
                                        }
                                        $row = "<tr>{$row}</tr>";
                                        echo $row;
                                        ?>
                                    <?php
                                    endforeach;


                                    $total_row = '<td class="text-right text-bold">'. $labels['total_premium'][$lang] .'</td>';
                                    if( _ENDORSEMENT_is_refundable($record->txn_type) )
                                    {
                                        $total_row .=   '<td class="text-right text-bold">0.00</td>' .
                                                        '<td class="text-right text-bold">' . ac_format_number($total_premium, 2) . '</td>';
                                    }
                                    else
                                    {
                                        $total_row .=   '<td class="text-right text-bold">' . ac_format_number($total_premium, 2) . '</td>'.
                                                        '<td class="text-right text-bold">0.00</td>';
                                    }
                                    $total_row = "<tr>{$total_row}</tr>";
                                    echo $total_row;
                                    ?>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><h3 style="margin: 0"><?php echo $endorsement_title[$lang] ?></h3></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo nl2br(htmlspecialchars($record->txn_details)); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php
            /**
             * Load Footer
             */
            $this->load->view('endorsements/print/_endorsement_footer', ['lang' => $lang]);


            /**
             * PDF Pagebreak for next Endorsement
             */
            if($i < $count)
            {
                echo '<pagebreak>';
            }
            $i++;
        endforeach;?>
    </body>
</html>