<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Surveyor
 */
?>
<style>
.select2-dropdown .select2-search__field:focus, .select2-search--inline .select2-search__field:focus{
    border:none;
}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-surveyor',
                            'data-pc' => '#form-box-surveyor' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Basic Information</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="logo" class="col-sm-2 control-label">Profile Picture</label>
            <div class="col-sm-10 col-md-6">
                <input type="file" id="picture" name="picture" onchange="InsQube.imagePreview(event,this,{multi: false, pc: 'picture-preview'})">
                <p id="picture-preview" class="ins-img-ipb">
                    <?php if(isset($record->picture)  && !empty($record->picture) ):?>
                        <img
                          src="<?php echo site_url('static/media/surveyors/' . thumbnail_name($record->picture))?>"
                          title="Click here to view large"
                          class="thumbnail ins-img-ip"
                          data-src="<?php echo site_url('static/media/surveyors/' . $record->picture)?>"
                          onclick="InsQube.imagePopup(this, 'Profile Picture')">
                    <?php else:?>
                    <i class="ion-ios-person-outline text-muted img-alt"></i>
                    <?php endif?>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label for="logo" class="col-sm-2 control-label">Surveyor's Resume / Profile / Bio-Data</label>
            <div class="col-sm-10 col-md-6">
                <input type="file" id="resume" name="resume">
                <p class="help-block">Only PDF, DOC or DOCX files supported.</p>
                <?php
                if(isset($record->resume)  && !empty($record->resume) ):
                    $_download_url  = $this->data['_url_base'] . '/download/resume/' . $record->id;?>
                      <p><a href="<?php echo site_url($_download_url)?>" target="_blank"><i class="fa fa-download"></i> Download Existing Document</a></p>
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
    </div>
    <?php
    /**
     * Contact Form
     */
    $this->load->view('templates/_common/_form_address', [
      'record'          => $address_record ?? NULL,
      'form_elements'   => $address_elements
    ]);
    ?>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<!-- Select2 -->
<script>
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $(".select-multiple").select2();
    });
</script>
