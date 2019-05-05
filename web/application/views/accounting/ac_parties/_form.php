<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Accounting Parties
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-ac_party',
                            'data-pc' => '#form-box-ac_party' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Basic Information</h3>
    </div>
    <div class="box-body">
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

    var $regbox = $('input[type="text"][name="company_reg_no"]').closest('.form-group'),
        $ctznbox = $('input[type="text"][name="citizenship_no"]').closest('.form-group'),
        $ppbox = $('input[type="text"][name="passport_no"]').closest('.form-group'),
        initial_type = $("input[type=radio][name=type]:checked").val();
    if(typeof initial_type === 'undefined'){
        $regbox.hide();
        $ctznbox.hide();
        $ppbox.hide();
    }
    else if(initial_type === 'I'){
        $regbox.hide();
    }else{
        $ctznbox.hide();
        $ppbox.hide();
    }

    // Toggle fields according to type
    $('input[type=radio][name=type]').on('ifChanged', function() {
        if (this.value == 'C') {
            $regbox.fadeIn();
            $ctznbox.fadeOut(function(){
                $('input[type="text"][name="citizenship_no"]').val('');
            });
            $ppbox.fadeOut(function(){
                $('input[type="text"][name="passport_no"]').val('');
            });
        }
        else {
            $ctznbox.fadeIn();
            $ppbox.fadeIn();
            $regbox.fadeOut(function(){
                $('input[type="text"][name="company_reg_no"]').val('');
            });
        }
    });

    // Fullname copy to Contact Field
    $('input[name="full_name"]').on('keyup', function(){
        $('input[name="contacts[contact_name]"]').val($(this).val());
    });

})(jQuery);
</script>
