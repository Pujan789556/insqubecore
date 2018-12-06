<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Discharge Voucher Print
 */
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
                                <td align="left"><img style="margin-bottom: 20px;" src="'. logo_path() .'" alt="'.$this->settings->orgn_name_np.'" width="200"></td>
                                <td align="right"><h2>दाबी फछ्र्यौट पूर्जा</h2></td>
                            </tr>
                        </table>
                    </htmlpageheader>
                    <sethtmlpageheader name="myheader" show-this-page="1" value="on" />
                    <htmlpagefooter name="myfooter" value="on">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t" align="left">'. $this->settings->orgn_name_np .'</td>
                                <td class="border-t" align="right">CLAIM NO. '. $record->claim_code .'</td>
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

        <br><br>
        <p>
            बीमक श्री नेको इन्सुरेन्स लि. बाट मेरो/हाम्रो नाममा जारी भएको बीमालेख नं. <strong><?php echo $record->policy_code?></strong> नविकरण नं. <strong><?php echo $endorsement->id ?></strong>  अन्तर्गत बीमा भएको सम्पतिमा मिति (ई. सं.) <strong><?php echo $record->accident_date ?></strong> मा दुर्घटना भएको क्षति/हानी नोक्सानीको क्षतिपूर्ति बापतको तपसिलमा उल्लेख भएका शीर्षक र सो अन्तर्गत उल्लेखित रकम समेत जम्मा रकम रु <strong><?php echo number_format($record->settlement_claim_amount, 2); ?></strong> (अक्षरेपी  रु <strong><?php echo ucfirst( amount_in_words( number_format($record->settlement_claim_amount, 2, '.', '') ) );?></strong> मात्र ।) उपलब्ध गराएमा बीमकलाई यस दावी नं. <strong><?php echo $record->claim_code ?></strong> अन्तर्गतको दायित्वबाट  मुक्त गरिदिन मन्जुर गर्दछु/गर्दछौं । यस दाबी बापत म/हामी/संस्था लाई प्राप्त हुनु पर्ने रकम चेक मार्फत वा तल उल्लेखित बैंक खातामा सोझौ रकमान्तर गर्न मेरो/हाम्रो मन्जुरी रहेको छ । यसमा अन्यथा भए गरेमा यसै कागजको आधारमा बदर गरीदिन मन्जुर छु/छौं  भनि राजिखुशी साथ यो सहि छाप गरि दिए/दियौं ।
        </p>
        <br>
        <p>IN BLOCK LETTER (खातामा सोझै रकम स्थानान्तरण गर्न चाहनेको लागि मात्र )</p>
        <table>
            <tr>
                <td width="30%"><?php echo strtoupper('Account Holder Name') ?>:</td>
                <td></td>
            </tr>
            <tr>
                <td><?php echo strtoupper('Account Number') ?>:</td>
                <td></td>
            </tr>
            <tr>
                <td><?php echo strtoupper('Bank Name') ?>:</td>
                <td></td>
            </tr>
        </table>
        <p>खातवाल  र बिमितको नाम हुवहु मिलेको अबस्थामा मात्र माथि उल्लेख गरिएको खातामा रकम स्थानान्तरण गरीने छ । </p>
        इति सम्बत् <strong><?php echo date('l j F Y') ?></strong> रोज शुभम् ।
        <br><br><br>
        <table class="no-border">
            <tr>
                <td class="no-padding" width="50%">
                    <table>
                        <tr><td><strong>बीमितको तर्फबाट:</strong></td></tr>
                        <tr><td>ऋणि:</td></tr>
                        <tr><td>बीमितको सहि:</td></tr>
                        <tr><td>नाम थर:</td></tr>
                        <tr><td>ठेगाना:</td></tr>
                        <tr><td>सम्पर्क नं.:</td></tr>
                        <tr><td>मिति:</td></tr>
                    </table>
                </td>
                <td class="no-padding">
                    <table>
                        <tr><td><strong>बीमकको तर्फबाट जारी गर्ने अधिकारी:</strong></td></tr>
                        <tr><td>हस्ताक्षर:</td></tr>
                        <tr><td>नाम थर:</td></tr>
                        <tr><td>पद:</td></tr>
                        <tr><td>मिति:</td></tr>
                        <tr><td>कार्यालयको छाप:</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br>
        <table class="no-border">
            <tr>
                <td class="no-padding" width="50%">
                    <table>
                        <tr><td><strong>साक्षी</strong></td></tr>
                        <tr><td>साक्षीको सहि:</td></tr>
                        <tr><td>नाम थर:</td></tr>
                        <tr><td>ठेगाना:</td></tr>
                        <tr><td>मिति</td></tr>
                    </table>
                </td>
                <td class="no-padding">
                    <table>
                        <tr><td><strong>बैंक तथा बित्तीय संस्थाको तर्फबाट</strong></td></tr>
                        <tr><td>श्री ......................................................................................</td></tr>
                        <tr><td>हस्ताक्षर:</td></tr>
                        <tr><td>नाम थर:</td></tr>
                        <tr><td>पद:</td></tr>
                        <tr><td>मिति:</td></tr>
                        <tr><td>कार्यालयको छाप:</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>