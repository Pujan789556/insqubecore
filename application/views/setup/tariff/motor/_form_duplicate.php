<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Department
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-tariff-motor',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-group">
        <label class="col-sm-2 control-label">Source Fiscal Year</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo $source_record->code_np?> (<?php echo $source_record->code_en?>)</p>
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
