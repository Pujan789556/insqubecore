<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */
?>
<?php echo form_open( $action_url,
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '_form-object',
                            'data-pc' => '#form-box-object' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="no-margin">Portfolio Information</h3>
    </div>
    <div class="box-body">

        <?php
        /**
         * Hidden Field - Portfolio
         */
        if($portfolio_record):?>
            <input type="hidden" name="portfolio_id" id="_object-portfolio-id" value="<?php echo $portfolio_record->id?>">
            <div class="form-group">
                <label class="col-sm-2 control-label">Portfolio</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo $portfolio_record->name_en;?></p>
                </div>
            </div>
        <?php endif;?>


        <?php
        /**
         * Load Form Components
         */
        // Portfolio
        if( !empty($form_elements) )
        {
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements,
                'form_record'   => $record
            ]);
        }
        ?>
    </div>

    <div id="_object-box">
        <div class="box-header with-border">
          <h3 class="no-margin">Object Information</h3>
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

<?php if($action == 'add' && $from_widget === 'n'):?>
    <script type="text/javascript">
    (function($){
        var portfolio = $('#_object-portfolio-id').val();
        if(!portfolio){
            $('#_object-box').hide();
            $('button[type="submit"]', $('#_form-object')).attr('disabled','disabled');
            $('button.btn-primary[data-bb-handler="primary"]').attr('disabled','disabled');
        }

        // Portfolio Change - Render attribute form & sub-portfolio
        $('#_object-portfolio-id').on('change', function(e){
            var v = this.value;
            if(v){
                // load the object form
                $.getJSON('<?php echo base_url()?>objects/get_attribute_form/'+v, function(r){

                    $('#_object-attribute-box').html(r.html);

                    // finally show the element box
                    $('#_object-box').show();

                    // checkbox Beautify if any
                    $('input.icheck').iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue'
                    });

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
<script type="text/javascript">
    // Datepicker
    $('.input-group.date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });

    // Initialize Select2 if any
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $('select[data-ddstyle="select"]').select2();
        $('.bootbox.modal').removeAttr('tabindex'); // modal workaround
    });
</script>
