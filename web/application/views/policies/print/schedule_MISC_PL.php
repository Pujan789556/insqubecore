<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - PUBLIC LIABILITY(PL)
 */
$this->load->helper('ph_misc_pl');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = "Public Liability (Schedule)";
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
            <thead><tr><td colspan="3" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr>
                    <td><strong><?php echo _POLICY_schedule_title_prefix($record->status, 'en')?>:</strong> <?php echo $record->code;?></td>

                    <?php
                    /**
                     * Agent Details
                     */
                    $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                    ?>
                    <td><strong>Agent:</strong> <?php echo $agent_text;?> </td>
                </tr>
                <tr>
                    <td>
                        <?php
                        /**
                         * Insured Party, Financer, Other Financer, Careof
                         */
                        $this->load->view('policies/print/_schedule_insured_party', ['lang' => 'en']);
                        ?>
                    </td>
                    <td>
                        <strong>Location of Risk:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->risk_locaiton)) ?><br><br>

                        <strong>Period of Insurance:</strong><br>
                        From: <strong><?php echo $record->start_date ?></strong><br>
                        To: <strong><?php echo $record->end_date ?></strong>
                        (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, 'en'); ?>)
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <?php
                        /**
                         * Load Cost Calculation Table
                         */
                        $this->load->view('endorsements/snippets/premium/_index',
                            ['lang' => 'en', 'endorsement_record' => $endorsement_record]
                        );
                        ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <?php
                        $form_elements = _OBJ_MISC_PL_validation_rules($record->portfolio_id);
                        $section_elements   = $form_elements['public_liability'];
                        $items              = $object_attributes->public_liability ?? NULL;
                        $item_count         = count( $items->limit ?? [] );
                        ?>
                        <strong>PUBLIC LIABILITY</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td><?php echo $elem['label'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                for ($i=0; $i < $item_count; $i++): ?>
                                    <tr>
                                        <?php
                                        foreach($section_elements as $elem):
                                            $key =  $elem['_key'];
                                            $value = $items->{$key}[$i];

                                            // If we have dropdown, load label from this
                                            $dd_data = $elem['_data'] ?? NULL;
                                            if( $dd_data )
                                            {
                                                $value = $dd_data[$value] ?? $value;
                                            }

                                            // Comput total
                                            if( $key == 'limit' )
                                            {
                                                // format this to echo
                                                $value = number_format(floatval($value), 2);
                                            }
                                        ?>

                                            <td <?php echo $key == 'limit' ? 'class="text-right"' : '' ?>>
                                                <?php echo htmlspecialchars($value)?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endfor ?>
                                <tr>
                                    <td class="text-bold">Total Public Liability(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table><br>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>