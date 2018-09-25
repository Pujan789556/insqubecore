<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-customer',
                            'data-pc' => '#form-box-customer' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Basic Information</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="logo" class="col-sm-2 control-label">Customer Picture</label>
            <div class="col-sm-10 col-md-6">
                <input type="file" id="picture" name="picture" onchange="InsQube.imagePreview(event,this,{multi: false, pc: 'picture-preview'})">
                <p id="picture-preview" class="ins-img-ipb">
                    <?php if(isset($record->picture)  && !empty($record->picture) ):?>
                        <img
                          src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo thumbnail_name($record->picture);?>"
                          title="Click here to view large"
                          class="thumbnail ins-img-ip"
                          data-src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo $record->picture?>"
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
    $this->load->view('templates/_common/_form_address', [
      'record'          => $address_record ?? NULL,
      'form_elements'   => $address_elements
    ]);
    ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
(function($){

    var $ref_i = $('input[data-ref="I"], select[data-ref="I"]'),
        $ref_c = $('input[data-ref="C"]'),
        $ref_i_box = $ref_i.closest('.form-group'),
        $ref_c_box = $ref_c.closest('.form-group'),
        initial_type = $("input[type=radio][name=type]:checked").val();

    if(typeof initial_type === 'undefined'){
        // hide both types
        $ref_i_box.hide();
        $ref_c_box.hide();
    }
    else if(initial_type === 'I'){
        $ref_c_box.hide();
    }else{
        $ref_i_box.hide();
    }

    // Toggle fields according to type
    $('input[type=radio][name=type]').on('ifChanged', function() {
        if (this.value == 'C') {
            $ref_c_box.show();
            $ref_i_box.hide(function(){
                $ref_i.val('');
            })
        }
        else {
            $ref_i_box.show();
            $ref_c_box.hide(function(){
                $ref_c.val('');
            })
        }
    });

    // Fullname copy to Contact Field
    $('input[name="full_name"]').on('keyup', function(){
        $('input[name="contacts[contact_name]"]').val($(this).val());
    });

})(jQuery);
</script>
