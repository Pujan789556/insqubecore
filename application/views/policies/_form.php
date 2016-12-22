<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy
 */

echo form_open( $this->uri->uri_string(),
    [
        'class' => 'form-horizontal form-iqb-general',
        'id'    => '_form-policy',
        'data-pc' => '.bootbox-body' // parent container ID
    ],
    // Hidden Fields
    isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box-header no-border gray">
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

    <div class="box-header no-border gray">
        <h4 class="box-title">Select Policy Package</h4>
    </div>
    <div class="box-body" id="_policy-package-box">
        <?php
        /**
         * Load Form Components
         */
        $package_elements = $form_elements['package'];
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $package_elements,
            'form_record'   => $record
        ]);
        ?>
    </div>


    <div class="box-header no-border gray">
      <h4 class="box-title">Select Customer</h4>
    </div>
    <div class="box-body">

        <div id="_customer-box">
            <div class="form-group <?php echo form_error('customer_id') ? 'has-error' : '';?>">
              <label class="col-sm-2 control-label">Customer</label>
              <div class="col-sm-10">
                  <?php
                  $customer_id = set_value('customer_id', '', FALSE) ?? ($record->customer_id ?? '');
                  $customer_name = set_value('customer_name', '', FALSE) ?? ($record->customer_name ?? '');
                  ?>
                  <span id="customer-text" class="mrg-r-5 text-purple" readonly><?php echo $customer_name;?></span>
                  <a href="#" id="_find-customer" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Customer"><i class="fa fa-filter"></i>...</a>
                  <input type="hidden" id="customer-name" name="customer_name" value="<?php echo $customer_name;?>">
                  <input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer_id;?>">
                  <?php if(form_error('customer_id')):?><p class="help-block"><?php echo form_error('customer_id'); ?></p><?php endif?>
              </div>
            </div>
        </div>
    </div>



    <div class="box-header no-border gray">
      <h4 class="box-title">Select Policy Object</h4>
    </div>
    <div class="box-body">

        <div id="_policy-object-box">
            <div class="form-group <?php echo form_error('object_id') ? 'has-error' : '';?>">
              <label class="col-sm-2 control-label">Policy Object</label>
              <div class="col-sm-10">
                  <?php
                  $object_id = set_value('object_id', '', FALSE) ?? ($record->object_id ?? '');
                  $object_name = set_value('object_name', '', FALSE) ?? ($record->object_name ?? '');
                  ?>
                  <span id="object-text" class="mrg-r-5 text-purple" readonly><?php echo $object_name;?></span>
                  <a href="#" id="_find-object" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Policy Object"><i class="fa fa-filter"></i>...</a>
                  <input type="hidden" id="object-name" name="object_name" value="<?php echo $object_name;?>">
                  <input type="hidden" id="object-id" name="object_id" value="<?php echo $object_id;?>">
                  <?php if(form_error('object_id')):?><p class="help-block"><?php echo form_error('object_id'); ?></p><?php endif?>
              </div>
            </div>
        </div>
    </div>

    <div class="box-header no-border gray">
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

    <div class="box-header no-border gray">
      <h4 class="box-title">Select Staff</h4>
    </div>
    <div class="box-body">

        <?php
        /**
         * Load Form Components
         */
        $staff_elements = $form_elements['staff'];
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $staff_elements,
            'form_record'   => $record
        ]);
        ?>
    </div>

    <div class="box-header no-border gray">
      <h4 class="box-title">Select Agent</h4>
    </div>
    <div class="box-body">

        <?php
        /**
         * Load Form Components
         */
        $agent_elements = $form_elements['agent'];
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $agent_elements,
            'form_record'   => $record
        ]);
        ?>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
function __do_select(a){
    var $a = $(a);
    $($a.data('id-box')).val($a.data('id'));
    $($a.data('text-box')).html( $($a.data('text-box-ref')).html() );

    // Close the bootbox if any
    var $bootbox = $a.closest('.bootbox');
    $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
}

// Policy Package Changer
$('#_portfolio-id').on('change', function(e){
    var v = this.value;
    $('#_policy-package-id').empty();
    if(v){
        // load the object form
        $.getJSON('<?php echo base_url()?>policies/gppp/'+v, function(r){
            // Update dropdown
            if(r.status == 'success' && typeof r.options !== 'undefined'){
                $.each(r.options, function(key, value) {
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
    $.getJSON('customers/page/f/y', function(r){
        if( typeof r.html !== 'undefined' && r.html != '' ){
            bootbox.dialog({
                className: 'modal-default',
                size: 'large',
                closeButton: false,
                message: r.html,
                buttons:{
                    cancel: {
                        label: "Close",
                        className: 'btn-default'
                    }
                }
            });
        }
    });
});

// Object Finder
$('#_find-object').on('click', function(e){
    e.preventDefault();
    var c = $('#customer-id').val(),
        p = $('#_portfolio-id').val();

    if( p == '' || c == '')
    {
        toastr.error('Please select customer and portfolio first.');
        return false;
    }
    var url = 'objects/find/'+c + '/'+ p;

    $.getJSON(url, function(r){
        if( typeof r.html !== 'undefined' && r.html != '' ){
            bootbox.dialog({
                className: 'modal-default',
                size: 'large',
                closeButton: false,
                message: r.html,
                buttons:{
                    cancel: {
                        label: "Close",
                        className: 'btn-default'
                    }
                }
            });
        }
    });
});

// Datepicker
$('.input-group.date').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd'
});

$.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //Initialize Select2 Elements
    $("#_marketing-staff").select2();
    $("#_agent-id").select2();
    $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
});
</script>
