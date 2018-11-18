<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : Motor - Motorcycle
 */
$object_attributes      = json_decode($record->object_attributes);

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
    ?>
    </head>
    <body>
        <?php
        /**
         * Header & Footer
         */
        ?>
        <!--mpdf
            <?php echo _POLICY_schedule_header_footer($record);?>
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
                                    <?php
                                    /**
                                     * Insured Party, Financer, Other Financer, Careof
                                     */
                                    $this->load->view('policies/print/_schedule_insured_party', ['lang' => 'np']);
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
                                <td>बीमालेखको किसिम: <?php echo htmlspecialchars($record->portfolio_name); ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo nl2br(htmlspecialchars($record->proposer));?></td>
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
                                $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                                ?>
                                <td>बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td>
                                    बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म
                                    (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, 'np'); ?>)
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <h4>बीमाशुल्क</h4>
                                    <?php
                                    /**
                                     * Policy Premium Card
                                     */
                                    $cost_summary_view = 'endorsements/snippets/_cost_calculation_table_summary_MOTOR';
                                    $this->load->view($cost_summary_view, ['endorsement_record' => $endorsement_record]);
                                    ?>
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
                        $voluntary_excess_key = $premium_computation_table->dr_voluntary_excess  ?? NULL;
                        ?>
                        <table class="table no-border">
                            <tr>
                                <td>इन्जिन नं.:</td>
                                <td><?php echo htmlspecialchars($object_attributes->engine_no);?></td>
                            </tr>
                            <tr>
                                <td>च्यासिस नं.:</td>
                                <td><?php echo htmlspecialchars($object_attributes->chasis_no);?></td>
                            </tr>
                            <tr>
                                <td>दर्ता नं.:</td>
                                <td><?php echo $object_attributes->reg_no_prefix . ' ' . $object_attributes->reg_no;?></td>
                            </tr>
                            <tr>
                                <td>बनाउने कम्पनी:</td>
                                <td><?php echo htmlspecialchars($object_attributes->manufacturer);?></td>
                            </tr>
                            <tr>
                                <td>बनौट:</td>
                                <td><?php echo htmlspecialchars($object_attributes->make);?></td>
                            </tr>
                            <tr>
                                <td>मोडेल:</td>
                                <td><?php echo $object_attributes->model;?></td>
                            </tr>
                            <tr>
                                <td>स्वेच्छीक अधिक:</td>
                                <td>रु.<?php if($voluntary_excess_key && isset($dd_voluntary_excess[$voluntary_excess_key]))
                                    {
                                        echo number_format($dd_voluntary_excess[$voluntary_excess_key], 2);
                                    }?>
                                </td>
                            </tr>
                            <tr>
                                <td>अनिवार्य अधिक:</td>
                                <td>
                                    <?php
                                    if( strtoupper($record->policy_package) === IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE )
                                    {
                                        $compulsory_excess = _PO_MOTOR_get_compulsory_excess( $tariff_record, $object_attributes->year_mfd);
                                        echo $compulsory_excess->amount ? 'रु. ' . number_format($compulsory_excess->amount, 2) : '';
                                    }?>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="table no-border">
                            <tr>
                                <td>बनेको वर्ष:</td>
                                <td><?php echo htmlspecialchars($object_attributes->year_mfd);?></td>
                            </tr>
                            <tr>
                                <td>घन क्षमता (क्यूविक क्यापासिटी):</td>
                                <td><?php echo htmlspecialchars($object_attributes->engine_capacity) . ' ' . $object_attributes->ec_unit;?></td>
                            </tr>
                            <tr>
                                <td>चालक सहित यात्रु । भारबहन क्षमाता</td>
                                <td>
                                    <?php echo $object_attributes->seating_capacity . ' seats';

                                    $carrying_capacity = $object_attributes->carrying_capacity ?? NULL;
                                    if( $carrying_capacity > 0)
                                    {
                                        echo ' | ' . $object_attributes->carrying_capacity . ' ' . _OBJ_MOTOR_carrying_unit_dropdown(FALSE)[$object_attributes->carrying_unit];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>दर्ता मिति: </td>
                                <td><?php echo $object_attributes->reg_date;?></td>
                            </tr>
                            <tr>
                                <td>सरसामान बाहेक सवारी साधनको घोषित मूल्य:</td>
                                <td align="right">रु. <?php echo number_format($object_attributes->price_vehicle, 2);?></td>
                            </tr>
                            <tr>
                                <td>सरसामानको घोषित मूल्य:</td>
                                <td align="right">रु. <?php echo number_format($object_attributes->price_accessories, 2);?></td>
                            </tr>

                            <?php
                            $trailer_price = $object_attributes->trailer_price ?? 0;
                            if($trailer_price): ?>
                                <tr>
                                    <td>ट्रेलरको घोषित मूल्य:</td>
                                    <td align="right">रु. <?php echo number_format($trailer_price, 2);?></td>
                                </tr>
                            <?php endif ?>

                            <tr>
                                <td><strong>जम्मा घोषित मूल्य:</strong></td>
                                <td align="right"><strong>रु. <?php echo number_format( (float)$object_attributes->price_vehicle + $object_attributes->price_accessories + $trailer_price, 2 );?></strong></td>
                            </tr>
                        </table>
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
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>

        <pagebreak>
        <?php
        /**
         * Policy Premium Card
         */
        $cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($record->portfolio_id, 'print');
        $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record, 'title' => $cost_table_title]);
        ?>



        <?php
        /**
         * Let's Build Vehicle Certificate
         */
        $certificate_goodies = _OBJ_MOTOR_vehicle_certificate_goodies()[$record->portfolio_id];

        if($record->status == IQB_POLICY_STATUS_ACTIVE ):
        ?>
            <pagebreak>
            <table class="table no-border">
                <tr>
                    <td align="center" colspan="2">
                        <img src="<?php echo logo_path();?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2"><br/><h2>सवारी साधन बीमाको प्रमाणपत्र</h2><br/><br/></td>
                </tr>
                <tr>
                    <td width="40%"><strong>प्रमाणपत्र जारी गर्ने बीमकको नाम र ठेगाना:</strong></td>
                    <td>
                        <?php
                        echo htmlspecialchars($this->settings->orgn_name_np . ', ' . $record->branch_name_np);
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
                    <td><strong>२. ठेगाना:</strong></td>
                    <td><?php
                    /**
                     * Parse Address Record - Customer
                     */
                    $customer_address_record = parse_address_record($record, 'addr_customer_');
                    echo address_widget_two_lines($customer_address_record); ?></td>
                </tr>
                <tr>
                    <td><strong>३. टेलिफोन नं.:</strong></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><strong>४. बीमालेख नं.:</strong></td>
                    <td><?php echo $record->code; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>५. तेश्रो पक्ष प्रतिको दायित्व बीमाको सीमा</strong></td>
                </tr>
                <tr>
                    <td><strong>&nbsp;&nbsp;&nbsp;&nbsp;(क) मानिसको शारीरिक क्षति वापत:</strong></td>
                    <td><?php echo $certificate_goodies['tp_human_damage'] ?></td>
                </tr>
                <tr>
                    <td><strong>&nbsp;&nbsp;&nbsp;&nbsp;(ख) सम्पत्तिको क्षति वापत:</strong></td>
                    <td><?php echo $certificate_goodies['tp_property_damage'] ?></td>
                </tr>
                <tr>
                    <td>
                        <?php
                        $label = '६. प्रति यात्री बीमाङ्क';
                        if($record->portfolio_id == IQB_SUB_PORTFOLIO_MOTORCYCLE_ID )
                        {
                            $label = '६. चालक तथा पछाडि बस्ने एक यात्रुको प्रतिव्यक्ति बीमाङ्क';
                        }
                         ?>
                        <strong><?php echo $label; ?></strong>
                    </td>
                    <td><?php echo $certificate_goodies['si_per_passenger'] ?></td>
                </tr>
                <?php if($record->portfolio_id != IQB_SUB_PORTFOLIO_MOTORCYCLE_ID ): ?>
                    <tr>
                        <td><strong>७. चालक तथा अन्य बीमित कर्मचारीहरुको प्रति व्यक्ति बीमाङ्क</strong></td>
                        <td><?php echo $certificate_goodies['si_driver_other'] ?></td>
                    </tr>
                <?php endif ?>
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
                        <td><?php echo htmlspecialchars($object_attributes->make) ?></td>
                        <td><?php echo htmlspecialchars($object_attributes->model) ?></td>
                        <td><?php echo htmlspecialchars( implode(' / ', array_filter([$object_attributes->reg_no_prefix . ' ' . $object_attributes->reg_no, $object_attributes->reg_date]) ) ) ?></td>
                        <td><?php echo htmlspecialchars( $object_attributes->chasis_no ) ?></td>
                        <td><?php echo htmlspecialchars( $object_attributes->engine_no ) ?></td>
                        <td>
                            <?php
                            echo $object_attributes->engine_capacity, $object_attributes->ec_unit;
                            echo isset($object_attributes->carrying_capacity) ? ' / ' . $object_attributes->carrying_capacity : '' ?>
                            </td>
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
                        <td width="30%"><strong>छाप</strong></td>
                        <td width="20%"><strong>कैफियत</strong></td>
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

            <div style="font-size: 9pt">
                <p><strong>द्रष्टब्य:</strong></p>
                <p>१.यस प्रमाणपत्रमा उल्लेख भएको बीमा सम्बंधि व्यवस्थाहरु सम्बंधित बीमालेखमा उल्लेख भए बमोजिम
                हुनेछ ।</p>
                <p>२. यो प्रमाणपत्र हराएमा तत्काल नजिकको प्रहरी कार्यालयमा लिखित सूचना दिनु पर्नेछ ।</p>
                <p>३. यो प्रमाणपत्र हराएमा वा नासिएमा यथाशिध्र बीमकबाट प्रतिलिपि दिनु पर्नेछ ।</p>
                <p>४. यो प्रमाणपत्र अनिवार्य रुपमा सवारी साधनमा राख्नु पर्नेछ ।</p>
            </div>

        <?php endif; ?>
    </body>
</html>