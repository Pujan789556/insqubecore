<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy - Endorsement
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-policy',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($policy_record) ? ['policy_id' => $policy_record->id] : []); ?>
    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Creditor</h4>
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
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
// Initialize Select2
$.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //Initialize Select2 Elements

    $("#_creditor-id").select2();
    $("#_creditor-branch-id").select2();
    $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
});

// Change Branch Options on Creditor Change
$('#_creditor-id').on('change', function(e){
    var v = this.value,
        $target = $('#_creditor-branch-id');
    $target.empty();
    if(v){
        // load the object form
        $.getJSON('<?php echo base_url()?>policies/gccbc/'+v, function(r){
            // Update dropdown
            if(r.status == 'success' && typeof r.options !== 'undefined'){
                $target.append($('<option>', {
                    value: '',
                    text : 'Select...'
                }));
                $.each(r.options, function(key, value) {
                    $target.append($('<option>', {
                        value: key,
                        text : value
                    }));
                });
                $target.prop('selectedIndex',0).trigger('change');
              }
        });
    }
});
</script>