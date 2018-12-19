<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Credit Note: Details View
*/
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border border-dark bg-gray">
        <h3 class="no-margin">
	        <span class="pull-left">Credit Note Details</span>
            <span class="pull-right">
                <?php if( $this->dx_auth->is_authorized('ac_credit_notes', 'print.credit_note') ): ?>
                        <a href="<?php echo site_url('ac_credit_notes/print/' . $record->id)?>"
                            title="Print Credit Note"
                            class="btn btn-sm bg-navy"
                            target="_blank"
                            data-toggle="tooltip">
                            <i class="fa fa-print"></i>
                        </a>
                <?php endif?>
            </span>
        </h3>
    </div>

    <div class="box-body">
        <?php
        /**
         * Load Credit Note Card
         */
        $this->load->view('accounting/credit_notes/snippets/_credit_note_card');
        ?>
    </div>
</div>
