<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form - Premium : MISCELLANEOUS - CASH IN TRANSIT
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options  = $endorsement_record->premium_compute_options ? json_decode($endorsement_record->premium_compute_options) : NULL;
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
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Portfolio Specific Premium Fields
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $form_elements['premium'],
                    'form_record'       => $premium_compute_options,
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
    </div>
</div>
<?php echo form_close();?>
