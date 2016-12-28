<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Snippet : Profile Card
*/
?>
<div class="box box-primary">
    <div class="box-body box-profile">

        <?php if( $record->picture ):?>
            <img
                class="profile-user-img img-responsive img-circle ins-img-ip"
                title="View large"
                src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo thumbnail_name($record->picture);?>"
                alt="User profile picture"
                data-src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo $record->picture?>"
                onclick="InsQube.imagePopup(this, 'Profile Picture')">
        <?php else:?>
            <p class="text-center img-circle profile-user-img">
                <i class="ion-ios-person-outline text-muted img-alt"></i>
            </p>
        <?php endif?>


        <h3 class="profile-username text-center"><?php echo  $record->full_name;?></h3>

        <ul class="list-group list-group-unbordered">
            <li class="list-group-item">
                <b>Code</b> <span class="pull-right"><?php echo $record->code?></span>
            </li>
            <li class="list-group-item">
                <b>Type</b> <span class="pull-right"><?php echo $record->type == 'I' ? 'Individual' : 'Compamy';?></span>
            </li>
            <?php if($record->type == 'C'):?>
                <li class="list-group-item">
                    <b>Company Reg. No.</b> <span class="pull-right"><?php echo $record->company_reg_no?></span>
                </li>
            <?php else:?>
                <li class="list-group-item">
                    <b>Citizenship No.</b> <span class="pull-right"><?php echo $record->citizenship_no?></span>
                </li>
                <li class="list-group-item">
                    <b>Passport No.</b> <span class="pull-right"><?php echo $record->passport_no?></span>
                </li>
            <?php endif?>
            <li class="list-group-item no-border-b">
                <b>PAN</b> <span class="pull-right"><?php echo $record->pan?></span>
            </li>

        </ul>
    </div>
</div>
