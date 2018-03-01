<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy Transaction : Row Actions
 */
?>

<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" title="Edit User" data-toggle="dropdown" aria-expanded="true">
    <i class="fa fa-pencil-square-o margin-r-5"></i>Edit <i class="fa fa-caret-down"></i></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <?php
        /**
         * Update Premium
         * ---------------
         * Allowed Status: draft | unverified
         */
        if( CLAIM__is_editable($record->status, FALSE) ):
        ?>
            <li>
                <a href="#"
                    title="Edit Claim Draft"
                    data-toggle="tooltip"
                    class="trg-dialog-edit"
                    data-box-size="full-width"
                    data-title='<i class="fa fa-pencil-square-o"></i> Edit Claim Draft - <?php echo $record->claim_code?>'
                    data-url="<?php echo site_url('claims/edit_draft/' . $record->id . '/' . $ref);?>"
                    data-form="#_form-claims">
                    <i class="fa fa-pencil-square-o"></i> Edit Claim Draft</a>
            </li><li class="divider"></li>

            <?php if($record->status == IQB_CLAIM_STATUS_DRAFT && $this->dx_auth->is_authorized('claims', 'delete.claim.draft')): ?>
                <li>
                    <a href="#"
                        title="Delete Claim Draft"
                        data-toggle="tooltip"
                        class="trg-row-action"
                        data-confirm="true"
                        data-url="<?php echo site_url('claims/delete/' . $record->id . '/' . $ref);?>">
                            <i class="fa fa-trash-o"></i>
                            <span>Delete Claim Draft</span></a>
                </li><li class="divider"></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($ref === 'l'): ?>
            <li>
                <a href="<?php echo site_url('claims/details/' . $record->id);?>"
                    title="View Details"
                    data-toggle="tooltip">
                    <i class="fa fa-list-alt"></i> View Details</a>
            </li>
        <?php endif; ?>


        <?php

        // ------------------------------------------------------------------------------

        /**
         * Status "to Verified"
         */
        if( $record->status === IQB_CLAIM_STATUS_DRAFT && $this->dx_auth->is_authorized('claims', 'status.to.verified') ): ?>
            <li class="divider"></li>
            <li>
                <a href="#"
                    title="Verify Claim Draft"
                    data-toggle="tooltip"
                    data-confirm="true"
                    class="text-orange trg-dialog-action"
                    data-message="Are you sure you want to do this?<br/>You can not modify this claim anymore."
                    data-url="<?php echo site_url('claims/verify/' . $record->id . '/' . $ref );?>"
                ><i class="fa fa-check-square-o"></i> Verify Draft</a>
            </li>
        <?php
        endif;


        /**
         * Status "Back to Draft"
         */
        if( $record->status === IQB_CLAIM_STATUS_VERIFIED ):

            if($this->dx_auth->is_authorized('claims', 'status.to.draft')): ?>
                <li class="divider"></li><li>
                    <a href="#"
                        title="Back to Draft"
                        data-toggle="tooltip"
                        data-confirm="true"
                        class="text-maroon trg-dialog-action"
                        data-message="Are you sure you want to do this?<br/>Staff having lower level permission will be able to <strong>edit/delete</strong> this transaction."
                        data-url="<?php echo site_url('claims/to_draft/' . $record->id . '/' . $ref);?>">
                        <i class="fa fa-level-down"></i> Back To Draft</a>
                </li>
            <?php
            endif;

            /**
             * Assign Surveyors
             */
            if($this->dx_auth->is_authorized('claims', 'assign.claim.surveyors')): ?>
                <li class="divider"></li><li>
                    <a href="#"
                        title="Assign Surveyors"
                        data-toggle="tooltip"
                        class="text-green trg-dialog-edit"
                        data-title='<i class="fa fa-pencil-square-o"></i> Assign Surveyors - <?php echo $record->claim_code?>'
                        data-url="<?php echo site_url('claims/surveyors/' . $record->id . '/' . $ref);?>"
                        data-box-size="large"
                        data-form="#_form-claims">
                        <i class="fa fa-pencil-square-o"></i> Assign Surveyors</a>
                </li>
            <?php
            endif;

            /**
             * Assign Claim Scheme
             */
            if($this->dx_auth->is_authorized('claims', 'update.claim.scheme')): ?>
                <li class="divider"></li><li>
                    <a href="#"
                        title="Assign Claim Scheme"
                        data-toggle="tooltip"
                        class="text-green trg-dialog-edit"
                        data-title='<i class="fa fa-pencil-square-o"></i> Assign Claim Scheme - <?php echo $record->claim_code?>'
                        data-url="<?php echo site_url('claims/scheme/' . $record->id . '/' . $ref);?>"
                        data-box-size="large"
                        data-form="#_form-claims">
                        <i class="fa fa-pencil-square-o"></i> Assign Claim Scheme</a>
                </li>
            <?php
            endif;

            /**
             * Status to Approve
             *
             *  Prerequisite
             *  --------------
             *  1. settlement_claim_amount, settlement_amount_breakdown must be set
             *  2. claim_scheme_id must be set
             *  3. assessment_brief must be set
             *
             *
             */
            $flag_eligible_to_approve = CLAIM__approval_constraint($record, FALSE);
            if( $flag_eligible_to_approve == TRUE  && $this->dx_auth->is_authorized('claims', 'status.to.approved') ): ?>
                <li class="divider"></li><li>
                    <a href="#"
                        title="Approve"
                        data-toggle="tooltip"
                        data-confirm="true"
                        class="text-green trg-dialog-action"
                        data-message="Are you sure you want to APPROVE this claim?"
                        data-url="<?php echo site_url('claims/approve/' . $record->id . '/' . $ref );?>"
                    ><i class="fa fa-check-square-o"></i> Approve</a>
                </li>
            <?php
            endif;

            /**
             * Close Claim
             */
            if($this->dx_auth->is_authorized('claims', 'status.to.closed') || $this->dx_auth->is_authorized('claims', 'status.to.withdrawn') ): ?>

                <li class="divider"></li>
                <?php if($this->dx_auth->is_authorized('claims', 'status.to.closed')): ?>
                    <li>
                        <a href="#"
                            title="Close Claim"
                            data-toggle="tooltip"
                            class="text-red trg-dialog-edit"
                            data-title='<i class="fa fa-ban"></i> Close Claim - <?php echo $record->claim_code?>'
                            data-url="<?php echo site_url('claims/close/' . $record->id . '/' . $ref);?>"
                            data-form="#_form-claims">
                            <i class="fa fa-ban"></i> Close Claim</a>
                    </li>
                <?php
                endif;

                /**
                 * Withdraw Claim
                 */
                if($this->dx_auth->is_authorized('claims', 'status.to.withdrawn')): ?>
                    <li>
                        <a href="#"
                            title="Withdraw Claim"
                            data-toggle="tooltip"
                            class="text-red trg-dialog-edit"
                            data-title='<i class="fa fa-ban"></i> Withdraw Claim - <?php echo $record->claim_code?>'
                            data-url="<?php echo site_url('claims/withdraw/' . $record->id . '/' . $ref);?>"
                            data-form="#_form-claims">
                            <i class="fa fa-ban"></i> Withdraw Claim</a>
                    </li>
                <?php endif;?>

            <?php endif;?>

        <?php endif;?>
    </ul>
</div>






