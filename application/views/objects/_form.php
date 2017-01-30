<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */
?>
<?php echo form_open( $action_url,
                        [
                            'class' => 'form-horizontal form-iqb-general',
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
         * Hidden Field - Sub-Portfolio
         */
        if($sub_portfolio_record):?>
            <input type="hidden" name="sub_portfolio_id" id="_object-sub-portfolio-id" data-code="<?php echo $sub_portfolio_record->code?>" value="<?php echo $sub_portfolio_record->id?>">
            <div class="form-group">
                <label class="col-sm-2 control-label">Sub-Portfolio</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo $sub_portfolio_record->name_en;?></p>
                </div>
            </div>
        <?php endif;?>


        <?php
        /**
         * Load Form Components
         */

        // Portfolio
        $portfolio_elements = $form_elements['portfolio'] ?? NULL;
        if($portfolio_elements)
        {
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => [$portfolio_elements],
                'form_record'   => $record
            ]);
        }

        // sub-portfolio (Only on Regular Add/Edit)
        $sub_portfolio_elements = $form_elements['subportfolio'] ?? NULL;
        if( $sub_portfolio_elements):
            $sub_portfolio_id = set_value($sub_portfolio_elements['field'], '', FALSE) ?? ( $record->{$sub_portfolio_elements['field']} ?? '' );
        ?>
            <div class="form-group <?php echo form_error($sub_portfolio_elements['field']) ? 'has-error' : '';?>">
                <label for="" class="col-sm-2 control-label">
                    <?php echo $sub_portfolio_elements['label'] . field_compulsary_text( $sub_portfolio_elements['_required'] ?? FALSE );?>
                </label>
                <div class="col-sm-10">
                    <select name="<?php echo $sub_portfolio_elements['field']?>" class="form-control" placeholder="Sub-Portfolio" id="<?php echo $sub_portfolio_elements['_id'];?>">
                        <option value="" >Select...</option>
                        <?php foreach($sub_portfolio_elements['_data'] as $child):?>

                            <option value="<?php echo $child->id?>" data-code="<?php echo $child->code?>" <?php echo $sub_portfolio_id == $child->id ? 'selected="selected"' : ''?>><?php echo $child->name_en?></option>
                        <?php endforeach?>
                    </select>
                </div>
            </div>
        <?php endif?>
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
            var v = this.value,
            $subpf = $('#_object-sub-portfolio-id');
            $subpf.empty();
            if(v){
                // load the object form
                $.getJSON('<?php echo base_url()?>objects/gaf/'+v, function(r){

                    $('#_object-attribute-box').html(r.html);

                    // finally show the element box
                    $('#_object-box').show();

                    // checkbox Beautify if any
                    $('input.icheck').iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue'
                    });

                    // Update subportfolio dropdown
                    if( typeof r.spdd !== 'undefined'){
                        $subpf.append($('<option>', {
                            value: '',
                            text : 'Select...'
                        }));
                        $.each(r.spdd, function(key, value) {
                            $subpf.append($('<option>', {
                                "value": value.id,
                                "text" : value.name_en,
                                "data-code": value.code
                            }));
                        });
                        $subpf.prop('selectedIndex',0).trigger('change');
                    }

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
</script>
