<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : ENGINEERING - CONTRACTOR ALL RISK Policy Premium
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_computation_table  = $endorsement_record->premium_computation_table ? json_decode($endorsement_record->premium_computation_table) : NULL;
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);

    /**
     * Premium Summary Table
     */
    $this->load->view('endorsements/snippets/_premium_summary');
?>

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

                            $i++; // Go to next item
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

