<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Address Form
 *
 * 	Required Variables:
 *
 * 		$contact_record
 */
$form_title = $form_title ?? 'Contact Address';
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
      <h3 class="box-title"><?php echo $form_title; ?></h3>
    </div>
    <div class="box-body">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Address Information</h4>
            </div>
            <div class="box-body">
                <?php
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['country'],
                    'form_record'   => $record
                ]);
                ?>
                <div id="state-box">
                    <?php
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['state'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
                <div id="address1-box">
                    <?php
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['address1'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>

                <?php
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['address_other'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Contact Information</h4>
            </div>
            <div class="box-body">
                <?php
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['contacts'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var _iqb_addrconfig = {

        'state_label':  '<label class="col-sm-2 control-label">' +
                            'State / Province<span class="text-red text-comp" data-toggle="tooltip" title="This field is compulsory.">*</span>' +
                        '</label>',

        'state_dd':     '<div class="col-sm-10">' +
                            '<select name="state_id" class="form-control" placeholder="State / Province" id="state-id">' +
                                '<option value="" selected="selected">Select...</option>' +
                            '</select>' +
                        '</div>',

        'state_txt':    '<div class="col-sm-10">' +
                            '<input type="text" name="alt_state_text" value="" class="form-control" placeholder="State / Province" id="alt-state-text" autocomplete="off">' +
                        '</div>',

        'address1_label':  '<label class="col-sm-2 control-label">' +
                                'Address<span class="text-red text-comp" data-toggle="tooltip" title="This field is compulsory.">*</span>' +
                            '</label>',

        'address1_dd':     '<div class="col-sm-10">' +
                                '<select name="address1_id" class="form-control" placeholder="Address" id="address1-id">' +
                                    '<option value="" selected="selected">Select...</option>' +
                                '</select>' +
                                '<p class="help-block">VDC/Municipality or City Area</p>' +
                            '</div>',

        'address1_txt':     '<div class="col-sm-10">' +
                                '<input type="text" name="alt_address1_text" value="" class="form-control" placeholder="Address1" id="alt-address1-text">' +
                                '<p class="help-block">VDC/Municipality or City Area</p>' +
                            '</div>'
    };

    $('#country-id').on('change', function(){

        /**
         * Fetch State List
         *  - if empty, show state textarea
         * - else
         *      - show state dropdown
         *      - Fetch Address1 List
         *          - if empty, show address1 textarea
         *          - else show address1 dropdown
         */

         var $this = $(this),
            cid = $.trim($this.val()),
            $sbox = $('#state-box'),
            $a1box = $('#address1-box');

        if(cid)
        {
            $.getJSON('<?php echo base_url()?>addresses/states/'+cid, function(r){
                // If found State Dropdown
                if ( Array.isArray(r) && r.length > 0 ){
                    $sbox.html(
                        '<div class="form-group">' +
                           _iqb_addrconfig.state_label +
                           _iqb_addrconfig.state_dd +
                        '</div>'
                    );
                    $.each(r, function(idx, opt) {
                        $('#state-id').append($('<option>', {
                            value: opt.value,
                            text : opt.label
                        }));
                    });

                    // Register State Change Event - Because the content is loaded dynamically
                    $( 'select#state-id').on('change',function(){
                        __state_change(this);
                    });
                }else{
                    // Show State and Address1 TextArea
                    $sbox.html(
                        '<div class="form-group">' +
                           _iqb_addrconfig.state_label +
                           _iqb_addrconfig.state_txt +
                        '</div>'
                    );

                    $a1box.html(
                        '<div class="form-group">' +
                           _iqb_addrconfig.address1_label +
                           _iqb_addrconfig.address1_txt +
                        '</div>'
                    );
                }
            });
        }
    });

    // Register State Change Event
    $( 'select#state-id').on('change',function(){
        __state_change(this);
    });

    function __state_change(dom)
    {
        /**
         * Fetch Address1 List
         *  - if empty, show address1 textarea
         * - else
         *      - show address1 dropdown
         */

         var $this = $(dom),
            sid = $.trim($this.val()),
            $a1box = $('#address1-box');

        if(sid)
        {
            $.getJSON('<?php echo base_url()?>addresses/address1/'+sid, function(r){
                // If found Address1 Dropdown
                if ( Array.isArray(r) && r.length > 0 ){

                    $a1box.html(
                        '<div class="form-group">' +
                           _iqb_addrconfig.address1_label +
                           _iqb_addrconfig.address1_dd +
                        '</div>'
                    );
                    $.each(r, function(idx, opt) {
                        $('#address1-id').append($('<option>', {
                            value: opt.value,
                            text : opt.label
                        }));
                    });

                }else{
                    // Show Address1 TextArea
                    $a1box.html(
                        '<div class="form-group">' +
                           _iqb_addrconfig.address1_label +
                           _iqb_addrconfig.address1_txt +
                        '</div>'
                    );
                }
            });
        }
    }
</script>











