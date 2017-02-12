<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Settings : General
 */
?>
<?php echo form_open_multipart( $this->uri->uri_string(),
                                [
                                    'class' => 'form-horizontal form-iqb-general',
                                    'data-pc' => '#tab-general-settings' // parent container ID
                                ]); ?>
    <div class="form-group">
        <label for="logo" class="col-sm-2 control-label">Logo</label>
        <div class="col-sm-10 col-md-6">
            <input type="file" id="logo" name="logo" onchange="InsQube.imagePreview(event,this,{multi: false, pc: 'logo-preview'})">
            <p id="logo-preview" class="ins-img-ipb">
                <?php if($record->logo):?>
                    <img
                      src="<?php echo INSQUBE_MEDIA_URL?>settings/<?php echo thumbnail_name($record->logo);?>"
                      title="Click here to view large"
                      class="thumbnail ins-img-ip"
                      data-src="<?php echo INSQUBE_MEDIA_URL?>settings/<?php echo $record->logo?>"
                      onclick="InsQube.imagePopup(this)">
                <?php else:?>
                <i class="ion-ios-flower-outline text-muted img-alt"></i>
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