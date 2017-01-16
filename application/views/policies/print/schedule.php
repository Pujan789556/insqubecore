<?php defined('BASEPATH') OR exit('No direct script access allowed');

$object_attributes = json_decode($record->object_attributes);

$premium_attributes = json_decode($record->premium_attributes);
?>
<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <?php
    /**
     * Load Styles (inline)
     */
    $this->load->view('print/style/schedule')
    ?>
    </head>
    <body>

        <table class="table table-bordered table-condensed" cellpadding="0" cellspacing="0" style="margin:0">
            <tbody>
                <tr><td  colspan="2" align="center"><h3>मोटरसाइकल बीमालेखको तालिका (सेड्युल)</h3></td></tr>
                <tr><td colspan="2">बीमालेख नं.: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" style="padding:0; margin:0">
                        <table class="table no-border" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="border-b-dark">
                                    <h4 class="border-b">बीमीतको विवरण</h4><br/>
                                    नाम थार: <?php echo $record->customer_name;?><br/>
                                    ठेगाना: <?php echo get_contact_widget($record->customer_contact, true);?><br/>
                                    पेशा: <?php echo $record->customer_profession;?>
                                </td>
                            </tr>
                            <tr>
                                <td class="border-b-dark">लागू हुने भौगोलिक क्षेत्र: नेपाल, भारत, भूटान, बंगलादेश र चीनको स्वशासित क्षेत्र तिब्बत</td>
                            </tr>

                            <tr>
                                <td>
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td style="padding:0; margin:0" >
                        <table class="table no-border table-condensed" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="border-b">बीमालेखको किसिम: <?php echo _PO_MOTOR_sub_portfolio_dropdown(FALSE)[$object_attributes->sub_portfolio]?></td>
                            </tr>

                            <tr>
                                <td class="border-b">प्रस्तावकको नाम: <?php echo nl2br($this->security->xss_clean($record->proposer));?></td>
                            </tr>

                            <tr>
                                <td class="border-b">बीमा प्रस्ताव भएको मिति: <?php echo $record->proposed_date?></td>
                            </tr>

                            <tr>
                                <td class="border-b">बीमालेख जारी भएको स्थान र मिति: <?php echo $this->dx_auth->get_branch_code()?>, <?php echo $record->issue_date?></td>
                            </tr>

                            <tr>
                                <td class="border-b">रक्षावरण गरिएका जोखिमहरु: <?php echo _PO_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
                            </tr>

                            <tr>
                                <td class="border-b">
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
                                <td class="border-b">बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td class="border-b-dark">बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म</td>
                            </tr>

                            <tr>
                                <td style="margin:0; padding:0;">
                                    <table class="table table-condensed no-border no-margin" cellpadding="0" cellspacing="0">
                                        <tr><td colspan="2"><h4 class="border-b">बीमाशुल्क</h4><br/></td></tr>

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
                                            <td class="totals cost border-top">
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
                                            <td class="totals">मु. अ. क.(VAT) सहित जम्मा शुल्क</td>
                                            <td class="totals cost border-top">
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
                    <td colspan="2">
                        <h4 class="border-b">प्रयोगको सीमा</h4>
                        <p>यस बीमालेख अन्तर्गत रक्षावरण गरिएको सवारी साधन भाडा, इनाम, संगठनात्मक दौड, प्रतियोगिता, टिकाउ परीक्षण, गति परीक्षण वा ढुवानी कार्यसंग सम्बन्धित कुनै प्रयोजनमा प्रयोग गर्न पाइने छैन ।</p><br/>

                        <h4 class="border-b">चालक</h4>
                        <p>निम्नमध्ये कुनैः–(क) बीमित स्वयं वा (ख) बीमितको साधारण जानकारी र वा सहमतिले सवारी साधन चलाउने व्यक्ति । <br/>चालकसंग उक्त मोटरसाइकल चलाउने अनुमतिपत्र भएको र त्यस्तो अनुमतिपत्र राख्न वा प्राप्त गर्न अयोग्य नठहरिएको हुनुपर्नेछ ।</p><br/>

                        <h4 class="border-b">बीमाशुल्कको हिसाब</h4>
                        <p>संलग्न तालिका बमोजिम</p><br/>

                        <h4 class="border-b">अनिवार्य अधिक:  स्वेच्छीक अधिक:</h4><br/>

                        <h4 class="border-b">संलग्न सम्पुष्टी नम्बरहरु: </h4>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="no-margin">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <table class="table no-border no-margin">
            <tr>
                <td width="50%">
                    कर बिजक नं: <br/>
                    मिति:<br/>
                    रु.:
                </td>
                <td align="left">
                    <h4>निमित्त, <?php echo $this->settings->orgn_name_np?></h4>
                    <h4 class="border-b">अधिकार प्राप्त अधिकारीको</h4>
                    दस्तखत:<br/>
                    नाम थर:<br/>
                    छाप:<br/>
                    दर्जा:
                </td>
            </tr>
        </table>
    </body>
</html>