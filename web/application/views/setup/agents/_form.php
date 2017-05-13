<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Agent
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-agent',
                            'data-pc' => '#form-box-agent' // parent container ID
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
                          src="<?php echo INSQUBE_MEDIA_URL?>agents/<?php echo thumbnail_name($record->picture);?>"
                          title="Click here to view large"
                          class="thumbnail ins-img-ip"
                          data-src="<?php echo INSQUBE_MEDIA_URL?>agents/<?php echo $record->picture?>"
                          onclick="InsQube.imagePopup(this, 'Profile Picture')">
                    <?php else:?>
                    <i class="ion-ios-person-outline text-muted img-alt"></i>
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
    </div>

    <?php
    /**
     * Contact Form
     */
    $contact_record = isset($record) && !empty($record->contact) ? json_decode($record->contact) : NULL;
    $this->load->view('templates/_common/_form_contact', compact('contact_record'));
    ?>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
