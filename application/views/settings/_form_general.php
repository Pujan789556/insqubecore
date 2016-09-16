<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Settings : General
 */
?>
<?php echo form_open_multipart( $this->uri->uri_string(), 
                                [
                                    'class' => 'form-horizontal form-iqb-general',
                                    'data-pc' => 'tab-general-settings' // parent container ID
                                ]); ?>
    <div class="form-group">
        <label for="logo" class="col-sm-2 control-label">Logo</label>
        <div class="col-sm-10 col-md-6">
            <input type="file" id="logo" name="logo" onchange="InsQube.imagePreview(event,this,{multi: false, pc: 'logo-preview'})">
            <p id="logo-preview" class="ins-img-ipb">
                <?php if($record->logo):?>
                    <img 
                      src="<?php echo base_url()?>media/settings/<?php echo thumbnail_name($record->logo);?>"
                      title="Click here to view large"
                      class="thumbnail ins-img-ip" 
                      data-src="<?php echo base_url()?>media/settings/<?php echo $record->logo?>"
                      onclick="InsQube.imagePopup(this)">
                <?php else:?>
                <i class="ion-ios-flower-outline" style="font-size:4em"></i>
                <?php endif?>
            </p>
        </div>
    </div>
    <?php foreach($form_elements as $element):?>        
        <div class="form-group <?php echo form_error($element['name']) ? 'has-error' : '';?>">
            <label for="<?php echo $element['_id'] ?>" class="col-sm-2 control-label"><?php echo $element['label']?></label>
            <div class="col-sm-10 col-md-6">
                <?php 
                /**
                 * Load Form Element
                 */
                $element_config = array(
                    'name'          => $element['name'],
                    'id'            => $element['_id'],
                    'class'         => 'form-control',
                    'placeholder'   => $element['label']
                );
                $value = set_value($element['name']) ? set_value($element['name'], '', FALSE) : $record->{$element['name']};

                switch($element['_type'])
                {
                    case 'text':
                        echo form_input($element_config, $value);
                        break;

                    case 'email':
                        echo form_email($element_config, $value);
                        break;

                    case 'url':
                        echo form_url($element_config, $value);
                        break;

                    case 'textarea':
                        echo form_textarea($element_config, $value);
                        break;

                    case 'dropdown':
                        echo form_dropdown($element_config, $element['_data'], $value);
                        break;

                    case 'switch':
                        $element_config['class'] = 'switch-checkbox';
                        $element_config['switch-type'] = 'switch-primary';
                        echo form_switch($element_config, $element['_data'], set_value($element['name']) || $record->{$element['name']});
                        break;
                }
                ?>
                <?php if(form_error($element_config['name'])):?><span class="help-block"><?php echo form_error($element_config['name']); ?></span><?php endif?>
            </div>
        </div>
    <?php endforeach?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-6">
            <button type="submit" class="btn btn-danger" data-loading-text="Saving...">Submit</button>
        </div>
    </div>
<?php echo form_close();?>

<script type="text/javascript">
  //   function imagePreview(e) {
  //       var files = e.target.files;

  //       // Loop through the FileList and render image files as thumbnails.
  //       for (var i = 0, f; f = files[i]; i++) {

  //           // Only process image files.
  //           if (!f.type.match('image.*')) {
  //               continue;
  //           }

  //           var reader = new FileReader();

  //           // Closure to capture the file information.
  //           reader.onload = (function(theFile) {
  //               return function(e) {
  //                   // Render thumbnail.
  //                   var span = document.createElement('span');
  //                   span.innerHTML = 
  //                   [
  //                   '<img style="height: 75px; border: 1px solid #000; margin: 5px" src="', 
  //                   e.target.result,
  //                   '" title="', escape(theFile.name), 
  //                   '"/>'
  //                   ].join('');

  //                   document.getElementById('logo-preview').insertBefore(span, null);
  //               };
  //           })(f);

  //           // Read in the image file as a data URL.
  //           reader.readAsDataURL(f);
  //       }
  //   }

  // document.getElementById('logo').addEventListener('change', imagePreview, false);

</script>