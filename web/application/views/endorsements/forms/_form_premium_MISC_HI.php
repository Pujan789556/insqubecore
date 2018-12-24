<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form - Premium : MISCELLANEOUS - HEALTH INSURANCE (HI)
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options  = $record->premium_compute_options ? json_decode($record->premium_compute_options) : NULL;

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
    <div class="col-md-6">
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
    </div>
</div>
<?php echo form_close();?>
