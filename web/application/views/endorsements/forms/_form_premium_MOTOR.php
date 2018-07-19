<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : MOTOR Policy Premium
 */
$object_attributes = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
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

    <?php if( $policy_record->policy_package == IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE ): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Comprehensive Premium Information
                 */
                $premium_computation_table = $endorsement_record->premium_computation_table ? json_decode($endorsement_record->premium_computation_table) : NULL;

                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $form_elements['premium'],
                    'form_record'       => $premium_computation_table
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
