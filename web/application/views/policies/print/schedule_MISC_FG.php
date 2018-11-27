<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - FIDELITY GUARANTEE (FG)
 */
$object_attributes  = json_decode($record->object_attributes);
$schedule_table_title   = 'निष्ठा जमानी बीमालेख';
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
                <tr>
                    <td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <strong><?php echo $record->code;?></strong></td>
                </tr>
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
                                    <strong></strong><br/>
                                    बीमांक रकम (रु): <?php echo number_format((float)$record->object_amt_sum_insured, 2)?><br>
                                    (संलग्न बीमितको विवरण सूचि बमोजिम ।)
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

                    <td width="50%" class="no-padding no-border">
                        <?php
                        /**
                         * Basic Information
                         */
                        $this->load->view('policies/print/_schedule_basic',
                            ['lang' => 'np', 'record' => $record]
                        );
                        ?>
                        <table class="table">
                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Load Cost Calculation Table
                                     */
                                    $this->load->view('endorsements/snippets/premium/_index',
                                        ['lang' => 'np', 'endorsement_record' => $endorsement_record]
                                    );
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>यस बीमालेखले रक्षावरण गरेका बीमित कर्मचारीहरु</strong><br/>
                        <?php
                        $form_elements = _OBJ_MISC_FG_validation_rules($record->portfolio_id);

                        /**
                         * Item List
                         */
                        $section_elements   = $form_elements['items'];


                        $items              = $object_attributes->items ?? NULL;
                        $item_count         = count( $items->sum_insured ?? [] );
                        ?>

                        <table class="table table-bordered table-condensed no-margin">
                            <thead>
                                <tr>
                                    <td>क्र सं</td>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td><?php echo $elem['label'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php for ($i=0; $i < $item_count; $i++): ?>
                                    <tr>
                                        <td><?php echo ($i+1) ?></td>
                                        <?php foreach($section_elements as $elem):
                                            $key =  $elem['_key'];
                                            $value = $items->{$key}[$i];
                                        ?>

                                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                                <?php echo htmlspecialchars($value)?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endfor ?>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>
            </tbody>
        </table><br/>

        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>