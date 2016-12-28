<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Snippet : Profile Card
*/
?>
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
    <h3 class="profile-username text-center"><?php echo  $record->full_name;?></h3><hr/>
    <table class="table no-margin no-border">
        <tbody>
            <tr>
                <td class="text-bold">Code</td>
                <td class="text-right"><?php echo $record->code?></td>
            </tr>
            <tr>
                <td class="text-bold">Type</td>
                <td class="text-right"><?php echo $record->type == '1' ? 'Individual' : 'Compamy';?></td>
            </tr>
            <?php if($record->type == 'C'):?>
                <tr>
                    <td class="text-bold">Company Reg. No.</td>
                    <td class="text-right"><?php echo $record->company_reg_no?></td>
                </tr>
            <?php else:?>
                <tr>
                    <td class="text-bold">Citizenship No.</td>
                    <td class="text-right"><?php echo $record->citizenship_no?></td>
                </tr>
                <tr>
                    <td class="text-bold">Passport No.</td>
                    <td class="text-right"><?php echo $record->passport_no?></td>
                </tr>
            <?php endif?>
            <tr>
                <td class="text-bold">PAN</td>
                <td class="text-right"><?php echo $record->pan?></td>
            </tr>
        </tbody>
    </table>
</div>
