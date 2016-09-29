<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Branch
 */
?>
<?php echo form_open( $this->uri->uri_string(), 
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '__testform',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ], 
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Basic Information</h3>
    </div>
    <div class="box-body">
        <?php 
        /**
         * Load Form Components
         */
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $form_elements,
            'form_record'   => $record
        ]);
        ?>
    </div>
    
    <?php 
    /**
     * Contact Form
     */
    $contact_record = isset($record) && !empty($record->contacts) ? json_decode($record->contacts) : NULL;
    $this->load->view('templates/_common/_form_contact', compact('contact_record'));
    ?>
    <button type="submit" class="hide">Submit</button> 
<?php echo form_close();?>
