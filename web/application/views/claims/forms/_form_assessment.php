<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Claim - Assessment
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-claims'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Claim Assessment Details (Reports)</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements,
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">

    function __default_status_remarks(a){
        $('#assessment-box').val('The mentioned loss has occurred during the period of insurance and covered under the insurance policy. The loss has been found to be reasonable and in order. Hence, it is recommended for settlement.');
    }
</script>