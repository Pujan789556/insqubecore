<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agent Profile Widget Used By Other Modules
*/
?>
<div class="box box-bordered box-info">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
            <span class="pull-left">Agent Details</span>
            <span class="pull-right">
                <span class="action divider"></span>
                <a href="<?php echo site_url('agents/details/'.$record->id)?>" class="action" title="View Details" data-toggle="tooltip" target="_blank">
                    <i class="fa fa-external-link"></i>
                </a>
            </span>
        </h3>
    </div>
    <div class="box-body bg-gray-light">
        <?php
        /**
        * Profile Card
        */
        $this->load->view('setup/agents/snippets/_profile_card', ['record' => $record]);
        ?>
        <div class="box-footer no-border no-padding">
            <?php echo get_contact_widget($record->contact);?>
        </div>
    </div>
</div>