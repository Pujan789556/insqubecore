<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio Settings - Add/Edit
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general form-horizontal',
                            // 'id'    => '__testform',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h3 class="box-title">Setting Information</h3>
    </div>
    <div class="box-body">
        <?php
        /**
         * Load Form Components
         */
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $form_elements,
            'form_record'   => $record,
            'grid_label'        => 'col-md-3',
            'grid_form_control' => 'col-md-9',
        ]);
        ?>
    </div>
</div>
<button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
