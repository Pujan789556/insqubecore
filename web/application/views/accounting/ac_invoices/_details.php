<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Invoice: Details View
*/
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border border-dark bg-gray">
        <h3 class="no-margin">
	        <span class="pull-left">Invoice Details</span>
            <span class="pull-right">
                <?php if( $this->dx_auth->is_authorized('ac_invoices', 'print.invoice') ): ?>
                        <a href="<?php echo site_url($this->data['_url_base'] .'/print/invoice/' . $record->id)?>"
                            title="Print Invoice"
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
         * Load Invoice Card
         */
        $this->load->view($this->data['_view_base'] .'/snippets/_invoice_card');
        ?>
    </div>
</div>
