<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy
 */
?>
<style>
.select2-dropdown .select2-search__field:focus, .select2-search--inline .select2-search__field:focus{
    border:none;
}
</style>
<?php
echo form_open( $this->uri->uri_string(),
    [
        'class' => 'form-iqb-general',
        'id'    => '_form-policy',
        'data-pc' => '#form-box-policy' // parent container ID
    ],
    // Hidden Fields
    isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Category</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            /**
             * Load Form Components
             */
            $portfolio_elements = $form_elements['category'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $portfolio_elements,
                'form_record'   => $record
            ]);
            ?>

            <div id="__insurance_company_box">
                <?php
                 /**
                 * Load Form Components : Creditor Info
                 */
                $section_elements = $form_elements['insurance_company'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Customer</h4>
        </div>
        <div class="box-body form-horizontal">
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

                      $customer_name_en = $record->customer_name_en ?? '';
                      if(set_value('customer_name_en', '', FALSE))
                      {
                        $customer_name_en = set_value('customer_name_en', '', FALSE);
                      }
                      ?>
                      <span id="_text-ref-customer" class="mrg-r-5 text-purple" readonly><?php echo $customer_name_en;?></span>
                      <a href="#" id="_find-customer" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Customer"><i class="fa fa-filter"></i>...</a>
                      <input type="hidden" id="customer-text" name="customer_name_en" value="<?php echo $customer_name_en;?>">
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
                // $creditor_info_elements = $form_elements['creditor_info'];
                // $this->load->view('templates/_common/_form_components_horz', [
                //     'form_elements' => $creditor_info_elements,
                //     'form_record'   => $record
                // ]);
                ?>
            </div>


            <?php
            /**
             * Load Form Components : Care of
             */
            $care_of_elements = $form_elements['care_of'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $care_of_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Risk District</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            /**
             * Load Form Components
             */
            $portfolio_elements = $form_elements['district'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $portfolio_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Portfolio</h4>
        </div>
        <div class="box-body form-horizontal">
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
          <h4 class="box-title">Select Policy Object</h4>
        </div>
        <div class="box-body form-horizontal">

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
          <h4 class="box-title">Proposer Information</h4>
        </div>
        <div class="box-body form-horizontal">
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
        </div>
    </div>


    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Duration</h4>
        </div>
        <div class="box-body">
            <div class="form-horizontal no-margin-b">
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
            <div class="form-inline">
                <div class="row">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="alert alert-info no-margin-b">
                            <div class="form-group">
                                <select class="form-control" id="_ed-type">
                                  <option value="y">Year(s)</option>
                                  <!-- <option value="M">Month(s)</option> -->
                                  <option value="w">Week(s)</option>
                                  <option value="d">Day(s)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="number" class="form-control" id="_ed-qty" placeholder="Type duration...">
                            </div>
                            <a class="btn btn-warning btn-sm" href="javascript:void(0)" id="_btn-apply-end_date">Apply</a>
                            <p>Please select and Click <strong>Apply</strong> button for automatic "<strong>End Date</strong>" calculation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Sales Info</h4>
        </div>
        <div class="box-body form-horizontal">

            <?php
            /**
             * Load Form Components
             */
            $sales_elements = $form_elements['sales'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $sales_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Disclaimer Information (सम्पुष्टि विवरण)</h4>
        </div>
        <div class="box-body form-horizontal">

            <?php
            /**
             * Load Form Components
             */
            $endorsement_basic_elements = $form_elements['endorsement_basic'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $endorsement_basic_elements,
                'form_record'   => $endorsement_record
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
// function __toggle_creditor_info(){

//     var $box    = $('#__creditor_info_box'),
//         flag    = $("input[type=radio][name=flag_on_credit]:checked").val();

//     if(typeof flag === 'undefined' || flag === 'N'){
//         $box.fadeOut();
//         $('#_creditor-id').prop('selectedIndex',0).trigger('change');
//         $('#_other-creditors').val('');
//     }else{
//         $box.fadeIn();
//     }
// }

// Creator Info Toggler
function __toggle_insurance_company(){

    var $box    = $('#__insurance_company_box'),
        cat    = $("#policy-category-id").val();

    if(typeof cat === 'undefined' || cat == 1){
        $("#insurance-company-id").val('').trigger('change');
        $box.fadeOut();
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

// // Change Branch Options on Creditor Change
// $('#_creditor-id').on('change', function(e){
//     var v = this.value,
//         $target = $('#_creditor-branch-id');
//     $target.empty();
//     if(v){
//         // load the object form
//         $.getJSON('<?php echo base_url()?>policies/gccbc/'+v, function(r){
//             // Update dropdown
//             if(r.status == 'success' && typeof r.options !== 'undefined'){
//                 $target.append($('<option>', {
//                     value: '',
//                     text : 'Select...'
//                 }));
//                 $.each(r.options, function(key, value) {
//                     $target.append($('<option>', {
//                         value: key,
//                         text : value
//                     }));
//                 });
//                 $target.prop('selectedIndex',0).trigger('change');
//               }
//         });
//     }
// });

// Policy Package Changer
$('#_portfolio-id').on('change', function(e){
    var v = this.value;
    $('#_policy-package-id').empty();
    if(v){
        // load the object form
        $.getJSON('<?php echo base_url()?>policies/gppp/'+v, function(r){

            // Update policy package options
            if(r.status == 'success' && typeof r.ppo !== 'undefined'){
                r.blank ? $('#_policy-package-id').append($('<option>', {value: '',text : 'Select...'})) : '';
                $.each(r.ppo, function(key, value) {

                    $('#_policy-package-id').append($('<option>', {
                        value: key,
                        text : value
                    }));
                });
            }
        });

        // Load template body from the reference supplied
        $.getJSON('<?php echo base_url()?>endorsements/template_reference/'+v + '/1', function(r){
            if(r.status == 'success' && typeof r.data !== 'undefined'){

                var $tmpl = $('#template-reference');
                $tmpl.empty();
                $tmpl.append($('<option>', {value: '',text : 'Select...'}));
                $.each(r.data, function(key, value) {
                    $tmpl.append($('<option>', {
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
        p = $('#_portfolio-id').val();

    if( p == '' || c == '')
    {
        toastr.error('Please select Portfolio & Customer first.');
        // Reset Loading
        $this.button('reset');
        return false;
    }
    var url = '<?php echo base_url()?>objects/find/' + c + '/'+ p;

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
// __toggle_creditor_info();
// $(document).on('ifChecked', 'input[name="flag_on_credit"]', function(e){
//     __toggle_creditor_info();
// });

// Hide Insurance Company if Policy Category is Regular
__toggle_insurance_company();
$(document).on('change', '#policy-category-id', function(e){
    __toggle_insurance_company();
});

// Hide agent list on Direct Discount Checked
__toggle_agent_info();
$(document).on('ifChecked', 'input[name="flag_dc"]', function(e){
    __toggle_agent_info();
});



// Initialize Select2
$.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //Initialize Select2 Elements
    $("#insurance-company-id").select2();
    $("#_portfolio-id").select2();
    $("#_marketing-staff").select2();
    $("#_agent-id").select2();
    $("#_creditor-id").select2();
    $("#_district-id").select2();
    $("#_creditor-branch-id").select2();
    $("#_policy-tags").select2();
    $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
});


// Load Txn Details from Endorsement Template
$('#template-reference').on('change', function(){
    var v = parseInt(this.value);
    if(v){
        // Load template body from the reference supplied
        $.getJSON('<?php echo base_url()?>endorsements/template_body/'+v, function(r){
            // Update dropdown
            if(r.status == 'success'){
                $('#txn-details').val(r.body);
            }
            else{
                toastr[r.status](r.message);
            }
        });
    }
});


// End Date Helper
$('#_end_datetime').closest('.form-group').css('margin-bottom','0');
$('#_ed-qty').on('focus', function(){$(this).select()});
$('#_btn-apply-end_date').on('click', function(){
    var st_dt = moment($('#_start_datetime').val()).format('YYYY-MM-DD'),
        type = $('#_ed-type').val(),
        qty  = $('#_ed-qty').val(),
        ed_dt = '';
        if(type != '' && qty !=''){
            ed_dt = moment(st_dt)
                            .add(qty, type)
                            .add('-1', 'day')
                            .format('YYYY-MM-DD') + ' 23:59:00';
            $('#_end_datetime').val(ed_dt);
            $('#_end_datetime').hide(200, function(){
                $(this).val(ed_dt).show();
            });
        }
});
</script>