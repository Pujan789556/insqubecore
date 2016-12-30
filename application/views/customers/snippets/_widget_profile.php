<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer Profile Widget Used By Other Modules
*/
?>
<div class="box box-bordered box-info" id="iqb-widget-customer-profile">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">Customer Details</span>
        <span class="pull-right">
            <span class="action divider"></span>
            <a href="<?php echo site_url('customers/details/'.$record->id)?>" class="action" title="View Details" data-toggle="tooltip" target="_blank">
                <i class="fa fa-external-link"></i>
            </a>
            <?php if( $this->dx_auth->is_authorized('customers', 'edit.customer') ): ?>
                <a href="#"
                    class="action narrow trg-dialog-edit"
                    title="Edit Customer Information"
                    data-toggle="tooltip"
                    data-box-size="large"
                    data-title='<i class="fa fa-pencil-square-o"></i> Edit Customer'
                    data-url="<?php echo site_url('customers/edit/' . $record->id . '/y');?>"
                    data-form="#_form-customer">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
            <?php endif?>
        </span>
        </h3>
    </div>
    <div class="box-body bg-gray-light">
        <?php
        /**
        * Customer Overview
        */
        $this->load->view('customers/snippets/_profile_card', ['record' => $record]);
        ?>
        <div class="box-footer no-border no-padding">
            <?php echo get_contact_widget($record->contact);?>
        </div>
    </div>
</div>