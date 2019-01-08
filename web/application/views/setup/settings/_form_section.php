<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Settings : Section Specific Form
 */
?>
<?php echo form_open( $action_url,
                                [
                                    'class' => 'form-horizontal form-iqb-general',
                                    'data-pc' => $dom_parent_container // parent container ID
                                ]); ?>
    <?php
    /**
     * Load Form Components
     */
    $this->load->view('templates/_common/_form_components_horz', [
        'form_elements' => $form_elements,
        'form_record'   => $record,
        'grid_form_control' => 'col-sm-10 col-md-6'
    ]);
    ?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-6">
            <button type="submit" class="btn btn-danger" data-loading-text="Saving...">Submit</button>
        </div>
    </div>
<?php echo form_close();?>