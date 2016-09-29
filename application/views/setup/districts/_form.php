<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Districts
 */
?>
<?php echo form_open( $this->uri->uri_string(), 
                                [
                                    'class' => 'form-horizontal form-iqb-general',
                                    'data-pc' => '.bootbox-body' // parent container ID
                                ]); ?>
    
    <?php 
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
