<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Premium Form : FIRE - FIRE
 */
$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_computation_table      = json_decode($endorsement_record->premium_computation_table ?? NULL);
$premium_computation_table_arr  = json_decode($endorsement_record->premium_computation_table ?? NULL, TRUE);
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);
?>

    <div class="row">
        <div class="col-sm-6">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Policy Summary</h4>
                </div>
                <table class="table table-responsive table-condensed">
                    <tbody>
                        <tr>
                            <th>Portfolio</th>
                            <td><?php echo $policy_record->portfolio_name;?></td>
                        </tr>
                        <tr>
                            <th>Sum Insured (Rs.)</th>
                            <td><?php echo $policy_object->amt_sum_insured;?></td>
                        </tr>
                        <tr>
                            <th>Direct Discount</th>
                            <td><?php echo $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT ? 'Yes' : 'No';?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="box box-solid box-bordered">
                    <div class="box-header with-border">
                        <h4 class="box-title">Fire Items Summary</h4>
                    </div>
                    <?php if($object_attributes->item_attached === 'N'):
                        $items              = $object_attributes->items;
                        $item_count         = count($items->category);
                        ?>
                        <table class="table table-responsive table-condensed">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Ownership</th>
                                    <th>Sum Insured</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i=0; $i < $item_count; $i++ ): ?>
                                    <tr>
                                        <td><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ]?></td>
                                        <td><?php echo _OBJ_FIRE_FIRE_item_ownership_dropdown(FALSE)[ $items->ownership[$i] ]; ?></td>
                                        <td>Rs. <?php echo $items->sum_insured[$i]; ?></td>
                                    </tr>
                                <?php endfor; ?>
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
                <h4 class="box-title">Discount/Additional Charges (Rate &nbsp; Amount)</h4>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                        <?php
                        /**
                         * Additional Charge/Discount Rates
                         */
                        $premium_file_additional_rates = $form_elements['premium_file_additional_rates'];
                        $form_section_record = $premium_computation_table->file ?? NULL;

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
                             * Apply Premium  We have $premium_computation_table
                             */
                            if($premium_computation_table_arr)
                            {
                                // Premium
                                $premium = $premium_computation_table_arr['file']['premium'][$risk_id];
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
            <div class="box-body">
                <?php
                /**
                 * Additional Charge/Discount Rates
                 */
                $form_section_record = $premium_computation_table->manual ?? NULL;
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
        <?php for($i=0; $i < $item_count; $i++ ): ?>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title"><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ]?> <em>( Sum Insured Rs. <?php echo $items->sum_insured[$i]; ?>)</em> - Premium Table</h4>
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
                                 * Apply data from $premium_computation_table
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endfor; ?>
    <?php endif; ?>


    <?php
    /**
     * Load TXN Common Elements
     */
    $this->load->view('endorsements/forms/_form_txn_common', [
        'endorsement_record'        => $endorsement_record,
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

<script type="text/javascript">
// Load Txn Details from Endorsement Template
$('#template-reference').on('change', function(){
    var v = parseInt(this.value);
    if(v){
        // Load template body from the reference supplied
        $.getJSON('<?php echo base_url()?>endorsement_templates/body/'+v, function(r){
            // Update dropdown
            if(r.status == 'success'){
                $('#txn-details').val(r.body);
            }
            else{
                toastr[r.status](r.message);
            }
        });
    }
})
</script>

