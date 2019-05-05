<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties: Party Profile Widget Used By Other Modules
*/
?>
<div class="box box-bordered box-info" id="iqb-widget-ac_party-profile">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">
            Accounting Party Details
            <a href="<?php echo site_url($this->data['_url_base'] . '/details/'.$record->id)?>" class="action" title="View Details" data-toggle="tooltip" target="_blank">
                <i class="fa fa-external-link small"></i>
            </a>
        </span>
        <span class="pull-right">
            <?php if( $this->dx_auth->is_authorized('ac_parties', 'edit.ac_party') ): ?>
                <a href="#"
                    class="trg-dialog-edit btn btn-primary btn-sm"
                    title="Edit Accounting Party Information"
                    data-toggle="tooltip"
                    data-box-size="large"
                    data-title='<i class="fa fa-pencil-square-o"></i> Edit Accounting Party'
                    data-url="<?php echo site_url($this->data['_url_base'] . '/edit/' . $record->id . '/y');?>"
                    data-form="#_form-ac_party">
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
        $this->load->view($this->data['_view_base'] . '/snippets/_profile_card', ['record' => $record]);
        ?>
        <div class="box-footer no-border no-padding">
            <?php echo get_contact_widget($record->contact);?>
        </div>
    </div>
</div>