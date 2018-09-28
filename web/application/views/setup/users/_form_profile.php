<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : User Profile
 */

$hidden = [
    'next_wizard' => isset($next_wizard) && $next_wizard ? 1 : 0
];
if (isset($record) )
{
    $hidden['id'] = $record->id;
}
?>
<?php echo form_open_multipart( $action_url,
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        $hidden); ?>
    <div class="box-header with-border">
        <h3 class="box-title"><?php echo $form_title?></h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="logo" class="col-sm-2 control-label">Profile Picture</label>
            <div class="col-sm-10 col-md-6">
                <input type="file" id="picture" name="picture" onchange="InsQube.imagePreview(event,this,{multi: false, pc: 'picture-preview'})">
                <p id="picture-preview" class="ins-img-ipb">
                    <?php if(isset($form_record->picture)  && !empty($form_record->picture) ):?>
                        <img
                          src="<?php echo site_url('static/media/users/' . thumbnail_name($form_record->picture))?>"
                          title="Click here to view large"
                          class="thumbnail ins-img-ip"
                          data-src="<?php echo site_url('static/media/users/' . $form_record->picture)?>"
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
            'form_record'   => $form_record
        ]);
        ?>
    </div>
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
