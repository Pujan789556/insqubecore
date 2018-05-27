<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : Motor - Motorcycle
 */

$object_attributes      = json_decode($record->object_attributes);
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;


/**
 * Let's get the Required Records
 */
$policy_object      = get_object_from_policy_record($record);
$premium_goodies    = _TXN_MOTOR_premium_goodies($record, $policy_object);
$tariff_record      = $premium_goodies['tariff_record'];

$premium_computation_table = json_decode($endorsement_record->premium_computation_table);


/**
 * Sub Portfolio-wise  Statements
 */
$usage_limitation = '';
$driver_limitation = '';
$reaffirm_numbers = '';
$schedule_table_title = '';
$cost_table_title = '';
switch ($record->portfolio_id)
{
    case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
        $schedule_table_title   = 'मोटरसाइकल बीमालेखको तालिका (सेड्युल)';
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

    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name_en;

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
                <tr><td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <?php echo $record->code;?></td></tr>
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
                                <td>बीमालेखको किसिम: <?php echo $record->portfolio_name; ?></td>
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
                                    <table class="table table-condensed no-border">
                                        <tr>
                                            <td><strong>बीमाशुल्क</strong></td>
                                            <td class="text-right"><?php echo number_format((float)$total_premium, 2)?></td>
                                        </tr>
                                        <tr>
                                            <td>टिकट</td>
                                            <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_stamp_duty, 2)?></td>
                                        </tr>
                                        <tr>
                                            <td>मु. अ. क. (VAT)</td>
                                            <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_vat, 2)?></td>
                                        </tr>
                                        <tr><td colspan="2" style="height:2px;"><hr style="margin:0" /></td></tr>
                                        <tr>
                                            <td class="border-t"><strong>मु. अ. क.(VAT) सहित जम्मा शुल्क (रु)</strong></td>
                                            <td class="text-right border-t"><strong><?php echo number_format( (float)$grand_total , 2);?></strong></td>
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
                        <?php
                        $dd_voluntary_excess = _PO_MOTOR_voluntary_excess_dropdown($tariff_record->dr_voluntary_excess, false, '');
                        $compulsory_excess = _PO_MOTOR_get_compulsory_excess( $tariff_record, $object_attributes->year_mfd);
                        ?>

                        इन्जिन नं.: <?php echo $object_attributes->engine_no;?> <br/>
                        च्यासिस नं.: <?php echo $object_attributes->chasis_no;?> <br/>
                        दर्ता नं.: <?php echo $object_attributes->reg_no;?> <br/>
                        बनाउने कम्पनी: <?php echo $object_attributes->manufacturer;?> <br/>
                        बनौट: <?php echo $object_attributes->make;?> <br/>
                        मोडेल: <?php echo $object_attributes->model;?><br>
                        स्वेच्छीक अधिक: रु.<?php echo $dd_voluntary_excess[$premium_computation_table->dr_voluntary_excess];?><br>
                        अनिवार्य अधिक: रु.<?php  echo $compulsory_excess->amount ?? ''?>


                    </td>
                    <td>
                        बनेको वर्ष : <?php echo $object_attributes->year_mfd;?> <br/>
                        घन क्षमता (क्यूविक क्यापासिटी) : <?php echo $object_attributes->engine_capacity;?> <br/>
                        दर्ता मिति : <?php echo $object_attributes->reg_date;?> <br/>
                        सरसामान बाहेक सवारी साधनको घोषित मूल्य : रु. <?php echo $object_attributes->price_vehicle;?> <br/>
                        सरसामानको घोषित मूल्य : रु. <?php echo $object_attributes->price_accessories;?> <br/>
                        जम्मा घोषित मूल्य: रु. <?php echo number_format( (float)$object_attributes->price_vehicle + $object_attributes->price_accessories, 2 );?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="font-size:8pt">
                        <?php
                        /**
                         * Sub Portfolio-wise  Statements

                        $usage_limitation = '';
                        $driver_limitation = '';
                        $reaffirm_numbers = '';
                        $cost_table_title = '';
                        switch ($record->portfolio_id)
                        {
                            case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः–(क) बीमित स्वयं वा (ख) बीमितको साधारण जानकारी र वा सहमतिले सवारी साधन चलाउने व्यक्ति । <br/>चालकसंग उक्त मोटरसाइकल चलाउने अनुमतिपत्र भएको र त्यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "मोटरसाइकलको बीमाशुल्क गणना तालिका";
                                break;

                            case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "निजी सवारी साधनको बीमाशुल्क गणना तालिका";
                                break;

                            case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
                                $usage_limitation = 'यस बीमालेख अन्तर्गत रक्षावरण गरिएको व्यावसायिक सवारी साधन दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षणसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।';
                                $driver_limitation = 'निम्नमध्ये कुनैः– (क) बीमित स्वयं वा (ख) बीमितको आदेश वा अनुमतिले सवारी साधन चलाउने व्यक्ति ।<br/>चालकसंग उक्त व्यवसायिक सवारी साधन चलाउने अनुमतिपत्र भएको र यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।';
                                $cost_table_title = "व्यावसायिक सवारी साधनको बीमाशुल्क गणना तालिका";
                                break;

                            default:
                                # code...
                                break;
                        }
                        */
                        ?>
                        <p><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details));?></p>
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
        $cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($record->portfolio_id, 'print');
        $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record, 'title' => $cost_table_title]);
        ?>

        <pagebreak>

        <?php
        /**
         * Let's Build Vehicle Certificate
         */
        $certificate_goodies = _OBJ_MOTOR_vehicle_certificate_goodies()[$record->portfolio_id];
        ?>
        <table class="table no-border">
            <tr>
                <td align="center" colspan="2">
                    <img src="<?php echo logo_url();?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2"><br/><h2>सवारी साधन बीमाको प्रमाणपत्र</h2><br/><br/></td>
            </tr>
            <tr>
                <td width="50%"><strong>प्रमाणपत्र जारी गर्ने बीमकको नाम र ठेगाना:</strong></td>
                <td>
                    <?php
                    $branch_contact_prefix = $this->settings->orgn_name_np . ', ' . $record->branch_name_np;
                    echo get_contact_widget_two_lines($record->branch_contact, $branch_contact_prefix);
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>प्रमाणपत्र नं.:</strong></td>
                <td><?php echo $record->code; ?></td>
            </tr>
            <tr>
                <td><strong>प्रमाणपत्र जारी मिति:</strong></td>
                <td><?php echo $record->issued_date ?></td>
            </tr>
            <tr>
                <td><strong>१. बीमितको नाम:</strong></td>
                <td><?php echo $record->customer_name; ?></td>
            </tr>
            <tr>
                <td><strong>२. ठेगाना, टेलिफोन नं.:</strong></td>
                <td><?php echo get_contact_widget_two_lines($record->customer_contact); ?></td>
            </tr>
            <tr>
                <td><strong>३. बीमालेख नं.:</strong></td>
                <td><?php echo $record->code; ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>४. तेश्रो पक्ष प्रतिको दायित्व बीमाको सीमा</strong></td>
            </tr>
            <tr>
                <td><strong>&nbsp;&nbsp;&nbsp;&nbsp;(क) मानिसको शारीरिक क्षति वापत:</strong></td>
                <td>रु <?php echo number_format($certificate_goodies['tp_human_damage'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>&nbsp;&nbsp;&nbsp;&nbsp;(ख) सम्पत्तिको क्षति वापत:</strong></td>
                <td>रु <?php echo number_format($certificate_goodies['tp_property_damage'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>५. प्रति यात्री बीमाङ्क</strong></td>
                <td>रु <?php echo number_format($certificate_goodies['si_per_passenger'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>६. चालक तथा अन्य बीमित कर्मचारीहरुको प्रति व्यक्ति बीमाङ्क</strong></td>
                <td>रु <?php echo number_format($certificate_goodies['si_driver_other'], 2) ?></td>
            </tr>
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <td colspan="6"><strong>सवारी साधनको विवरण</strong></td>
                </tr>
                <tr>
                    <td><strong>किसिम</strong></td>
                    <td><strong>बनौट</strong></td>
                    <td><strong>दर्ता नं./मिति</strong></td>
                    <td><strong>चेसीस नं.</strong></td>
                    <td><strong>इन्जीन नं.</strong></td>
                    <td><strong>भारबहनक्षमता/सिट संख्या</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $object_attributes->make ?></td>
                    <td><?php echo $object_attributes->model ?></td>
                    <td><?php echo implode(' / ', array_filter([$object_attributes->reg_no, $object_attributes->reg_date]) ) ?></td>
                    <td><?php echo $object_attributes->chasis_no ?></td>
                    <td><?php echo $object_attributes->engine_no ?></td>
                    <td><?php echo $object_attributes->engine_capacity, $object_attributes->ec_unit, ' / ', $object_attributes->carrying_capacity ?></td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <td colspan="6"><strong>बीमालेख वहाल रहने अवधि</strong></td>
                </tr>
                <tr>
                    <td><strong>देखि</strong></td>
                    <td><strong>सम्म</strong></td>
                    <td><strong>प्रमाणित गर्ने अधिकारीको हस्ताक्षर र मिति</strong></td>
                    <td><strong>छाप</strong></td>
                    <td><strong>कैफियत</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $record->start_date ?></td>
                    <td><?php echo $record->end_date ?></td>
                    <td><br><br><br><br><br><br></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <br>

        <p><strong>द्रष्टब्य:</strong></p>
        <p>१.यस प्रमाणपत्रमा उल्लेख भएको बीमा सम्बन्धी व्यवस्थाहरु सम्बन्धित बीमालेखमा उल्लेख भए बमोजिम
        हुनेछ ।</p>
        <p>२. यो प्रमाणपत्र हराएमा तत्काल नजिकको प्रहरी कार्यालयमा लिखित सूचना दिनु पर्नेछ ।</p>
        <p>३. यो प्रमाणपत्र हराएमा वा नासिएमा यथाशिध्र बीमकबाट प्रतिलिपि दिनु पर्नेछ ।</p>
        <p>४. यो प्रमाणपत्र अनिवार्य रुपमा सवारी साधनमा राख्नु पर्नेछ ।</p>
    </body>
</html>