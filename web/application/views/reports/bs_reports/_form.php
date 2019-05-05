<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Branch
 */
?>
<?php echo form_open( $this->uri->uri_string(),
            [
                'class' => 'form-horizontal form-iqb-general',
                'id'    => '__form-report',
                'data-pc' => '#form-box-reports' // parent container ID
            ],
            // Hidden Fields
            isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box-header with-border">
      <h3 class="box-title">Supply Report Information</h3>
    </div>
    <div class="box-body">
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
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>


<script type="text/javascript">
    $(document).on('change', 'select[name="type"]', function(e){
        e.preventDefault();
        var $this = $(this),
            v = $this.val(),
            $target = $('select[name="fy_quarter_month"]');
            $target.empty();

        if( v == 'Q' ){
            $('#quarter-dropdown-template option').clone().appendTo($target);
        }else if( v == 'M' ){
            $('#month-dropdown-template option').clone().appendTo($target);
        }
    });
</script>