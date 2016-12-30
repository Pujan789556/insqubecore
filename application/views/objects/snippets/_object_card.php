<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object - Policy Object Card
*/
?>
<div class="box box-bordered box-warning" id="iqb-object-card"">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">Policy Object Details</span>
        <span class="pull-right">
            <?php if( $__flag_object_editable ): ?>
                <span class="action divider"></span>
                <a href="#"
                    class="action trg-dialog-edit"
                    title="Edit Object Information"
                    data-toggle="tooltip"
                    data-box-size="large"
                    data-title='<i class="fa fa-pencil-square-o"></i> Edit Object'
                    data-url="<?php echo site_url('objects/edit/' . $record->id . '/y');?>"
                    data-form="#_form-object">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
            <?php endif?>
        </span>
        </h3>
    </div>
    <div class="box-body">
        <?php
        /**
        * Policy Object Details
        */
        $this->load->view('objects/snippets/_popup', ['record' => $record]);
        ?>
    </div>
</div>