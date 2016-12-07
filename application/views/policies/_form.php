<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */

echo form_open( $this->uri->uri_string(),
    [
        'class' => 'form-horizontal form-iqb-general',
        'id'    => '__testform',
        'data-pc' => '.bootbox-body' // parent container ID
    ],
    // Hidden Fields
    isset($record) ? ['id' => $record->id] : []); ?>

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
                  <p class="form-control-static">
                      <span id="customer-text" data-fillable-text="customer-text" class="margin-r-5"><?php echo $customer_name;?></span>
                      <a href="#" id="_find-customer" class=""><i class="fa fa-search"></i> Find Customer...</a>
                  </p>
                  <input type="hidden" id="customer-name" name="customer_name" value="<?php echo $customer_name;?>">
                  <input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer_id;?>">
                  <?php if(form_error('customer_id')):?><p class="help-block"><?php echo form_error('customer_id'); ?></p><?php endif?>
              </div>
            </div>
        </div>
    </div>


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
                  <p class="form-control-static">
                      <span id="object-text" data-fillable-text="object-text" class="margin-r-5"><?php echo $object_name;?></span>
                      <a href="#" id="_find-object" class=""><i class="fa fa-search"></i> Find Policy Object...</a>
                  </p>
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
    // $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
    //     //Initialize Select2 Elements
    //     $("#agent-id").select2();
    // });

    function __do_select(a){
        var $a = $(a);
        $($a.data('id-box')).val($a.data('id'));
        $($a.data('text-box')).html($a.data('text'));

        // Close the bootbox if any
        var $bootbox = $a.closest('.bootbox');
        $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
    }

    $('#_find-customer').on('click', function(e){
        e.preventDefault();
        $.getJSON('customers/page/f/y', function(r){
            if( typeof r.html !== 'undefined' && r.html != '' ){
                bootbox.dialog({
                    className: 'modal-default',
                    size: 'large',
                    // title: 'Find Customer',
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
</script>
