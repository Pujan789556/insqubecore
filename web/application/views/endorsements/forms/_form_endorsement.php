<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy TXN - Endorsement
 */
$hidden_fields = ['policy_id' => $policy_record->id];
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
          <h4 class="box-title">Supply Basic Information</h4>
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

    <?php if(isset($form_elements['refund_compute_reference'])): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Premium Computation Basis</h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['refund_compute_reference'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>

    <?php if(isset($form_elements['customer'])): ?>

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

    <?php if(isset($form_elements['fees'])): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Transfer Fee</h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['fees'],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    <?php endif ?>

    <?php if(isset($form_elements['terminate'])): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Terminate Policy?</h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements['terminate'],
                    'form_record'   => $record
                ]);
                ?>
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
