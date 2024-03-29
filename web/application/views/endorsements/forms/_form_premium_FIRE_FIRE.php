<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Premium Form : FIRE - FIRE
 */
$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options      = json_decode($record->premium_compute_options ?? NULL);
$premium_computation_table_arr  = json_decode($record->premium_compute_options ?? NULL, TRUE);

echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        ['id' => $record->id]);

    /**
     * Premium Summary Table
     */
    $this->load->view('endorsements/snippets/_premium_summary');
?>

    <div class="row">
        <div class="col-sm-6">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Fire Items Summary</h4>
                </div>
                <?php if($object_attributes->item_attached === 'N'):
                    $items              = $object_attributes->items;
                    $item_count         = count($items);
                    $i = 1;
                    ?>
                    <table class="table table-responsive table-condensed">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>Item</th>
                                <th>Ownership</th>
                                <th>Sum Insured (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item_record): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item_record->category ]?></td>
                                    <td><?php echo _OBJ_FIRE_FIRE_item_ownership_dropdown(FALSE)[ $item_record->ownership ]; ?></td>
                                    <td class="text-right"><?php echo number_format($item_record->sum_insured, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="table table-bordered table-condensed no-margin">
                        <tr>
                            <th>Sum Insured (Rs)</th>
                            <td>
                                <?php echo $object_attributes->sum_insured; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Download Item List</th>
                            <td>
                                <?php echo anchor('objects/download/' . $object_attributes->document, 'Download', 'target="_blank"') ?>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($object_attributes->item_attached === 'Y'):?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Discount/Additional Charges (Rate & Amount)</h4>
            </div>
            <div class="box-body form-horizontal">
                <div class="row">
                    <div class="col-sm-6">
                        <?php
                        /**
                         * Additional Charge/Discount Rates
                         */
                        $premium_file_additional_rates = $form_elements['premium_file_additional_rates'];
                        $form_section_record = $premium_compute_options->file ?? NULL;

                        $this->load->view('templates/_common/_form_components_horz', [
                            'form_elements' => $premium_file_additional_rates,
                            'form_record'   => $form_section_record,
                            'grid_label'    => 'col-sm-8',
                            'grid_form_control' => 'col-sm-4'
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <?php
                        /**
                         * Additional Charge/Discount Amount
                         */
                        $premium_file_additional_amount = $form_elements['premium_file_additional_amount'];

                        $this->load->view('templates/_common/_form_components_horz', [
                            'form_elements' => $premium_file_additional_amount,
                            'form_record'   => $form_section_record,
                            'grid_label'    => 'col-sm-6',
                            'grid_form_control'    => 'col-sm-12'
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Distribution by Risks</h4>
            </div>
            <div class="box-body form-inline">
                <table class="table table-responsive table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th>Risk</th>
                            <th>Premium(Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $premium_elements = $form_elements['premium_file_risks'];
                        foreach($portfolio_risks as $risk_id=>$risk_name):
                            /**
                             * Add Value to the first element
                             */
                            $premium_elements[0]['_default']            = $risk_id;
                            $premium_elements[0]['_extra_html_below']   = $risk_name;

                            /**
                             * Format Field Name
                             *
                             * Format: premium[file][<field>][<risk_id>]
                             *
                             */
                            $formatted_premium_elements = [];
                            foreach ($premium_elements as $elem)
                            {
                                $elem['field'] .= "[{$risk_id}]";
                                $formatted_premium_elements[] = $elem;
                            }


                            /**
                             * Apply Premium  We have $premium_compute_options
                             */
                            if($premium_computation_table_arr)
                            {
                                // Premium
                                $premium = $premium_computation_table_arr['file']['premium'][$risk_id] ?? '';
                                $formatted_premium_elements[1]['_default'] = $premium;
                            }
                            ?>
                            <tr>
                                <?php
                                /**
                                 * Load Form Components
                                 */
                                $this->load->view('templates/_common/_form_components_table', [
                                    'form_elements' => $formatted_premium_elements,
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Discount/Additional Charges (Rates)</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Additional Charge/Discount Rates
                 */
                $form_section_record = $premium_compute_options->manual ?? NULL;
                $premium_manual_additional_rates = $form_elements['premium_manual_additional_rates'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $premium_manual_additional_rates,
                    'form_record'   => $form_section_record,
                    'grid_label'    => 'col-sm-8',
                    'grid_form_control' => 'col-sm-4'
                ]);
                ?>
            </div>
        </div>
        <?php
        $i = 0;
        foreach($items as $item_record ): ?>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title"><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item_record->category ]?> <em>( Sum Insured Rs. <?php echo $item_record->sum_insured; ?>)</em> - Premium Table</h4>
                </div>
                <div class="box-body form-inline" style="overflow-x: scroll;">
                    <table class="table table-responsive table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>Risk</th>
                                <th>Rate (Rs. Per Thousand)</th>
                                <th>Apply NWL?</th>
                                <th>Apply FFA?</th>
                                <th>Apply SDD?</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $premium_elements = $form_elements['premium_manual_risks'];
                            foreach($portfolio_risks as $risk_id=>$risk_name):

                                /**
                                 * Add Value to the first element
                                 */
                                $premium_elements[0]['_default']            = $risk_id;
                                $premium_elements[0]['_extra_html_below']   = $risk_name;

                                /**
                                 * Format Field Name
                                 *
                                 * Format: premium[manual][<field>][<risk_id>][<ItemIndex>][]
                                 *
                                 */
                                $formatted_premium_elements = [];
                                foreach ($premium_elements as $elem)
                                {
                                    $elem['field'] .= "[{$risk_id}][{$i}]";
                                    $formatted_premium_elements[] = $elem;
                                }


                                /**
                                 * Apply data from $premium_compute_options
                                 */
                                if($premium_computation_table_arr)
                                {
                                    // Riskwise Data
                                    $rate        = $premium_computation_table_arr['manual']['rate'][$risk_id] ?? [];
                                    $nwl_apply   = $premium_computation_table_arr['manual']['nwl_apply'][$risk_id] ?? [];
                                    $ffa_apply   = $premium_computation_table_arr['manual']['ffa_apply'][$risk_id] ?? [];
                                    $sdd_apply   = $premium_computation_table_arr['manual']['sdd_apply'][$risk_id] ?? [];

                                    // echo '<pre>'; print_r($sdd_apply); echo '</pre>';

                                    // Apply to each element (except first one)
                                    $formatted_premium_elements[1]['_default'] = $rate[$i] ?? '';
                                    $formatted_premium_elements[2]['_default'] = $nwl_apply[$i] ?? '';
                                    $formatted_premium_elements[3]['_default'] = $ffa_apply[$i] ?? '';
                                    $formatted_premium_elements[4]['_default'] = $sdd_apply[$i] ?? '';
                                }
                            ?>
                                <tr>
                                    <?php
                                    /**
                                     * Load Form Components
                                     */
                                    $this->load->view('templates/_common/_form_components_table', [
                                        'form_elements' => $formatted_premium_elements,
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </tr>
                            <?php
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
            $i++;
        endforeach; ?>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Manual Discount</h4>
            </div>
            <div class="box-body form-horizontal">
                <p class="alert alert-info">
                    This discount applies only after computing the final Basic and Pool Premium. <br>
                    This case mostly applies for <strong>Under-construction Building</strong>.<br><br>
                    NOTE: If "Premium" is less or equal to "Default Minimum", the discount will not be computed.
                </p>
                <?php
                /**
                 * Additional Charge/Discount Rates
                 */
                $form_section_record = $premium_compute_options->manual_discount ?? NULL;
                $manual_discount = $form_elements['manual_discount'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $manual_discount,
                    'form_record'   => $form_section_record,
                    'grid_label'    => 'col-sm-8',
                    'grid_form_control' => 'col-sm-4'
                ]);
                ?>
            </div>
        </div>

    <?php endif; ?>




    <?php
    /**
     * Load TXN Common Elements
     */
    $this->load->view('endorsements/forms/_form_txn_common', [
        'record'        => $record,
        'form_elements'     => $form_elements['basic']
    ]);

    /**
     * Other Common Components
     *  1. Premium Installments
     */
    echo $common_components;
    ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

