<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form - Premium : MISCELLANEOUS - HEALTH INSURANCE (HI)
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
