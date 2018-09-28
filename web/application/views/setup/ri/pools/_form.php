<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Pool
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-pool-setup',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Pool Basic Information</h4>
        </div>
        <div class="box-body form-horizontal ">

            <div class="form-group">
                <label for="logo" class="col-sm-2 control-label">Treay File</label>
                <div class="col-sm-10 col-md-6">
                    <input type="file" name="file">
                    <p>
                        <?php if(isset($record->file) && !empty($record->file) ):?>
                            <a href="<?php echo site_url('ri_setup_pools/download/' . $record->id);?>" target="_blank">Download Treaty File</a>
                        <?php endif?>
                    </p>
                </div>
            </div>
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

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Select Portfolios</h4>
        </div>
        <div class="box-body">
            <?php
            $portfolio_form_element = $form_elements['portfolios'][0]; // This is a single element
            $portfolio_list = $portfolio_form_element['_data'];
            unset($portfolio_form_element['_data']);
            foreach($portfolio_list as $portfolio_id=>$portfolio_name)
            {
                $portfolio_form_element['_checkbox_value']  = $portfolio_id;
                $portfolio_form_element['label']            = $portfolio_name;
                $portfolio_form_element['_value']           = in_array($portfolio_id, $treaty_portfolios) ? $portfolio_id : '';

                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => [$portfolio_form_element],
                    'form_record'   => NULL
                ]);
            }
            ?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
    // Datepicker
    $('.input-group.date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });
</script>