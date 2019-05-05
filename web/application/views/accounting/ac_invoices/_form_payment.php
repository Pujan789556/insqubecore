<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Account - Voucher Type
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-horizontal form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-payment'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <?php

    /**
     * Invoice Preview
     */
    $this->load->view($this->data['_view_base'] .'/snippets/_invoice_card', $invoice_data);

    /**
     * Load Form Components
     */
    $this->load->view('templates/_common/_form_components_horz', [
        'form_elements' => $form_elements,
        'form_record'   => $record
    ]);
    ?>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
