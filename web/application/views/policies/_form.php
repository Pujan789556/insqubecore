<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy
 */

echo form_open( $this->uri->uri_string(),
    [
        'class' => 'form-horizontal form-iqb-general',
        'id'    => '_form-policy',
        'data-pc' => '#form-box-policy' // parent container ID
    ],
    // Hidden Fields
    isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Portfolio</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $portfolio_elements = $form_elements['portfolio'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $portfolio_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Customer</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components : Proposer, Care Of
             */
            $proposer_elements = $form_elements['proposer'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $proposer_elements,
                'form_record'   => $record
            ]);
            ?>
            <div id="_customer-box">
                <div class="form-group <?php echo form_error('customer_id') ? 'has-error' : '';?>">
                  <label class="col-sm-2 control-label">Customer<?php echo field_compulsary_text( TRUE )?></label>
                  <div class="col-sm-10">
                      <?php
                      $customer_id = $record->customer_id ?? '';
                      if(set_value('customer_id', '', FALSE))
                      {
                        $customer_id = set_value('customer_id', '', FALSE);
                      }

                      $customer_name = $record->customer_name ?? '';
                      if(set_value('customer_name', '', FALSE))
                      {
                        $customer_name = set_value('customer_name', '', FALSE);
                      }
                      ?>
                      <span id="_text-ref-customer" class="mrg-r-5 text-purple" readonly><?php echo $customer_name;?></span>
                      <a href="#" id="_find-customer" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Customer"><i class="fa fa-filter"></i>...</a>
                      <input type="hidden" id="customer-text" name="customer_name" value="<?php echo $customer_name;?>">
                      <input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer_id;?>">
                      <?php if(form_error('customer_id')):?><span class="help-block"><?php echo form_error('customer_id'); ?></span><?php endif?>
                  </div>
                </div>
            </div>
            <?php
            /**
             * Load Form Components : Flag on Credit
             */
            $flag_on_credit_elements = $form_elements['flag_on_credit'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $flag_on_credit_elements,
                'form_record'   => $record
            ]);
            ?>

            <div id="__creditor_info_box">
                <?php
                 /**
                 * Load Form Components : Creditor Info
                 */
                $creditor_info_elements = $form_elements['creditor_info'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $creditor_info_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Policy Object</h4>
        </div>
        <div class="box-body">

            <div id="_policy-object-box">
                <div class="form-group <?php echo form_error('object_id') ? 'has-error' : '';?>">
                  <label class="col-sm-2 control-label">Policy Object<?php echo field_compulsary_text( TRUE )?></label>
                  <div class="col-sm-10">
                      <?php
                      $object_id = $record->object_id ?? '';
                      if(set_value('object_id', '', FALSE))
                      {
                        $object_id = set_value('object_id', '', FALSE);
                      }

                      $object_name = $record->object_name ?? '';
                      if(set_value('object_name', '', FALSE))
                      {
                        $object_name = set_value('object_name', '', FALSE);
                      }
                      ?>
                      <span id="_text-ref-object" class="mrg-r-5 text-purple" readonly><?php echo $object_name;?></span>
                      <a href="#" id="_find-object" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Policy Object"><i class="fa fa-filter"></i>...</a>
                      <input type="hidden" id="object-text" name="object_name" value="<?php echo $object_name;?>">
                      <input type="hidden" id="object-id" name="object_id" value="<?php echo $object_id;?>">
                      <?php if(form_error('object_id')):?><span class="help-block"><?php echo form_error('object_id'); ?></span><?php endif?>
                  </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Duration</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $duration_elements = $form_elements['duration'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $duration_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Sales Info</h4>
        </div>
        <div class="box-body">

            <?php
            /**
             * Load Form Components
             */
            $staff_elements = $form_elements['sales'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $staff_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
function __do_select(a){
    var $a = $(a),
    selectable = $a.data('selectable'),
    fields = selectable.fields,
    html = selectable.html;

    if( typeof fields === 'object'){
        for(var i = 0; i < fields.length; i++) {
            var obj = fields[i];
            $('#' + obj.id ).val(obj.val);
        }
    }
    if( typeof html === 'object'){
        for(var i = 0; i < html.length; i++) {
            var obj = html[i];
            $('#' + obj.id ).html(obj.val);
        }
    }

    // Close the bootbox if any
    var $bootbox = $a.closest('.bootbox');
    $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
}

// Creator Info Toggler
function __toggle_creditor_info(){

    var $box    = $('#__creditor_info_box'),
        flag    = $("input[type=radio][name=flag_on_credit]:checked").val();

    if(typeof flag === 'undefined' || flag === 'N'){
        $box.fadeOut();
        $('#_creditor-id').prop('selectedIndex',0).trigger('change');
        $('#_care-of-text').val('');
    }else{
        $box.fadeIn();
    }
}

// Agent Info Toggler
function __toggle_agent_info(){
    var v = $("input[type=radio][name=flag_dc]:checked").val(),
      $agentbox = $('#_agent-id').closest('.form-group');
    if(v === 'C'){
        $agentbox.fadeIn();
    }else{
        $agentbox.fadeOut();
        $('#_agent-id').prop('selectedIndex',0).trigger('change');
    }
}

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

// Policy Package Changer
$('#_portfolio-id').on('change', function(e){
    var v = this.value;
    $('#_policy-package-id').empty();
    if(v){
        // load the object form
        $.getJSON('<?php echo base_url()?>policies/gppp/'+v, function(r){

            // Update policy package options
            if(r.status == 'success' && typeof r.ppo !== 'undefined'){
                $('#_policy-package-id').append($('<option>', {value: '',text : 'Select...'}));
                $.each(r.ppo, function(key, value) {

                    $('#_policy-package-id').append($('<option>', {
                        value: key,
                        text : value
                    }));
                });
            }
        });
    }
});

// Customer Finder
$('#_find-customer').on('click', function(e){
    e.preventDefault();
    var $this = $(this);
    $this.button('loading');
    InsQube.options.__btn_loading = $this;
    $.getJSON('<?php echo base_url()?>customers/page/f/y/0/_customer-box:policy', function(r){
        if( typeof r.html !== 'undefined' && r.html != '' ){
            bootbox.dialog({
                className: 'modal-default',
                size: 'large',
                title: 'Find Customer',
                closeButton: true,
                message: r.html,
                buttons:{
                    cancel: {
                        label: "Close",
                        className: 'btn-default'
                    }
                }
            });
        }
        // Reset Loading
        $this.button('reset');
    });
});

// Object Finder
$('#_find-object').on('click', function(e){
    e.preventDefault();
    var $this = $(this);
    $this.button('loading');
    InsQube.options.__btn_loading = $this;

    var c = $('#customer-id').val(),
        p = $('#_portfolio-id').val(),
        sp = $('#_sub-portfolio-id').val();

    if( p == '' || c == '' || sp == '')
    {
        toastr.error('Please select Portfolio, Sub-Portfolio, & Customer first.');
        // Reset Loading
        $this.button('reset');
        return false;
    }
    var url = '<?php echo base_url()?>objects/find/' + c + '/'+ p + '/' + sp;

    $.getJSON(url, function(r){
        if( typeof r.html !== 'undefined' && r.html != '' ){
            bootbox.dialog({
                className: 'modal-default',
                size: 'large',
                closeButton: true,
                title: 'Find Policy Object',
                message: r.html,
                buttons:{
                    cancel: {
                        label: "Close",
                        className: 'btn-default'
                    }
                }
            });
        }
        // Reset Loading
        $this.button('reset');
    });
});

// Datetimepicker
$('.input-group.datetime, .input-group.datetime input').datetimepicker({
    format: 'YYYY-MM-DD HH:mm:00',
    showClose: true,
    showClear: true
});

// Hide Creditor Info if Not on Loan
__toggle_creditor_info();
$(document).on('ifChecked', 'input[name="flag_on_credit"]', function(e){
    __toggle_creditor_info();
});


// Hide agent list on Direct Discount Checked
__toggle_agent_info();
$(document).on('ifChecked', 'input[name="flag_dc"]', function(e){
    __toggle_agent_info();
});



// Initialize Select2
$.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //Initialize Select2 Elements
    $("#_marketing-staff").select2();
    $("#_agent-id").select2();
    $("#_creditor-id").select2();
    $("#_creditor-branch-id").select2();
    $("#_ref-company-id").select2();

    $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
});
</script>
