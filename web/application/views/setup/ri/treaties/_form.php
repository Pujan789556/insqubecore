<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'id'    => '__form-ac-chart-of-account',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Treaty Basic Information</h4>
        </div>
        <div class="box-body">

            <div class="form-group">
                <label for="logo" class="col-sm-2 control-label">Treay File</label>
                <div class="col-sm-10 col-md-6">
                    <input type="file" name="file">
                    <p>
                        <?php if(isset($record->file) && !empty($record->file) ):?>
                            <a href="<?php echo site_url('ri_setup_treaties/download/' . $record->id);?>" target="_blank">Download Treaty File</a>
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
          <h4 class="box-title">Select Brokers</h4>
        </div>
        <div class="box-body">
            <?php
            $broker_form_element = $form_elements['brokers'][0]; // This is a single element
            $broker_list = $broker_form_element['_data'];
            unset($broker_form_element['_data']);
            foreach($broker_list as $broker_id=>$broker_name)
            {
                $broker_form_element['_checkbox_value']  = $broker_id;
                $broker_form_element['label']            = $broker_name;
                $broker_form_element['_value']           = in_array($broker_id, $treaty_borkers) ? $broker_id : '';

                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => [$broker_form_element],
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