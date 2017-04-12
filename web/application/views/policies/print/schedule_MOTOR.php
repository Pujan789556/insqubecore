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
$schedule_table_title = '';
$cost_table_title = '';
switch ($record->sub_portfolio_code)
{
    case IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE:
        $schedule_table_title   = 'मोटरसाइकल बीमालेखको तालिका (सेड्युल)';
        $usage_limitation       = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
        $driver_limitation      = 'निम्नमध्ये कुनैः–(क) बीमित स्वयं वा (ख) बीमितको साधारण जानकारी र वा सहमतिले सवारी साधन चलाउने व्यक्ति । <br/>चालकसंग उक्त मोटरसाइकल चलाउने अनुमतिपत्र भएको र त्यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
        $cost_table_title       = "मोटरसाइकलको बीमाशुल्क गणना तालिका";
        break;

    case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE:
        $schedule_table_title   = 'निजी सवारी साधन बीमालेखको तालिका (सेड्युल)';
        $usage_limitation       = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
        $driver_limitation      = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
        $cost_table_title       = "निजी सवारी साधनको बीमाशुल्क गणना तालिका";
        break;

    case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE:

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
                                    <h4 class="border-b">बीमीतको विवरण</h4><br/>
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
                                <td>लागू हुने भौगोलिक क्षेत्र: नेपाल, भारत, भूटान, बंगलादेश र चीनको स्वशासित क्षेत्र तिब्बत</td>
                            </tr>

                            <tr>
                                <td>
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding no-border">
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
                                <td>रक्षावरण गरिएका जोखिमहरु: <?php echo _PO_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
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
                                    <table class="table no-border">
                                        <tr><td colspan="2"><h4 class="underline">बीमाशुल्क</h4><br/></td></tr>

                                        <?php foreach($premium_attributes as $section_object):?>
                                            <tr>
                                                <td><?php echo $section_object->title_np?></td>
                                                <td class="cost">
                                                    रु <?php echo number_format((float)$section_object->sub_total, 2, '.', '');?>
                                                </td>
                                            </tr>
                                        <?php endforeach?>

                                        <tr>
                                            <td>जम्मा</td>
                                            <td class="cost border-top">
                                                रु <?php echo number_format((float)$record->total_amount, 2, '.', '');?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>टिकट</td>
                                            <td class="cost">
                                                रु <?php echo number_format((float)$record->stamp_duty, 2, '.', '');?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>मु. अ. क. (VAT)</td>
                                            <td class="cost">
                                                रु <?php echo number_format( (float)($record->stamp_duty + $record->total_amount) * 0.13, 2, '.', '');?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bold">मु. अ. क.(VAT) सहित जम्मा शुल्क</td>
                                            <td class="cost border-top bold">
                                                रु <?php echo number_format( (float)($record->stamp_duty + $record->total_amount) * 1.13, 2, '.', '');?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" align="center"><h4>बीमालेखले रक्षावरण गरेको सवारी साधनको</h4></td>
                </tr>
                <tr>
                    <td>
                        इन्जिन नं.: <?php echo $object_attributes->engine_no;?> <br/>
                        च्यासिस नं.: <?php echo $object_attributes->chasis_no;?> <br/>
                        दर्ता नं.: <?php echo $object_attributes->reg_no;?> <br/>
                        बनाउने कम्पनी: <?php echo $object_attributes->manufacturer;?> <br/>
                        बनौट: <?php echo $object_attributes->make;?> <br/>
                        मोडेल: <?php echo $object_attributes->model;?>
                    </td>
                    <td>
                        बनेको वर्ष : <?php echo $object_attributes->year_mfd;?> <br/>
                        घन क्षमता (क्यूविक क्यापासिटी) : <?php echo $object_attributes->engine_capacity;?> <br/>
                        दर्ता मिति : <?php echo $object_attributes->reg_date;?> <br/>
                        सरसामान बाहेक सवारी साधनको घोषित मूल्य : रु. <?php echo $object_attributes->price_vehicle;?> <br/>
                        सरसामानको घोषित मूल्य : रु. <?php echo $object_attributes->price_accessories;?> <br/>
                        जम्मा घोषित मूल्य: रु. <?php echo (float)($object_attributes->price_vehicle + $object_attributes->price_accessories);?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="font-size:8pt">
                        <?php
                        /**
                         * Sub Portfolio-wise  Statements
                         */
                        $usage_limitation = '';
                        $driver_limitation = '';
                        $reaffirm_numbers = '';
                        $cost_table_title = '';
                        switch ($record->sub_portfolio_code)
                        {
                            case IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः–(क) बीमित स्वयं वा (ख) बीमितको साधारण जानकारी र वा सहमतिले सवारी साधन चलाउने व्यक्ति । <br/>चालकसंग उक्त मोटरसाइकल चलाउने अनुमतिपत्र भएको र त्यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "मोटरसाइकलको बीमाशुल्क गणना तालिका";
                                break;

                            case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "निजी सवारी साधनको बीमाशुल्क गणना तालिका";
                                break;

                            case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको व्यावसायिक सवारी साधन दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षणसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त व्यवसायिक सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "व्यावसायिक सवारी साधनको बीमाशुल्क गणना तालिका";
                                break;

                            default:
                                # code...
                                break;
                        }
                        ?>
                        <h4 class="underline">प्रयोगको सीमा</h4>

                        <p><?php echo $usage_limitation?></p><br/>

                        <h4 class="underline">चालक</h4>
                        <p><?php echo $driver_limitation?></p><br/>

                        <h4 class="underline">बीमाशुल्कको हिसाब</h4>
                        <p>संलग्न तालिका बमोजिम</p><br/>

                        <h4 class="underline">अनिवार्य अधिक:  स्वेच्छीक अधिक:</h4><br/>

                        <h4>संलग्न सम्पुष्टी नम्बरहरु: <?php echo $reaffirm_numbers?></h4>
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

        <pagebreak>

        <?php
        /**
         * Policy Premium Card
         */
        $premium_record = (object)[
            'policy_id'     => $record->id,
            'total_premium_amount'  => $record->total_premium_amount,
            'stamp_duty_amount'     => $record->stamp_duty_amount,
            'attributes'    => $record->premium_attributes
        ];
        $this->load->view('premium/snippets/_print_card_overview_MOTOR', ['premium_record' => $premium_record, 'policy_record' => $record, 'title' => $cost_table_title]);
        ?>
    </body>
</html>