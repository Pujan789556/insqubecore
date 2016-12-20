<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '_form-object',
                            'data-pc' => '#form-box-object' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Portfolio Information</h3>
    </div>
    <div class="box-body">
        <?php
        if($action == 'add')
        {
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements,
                'form_record'   => $record
            ]);
        }
        else
        {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Portfolio</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo $record->portfolio_name;?></p>
                </div>
            </div>
            <?php
        }?>
    </div>

    <div id="_object-box">
        <div class="box-header with-border">
          <h3 class="box-title">Object Information</h3>
        </div>
        <div class="box-body" id="_object-attribute-box">
            <?php
            /**
             * Object Attributes Elements
             */
            echo $html_form_attribute_components ?? '';
            ?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<?php if($action == 'add'):?>
    <script type="text/javascript">
    (function($){
        var portfolio = $('#_object-portfolio-id').val();
        if(!portfolio){
            $('#_object-box').hide();
            $('button[type="submit"]', $('#_form-object')).attr('disabled','disabled');
            $('button.btn-primary[data-bb-handler="primary"]').attr('disabled','disabled');
        }
        $('#_object-portfolio-id').on('change', function(e){
            var v = this.value;
            if(v){
                // load the object form
                $.getJSON('<?php echo base_url()?>objects/gaf/'+v, function(r){

                    $('#_object-attribute-box').html(r.html);

                    // finally show the element box
                    $('#_object-box').show();

                    // Enable Submit
                    $('button[type="submit"]', $('#_form-object')).removeAttr('disabled');
                    $('button.btn-primary[data-bb-handler="primary"]').removeAttr('disabled','disabled');
                });

            }else{
                $('#_object-box').show();
                // disable form
                $('button[type="submit"]', $('#_form-object')).attr('disabled','disabled');
                $('button.btn-primary[data-bb-handler="primary"]').attr('disabled','disabled');
            }
        });
    })(jQuery);
    </script>
<?php endif;?>
