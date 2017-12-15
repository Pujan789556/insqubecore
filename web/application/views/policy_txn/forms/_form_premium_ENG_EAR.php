<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : ENGINEERING - ERECTION ALL RISK Policy Premium
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_computation_table  = $txn_record->premium_computation_table ? json_decode($txn_record->premium_computation_table) : NULL;
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
                    <td class="text-right"><?php echo number_format($policy_object->amt_sum_insured, 2, '.', '');?></td>
                </tr>
                <tr>
                    <th>Total Third Party Liability (Rs.)</th>
                    <td class="text-right"><?php
                        $total_tpl_amount = _OBJ_ENG_EAR_compute_tpl_amount($object_attributes->third_party->limit);
                        echo number_format($total_tpl_amount, 2, '.', '');?></td>
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
            <h4 class="box-title">Premium Computation - Itemwise</h4>
        </div>
        <?php
        $section_elements           = $form_elements['items'];
        $insured_items_dropdown     = $section_elements[0]['_data'];
        $item_objects               = $premium_computation_table->items ?? NULL;
        ?>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php foreach($section_elements as $elem): ?>
                        <th><?php echo $elem['label'] ?></th>
                    <?php endforeach ?>
                    <th>Sum Insured (Rs)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                foreach($insured_items_dropdown as $group_title=>$item_groups): ?>
                    <tr>
                        <th colspan="3"><strong><?php echo $group_title ?></strong></th>
                    </tr>
                    <?php foreach($item_groups as $key=>$label): ?>
                        <tr>
                            <?php
                            /**
                             * Create a Record for Third Single Row object, (edit mode)
                             */
                            $item_row_record = NULL;
                            if($item_objects)
                            {
                                $item_row_record = (object)[];
                                foreach ($section_elements as $elem)
                                {
                                    $item_row_record->{$elem['_key']} = $item_objects->{$elem['_key']}[$i];
                                }
                            }
                            else
                            {
                                $section_elements[0]['_default'] = $key;
                            }
                            $section_elements[0]['_extra_html_below']   = $label;

                            $this->load->view('templates/_common/_form_components_table', [
                                'form_elements' => $section_elements,
                                'form_record'   => $item_row_record
                            ]);

                            // Item's Sum Insured Amount
                            echo '<td class="text-right">' . $object_attributes->items->sum_insured[$i] . '</td>';

                            $i++; // Go to next item;
                            ?>
                        </tr>
                    <?php endforeach ?>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Premium Computation - Third Party and Pool Risk</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Portfolio Specific Premium Fields
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $form_elements['tppl'],
                'form_record'       => $premium_computation_table,
                'grid_label'        => 'col-md-4',
                'grid_form_control' => 'col-md-8'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Premium Computation - Other Details</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Common Fields
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $form_elements['basic'],
                'form_record'       => $txn_record,
                'grid_label'        => 'col-md-4',
                'grid_form_control' => 'col-md-8'
            ]);
            ?>
        </div>
    </div>

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

