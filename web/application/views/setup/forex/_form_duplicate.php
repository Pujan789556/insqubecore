<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Forex - Duplicate
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-group">
        <label class="col-sm-2 control-label">Source Exchange Date</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo $source_record->exchange_date?></p>
        </div>
    </div>

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
