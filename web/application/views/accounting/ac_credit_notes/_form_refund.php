<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Account - Credit Note - Refund
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-horizontal form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-refund'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <?php

    /**
     * Credit Note Preview
     */
    $this->load->view($this->data['_view_base'] . '/snippets/_credit_note_card', $credit_note_data);

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
