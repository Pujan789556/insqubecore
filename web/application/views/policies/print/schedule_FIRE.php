<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : Motor - Motorcycle
 */

$object_attributes = json_decode($record->object_attributes);

$premium_attributes = json_decode($record->premium_attributes);


/**
 * Sub Portfolio-wise  Statements
 */
$usage_limitation = '';
$driver_limitation = '';
$reaffirm_numbers = '';
$schedule_table_title   = 'अग्नि बीमालेखको तालिका (सेड्युल)';
$cost_table_title = '';
switch ($record->portfolio_id)
{
    case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
        $schedule_table_title   = 'अग्नि बीमालेखको तालिका (सेड्युल)';
        $usage_limitation       = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
        $driver_limitation      = 'निम्नमध्ये कुनैः–(क) बीमित स्वयं वा (ख) बीमितको साधारण जानकारी र वा सहमतिले सवारी साधन चलाउने व्यक्ति । <br/>चालकसंग उक्त मोटरसाइकल चलाउने अनुमतिपत्र भएको र त्यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
        $cost_table_title       = "मोटरसाइकलको बीमाशुल्क गणना तालिका";
        break;

    case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
        $schedule_table_title   = 'निजी सवारी साधन बीमालेखको तालिका (सेड्युल)';
        $usage_limitation       = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
        $driver_limitation      = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
        $cost_table_title       = "निजी सवारी साधनको बीमाशुल्क गणना तालिका";
        break;

    case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:

        $schedule_table_title   = 'व्यावसायिक सवारी साधन बीमालेखको तालिका (सेड्युल)';
        $usage_limitation       = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको व्यावसायिक सवारी साधन दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षणसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
        $driver_limitation      = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त व्यवसायिक सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
        $cost_table_title       = "व्यावसायिक सवारी साधनको बीमाशुल्क गणना तालिका";
        break;

    default:
        # code...
        break;
}
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
    $creator_text = ( $record->created_by_profile_name ?? $record->created_by_username ) . ' - ' . $record->created_by_code;

    $verifier_text = $record->verified_by_code
                        ? ( $record->verified_by_profile_name ?? $record->verified_by_username ) . ' - ' . $record->verified_by_code
                        : '';

    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name;

    $header_footer = '<htmlpagefooter name="myfooter">
                        <table class="table table-footer no-border">
                            <tr>
                                <td align="left"> Created By: ' . $creator_text . ' </td>
                                <td align="right"> Verified By: ' . $verifier_text . ' </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border-t">'. get_contact_widget_two_lines($record->branch_contact, $branch_contact_prefix) .'</td>
                            </tr>
                        </table>
                    </htmlpagefooter>
                    <sethtmlpagefooter name="myfooter" value="on" />';
    ?>
    </head>
    <body>
        <!--mpdf
            <?php echo $header_footer?>
        mpdf-->
        <?php
        /**
         * Policy Schedule
         */
        ?>
        <table class="table" width="100%">
            <thead><tr><td colspan="2" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr><td colspan="2">बीमालेख नं.: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <strong>बीमीतको विवरण</strong><br/>
                                    नाम थर, ठेगाना:<br/>
                                    <?php
                                    /**
                                     * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                                     * and the customer will be "Account Party"
                                     */
                                    if($record->flag_on_credit === 'Y')
                                    {
                                        echo '<strong>INS.: ' . $this->security->xss_clean($record->creditor_name) . ', ' . $this->security->xss_clean($record->creditor_branch_name) . '</strong><br/>';
                                        echo '<br/>' . get_contact_widget($record->creditor_branch_contact, true, true) . '<br/>';

                                        // Care of
                                        echo '<strong>A/C.: ' . $this->security->xss_clean($record->customer_name) . '<br/></strong>';
                                        echo '<br/>' . get_contact_widget($record->customer_contact, true, true);

                                        echo  $record->care_of ? '<br/>C/O.: ' . $this->security->xss_clean($record->care_of) : '';
                                    }
                                    else
                                    {
                                        echo $this->security->xss_clean($record->customer_name) . '<br/>';
                                        echo '<br/>' . get_contact_widget($record->customer_contact, true, true);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    पेशा: <?php echo $record->customer_profession;?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमा योग्य हित रहेको बैंक/वित्तीय संस्थाको विवरण</strong> <br/>
                                    नाम: <br/>
                                    ठेगाना:
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमाको विषयवस्तु रहेको स्थान, भवन वा सम्पत्तिको विवरण</strong><br/>
                                    <?php

                                    $object = (object)[
                                        'attributes' => $record->object_attributes
                                    ];

                                    $this->load->view('objects/snippets/_schedule_snippet_fire', ['record' => $object ]);
                                     ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding">
                        <table class="table">
                            <tr>
                                <td>बीमालेखको किसिम: <?php echo $record->sub_portfolio_name; ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo nl2br($this->security->xss_clean($record->proposer));?></td>
                            </tr>

                            <tr>
                                <td>बीमा प्रस्ताव भएको मिति: <?php echo $record->proposed_date?></td>
                            </tr>

                            <tr>
                                <td>बीमालेख जारी भएको स्थान र मिति: <?php echo $this->dx_auth->get_branch_code()?>, <?php echo $record->issued_date?></td>
                            </tr>

                            <tr>
                                <td>रक्षावरण गरिएका जोखिमहरु: <?php echo _OBJ_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
                            </tr>

                            <tr>
                                <td>
                                    जोखिम बहन गर्न शूरु हुने मिति: <?php echo $record->start_date?><br/>
                                    समय:
                                </td>
                            </tr>

                            <tr>
                                <?php
                                /**
                                 * Agent Details
                                 */
                                $agent_text = implode(', ', array_filter([$record->agent_name, $record->agent_ud_code]));
                                ?>
                                <td>बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td>बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म</td>
                            </tr>

                            <tr>
                                <td>
                                    <?php
                                    $this->load->view('policy_txn/snippets/_schedule_cost_calculation_table_risks_FIRE', ['txn_record' => $txn_record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>बीमालेखले रक्षावरण गरेको सम्पत्तिको विवरण</strong>
                        <?php
                        $this->load->view('policy_txn/snippets/_schedule_cost_calculation_table_property_FIRE', ['txn_record' => $txn_record]);
                        ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>बीमाशुल्कको हिसाब</strong>: <br>
                        <strong>संलग्न सम्पुष्टीहरु</strong>: <br>
                        <strong>बन्देज (Warranty)</strong>:
                    </td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center;">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <table class="table">
            <tr>
                <td width="50%">
                    कर बिजक नं: <br/>
                    मिति:<br/>
                    रु.:
                </td>
                <td align="left">
                    <h4 class="underline"><?php echo $this->settings->orgn_name_np?> तर्फबाट अधिकार प्राप्त अधिकारीको</h4>
                    <p style="line-height: 30px">दस्तखत:</p>
                    <p>नाम थर:</p>
                    <p>छाप:</p>
                    <p>दर्जा:</p>
                </td>
            </tr>
        </table>
    </body>
</html>