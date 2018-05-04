<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Department
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
        <label for="logo" class="col-sm-2 control-label">Terms & Condition Document</label>
        <div class="col-sm-10 col-md-6">
            <input type="file" id="file_toc" name="file_toc">
            <p class="help-block">Only doc, docx, pdf file allowed.</p>
            <?php if(isset($record->file_toc)  && !empty($record->file_toc) ):?>
                <p><?php echo anchor('downloads/get/portfolio/' . $record->file_toc, 'Download', 'target="_blank"') ?></p>
            <?php endif?>
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

    <h5><a href="<?php echo site_url('public/samples/bs-ri-report-sample.xlsx') ?>" target="_blank"> <i class="fa fa-download"></i> Download Beema Samiti Business Code Reference File</a></h5>
    <div class="alert alert-info">
        <p><strong>Beema Samiti Business Code</strong> is required to generate Beema Samiti Report for Re-Insurance.</p>
        <p>Please download and see the following reference file for finding the correct code.</p>

    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">
    // Initialize Select2
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $('select[data-ddstyle="select"]').select2();
        $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
    });
</script>