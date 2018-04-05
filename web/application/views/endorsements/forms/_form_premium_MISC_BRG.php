<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form - Premium : MISCELLANEOUS - BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_computation_table  = $txn_record->premium_computation_table ? json_decode($txn_record->premium_computation_table) : NULL;
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
                    <th>Direct Discount</th>
                    <td><?php echo $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT ? 'Yes' : 'No';?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Premium Information</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Portfolio Specific Premium Fields
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $form_elements['premium'],
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
        'txn_record'        => $txn_record,
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

