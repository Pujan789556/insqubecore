<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : FIRE Policy Premium
 */
$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$items              = $object_attributes->items;
$item_count         = count($items->category);

$premium_computation_table = $txn_record->premium_computation_table ? json_decode($txn_record->premium_computation_table, TRUE) : NULL;
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-horizontal form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);
?>

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

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Fire Items Summary</h4>
        </div>
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
                        <td><?php echo _OBJ_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ]?></td>
                        <td><?php echo _OBJ_FIRE_item_ownership_dropdown(FALSE)[ $items->ownership[$i] ]; ?></td>
                        <td>Rs. <?php echo $items->sum_insured[$i]; ?></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>


    <?php
    for($i=0; $i < $item_count; $i++ ): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title"><?php echo _OBJ_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ]?> <em>(Rs. <?php echo $items->sum_insured[$i]; ?>)</em> - Premium Table</h4>
            </div>
            <div class="box-body form-inline" style="overflow-x: scroll;">
                <table class="table table-responsive table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th>Risk</th>
                            <th>Rate</th>
                            <th>Rate Base</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $premium_elements = $form_elements['premium'];
                        foreach($portfolio_risks as $risk_id=>$risk_name):

                            /**
                             * Add Value to the first element
                             */
                            $premium_elements[0]['_default']            = $risk_id;
                            $premium_elements[0]['_extra_html_below']   = $risk_name;

                            /**
                             * Format Field Name
                             *
                             * Format: premium[<field>][<risk_id>][<ItemIndex>][]
                             *
                             */
                            $formatted_premium_elements = [];
                            foreach ($premium_elements as $elem)
                            {
                                $elem['field'] .= "[{$risk_id}][{$i}]";
                                $formatted_premium_elements[] = $elem;
                            }


                            /**
                             * Apply Rate and Rate Base if We have $premium_computation_table
                             */
                            if($premium_computation_table)
                            {
                                // Rate
                                $rate       = $premium_computation_table['rate'][$risk_id];
                                $rate_base  = $premium_computation_table['rate_base'][$risk_id];

                                // Rate
                                $formatted_premium_elements[1]['_default'] = $rate[$i];
                                $formatted_premium_elements[2]['_default'] = $rate_base[$i];
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

    <?php
    /**
     * Load Form Components - Basic Elements
     */
    $this->load->view('templates/_common/_form_components_horz', [
        'form_elements'     => $form_elements['basic'],
        'form_record'       => $txn_record
    ]);
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

