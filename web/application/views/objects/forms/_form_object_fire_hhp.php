<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Fire - Householder
 */
$old_document   = $record->document ?? NULL;
$district_id    = $record->building->district ?? NULL;
$vdc_id         = $record->building->vdc ?? NULL;
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Building Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements = $form_elements['building'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record->building ?? NULL
                ]);?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Goods Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements = $form_elements['goods'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record->goods ?? NULL
                ]);
                if($old_document):
                ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Download existing Item List</label>
                    <div class="col-sm-10">
                        <p class="form-control-static">
                            <?php echo anchor('objects/download/'.$old_document, 'Download', ['target' => '_blank']); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Register District Change Event
    $( 'select#district-dropdown').on('change',function(){
        __district_change();
    });

    // Register VDC Change Event
    $( 'select#vdc-dropdown').on('change',function(){
        __vdc_change();
    });

    // ON EDIT MODE - list the vdc from selected district
    var __iqb_did = '<?php echo $district_id ?>';
    var __iqb_vid = '<?php echo $vdc_id ?>';
    if(__iqb_did){
        __district_change(function(){
            if(__iqb_vid){
                $('#vdc-dropdown').val(__iqb_vid);
                $('#vdc-dropdown').trigger('change');
            }
        });
    }

    // District Change Function
    function __district_change(callback)
    {
        /**
         * Fetch Address1 List
         *  - if empty, show address1 textarea
         * - else
         *      - show address1 dropdown
         */

         var $this = $('#district-dropdown'),
            did = $.trim($this.val());

        if(did)
        {
            $.getJSON('<?php echo base_url()?>addresses/address1_by_district/'+did, function(r){
                // If found Address1 Dropdown
                if ( Array.isArray(r) && r.length > 0 ){

                    $('#vdc-dropdown')
                        .empty()
                        .append('<option selected="selected" value="">Select...</option>');
                    $.each(r, function(idx, opt) {
                        $('#vdc-dropdown').append($('<option>', {
                            value: opt.value,
                            text : opt.label
                        }));
                    });
                }

                // Callback if any
                if (callback && typeof(callback) === "function") {
                    callback();
                }
            });
        }
    }

    // VDC Change Function
    function __vdc_change()
    {
        /**
         * Update the vdc text field
         */
         var $this = $('#vdc-dropdown'),
            vdc = $.trim($this.val());

        if(vdc){
            var text = $('#vdc-dropdown option:selected').text();
            $('input[name="object[building][vdc_text]"]').val(text);
        }
    }
</script>
