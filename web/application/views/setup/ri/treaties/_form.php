<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '__form-ac-chart-of-account',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-group">
        <label for="logo" class="col-sm-2 control-label">Treay File</label>
        <div class="col-sm-10 col-md-6">
            <input type="file" name="file">
            <p>
                <?php if(isset($record->file) && !empty($record->file) ):?>
                    <a href="<?php echo site_url('ri_setup_treaties/download/' . $record->id);?>" target="_blank">Download Treaty File</a>
                <?php endif?>
            </p>
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
<script type="text/javascript">
    // Datepicker
    $('.input-group.date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });
</script>