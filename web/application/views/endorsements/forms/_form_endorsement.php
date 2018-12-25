<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy TXN - Endorsement
 */
$hidden_fields = ['policy_id' => $policy_record->id, 'txn_type' => $txn_type];
if(isset($record))
{
    $hidden_fields['id'] = $record->id;
}
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-horizontal form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-endorsements'
                        ],
                        // Hidden Fields
                        $hidden_fields); ?>
    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Basic Information</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements['basic'],
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>




    <?php
    /**
     * Premium Compute References
     */
    if(isset($form_elements['compute_references'])): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Premium Computation References</h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['compute_references'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>

    <?php
    /**
     * Endorsement Dates
     */
    if(isset($form_elements['dates'])): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Endorsement Dates</h4>
            </div>
            <div class="box-body" id="endorsment-dates">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['dates'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>

    <?php
    /**
     * Type Specific - Cusotmer
     */
    if(isset($form_elements['customer'])): ?>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Which Customer to Transfer this Policy?</h4>
            </div>
            <div class="box-body">
                <div id="_customer-box">
                    <div class="form-group <?php echo form_error('transfer_customer_id') ? 'has-error' : '';?>">
                      <label class="col-sm-2 control-label">Customer<?php echo field_compulsary_text( TRUE )?></label>
                      <div class="col-sm-10">
                          <?php
                          $transfer_customer_id = $record->transfer_customer_id ?? '';
                          if(set_value('transfer_customer_id', '', FALSE))
                          {
                            $transfer_customer_id = set_value('transfer_customer_id', '', FALSE);
                          }

                          $customer_name_en = $record->transfer_customer_name_en ?? '';
                          if(set_value('customer_name_en', '', FALSE))
                          {
                            $customer_name_en = set_value('customer_name_en', '', FALSE);
                          }
                          ?>
                          <span id="_text-ref-customer" class="mrg-r-5 text-purple" readonly><?php echo $customer_name_en;?></span>
                          <a href="#" id="_find-customer" class="btn btn-sm btn-round bg-purple" data-toggle="tooltip" title="Find Customer"><i class="fa fa-filter"></i>...</a>
                          <input type="hidden" id="customer-text" name="customer_name_en" value="<?php echo $customer_name_en;?>">
                          <input type="hidden" id="customer-id" name="transfer_customer_id" value="<?php echo $transfer_customer_id;?>">
                          <?php if(form_error('transfer_customer_id')):?><span class="help-block"><?php echo form_error('transfer_customer_id'); ?></span><?php endif?>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<?php if(isset($form_elements['customer'])): ?>
    <script type="text/javascript">

        // Customer Selectable
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
    </script>
<?php endif ?>

<script type="text/javascript">
// Initialize Select2
$.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //Initialize Select2 Elements
    $("#_marketing-staff").select2();
    $("#_agent-id").select2();
    $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
});


// Toggler of Dates for Time Extended
function __toggle_dates_on_time_extended(v)
{
    // Hide Start Date if Duration Prorata
    var $st_dt = $('#start_date');
    if(v == 1)
    {
        $st_dt.closest('.form-group ').hide(200);
    }else{
        $st_dt.closest('.form-group ').show(200);
    }
}

// Time Extended Field Toggler
if(('#te_compute_ref').length){
    __toggle_dates_on_time_extended(parseInt($('#te_compute_ref').val()));
}

// Time Extended - Date Toggle
$('#te_compute_ref').on('change', function(){
    __toggle_dates_on_time_extended(parseInt(this.value));
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
})
</script>
