<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agent: Snippet : Profile Card
*/
?>
<div class="box-body box-profile">
    <?php if( $record->picture ):?>
    <img
    class="profile-user-img img-responsive img-circle ins-img-ip"
    title="View large"
    src="<?php echo INSQUBE_MEDIA_URL?>agents/<?php echo thumbnail_name($record->picture);?>"
    alt="User profile picture"
    data-src="<?php echo INSQUBE_MEDIA_URL?>agents/<?php echo $record->picture?>"
    onclick="InsQube.imagePopup(this, 'Profile Picture')">
    <?php else:?>
    <p class="text-center img-circle profile-user-img">
        <i class="ion-ios-person-outline text-muted img-alt"></i>
    </p>
    <?php endif?>
    <h3 class="profile-username text-center"><?php echo  $record->name;?></h3><hr/>
    <table class="table no-margin no-border">
        <tbody>
            <tr>
                <td class="text-bold">UD Code</td>
                <td class="text-right"><?php echo $record->ud_code?></td>
            </tr>
            <tr>
                <td class="text-bold">BS Code</td>
                <td class="text-right"><?php echo $record->bs_code?></td>
            </tr>
            <tr>
                <td class="text-bold">Type</td>
                <td class="text-right"><?php echo $record->type == '1' ? 'Individual' : 'Compamy';?></td>
            </tr>
            <tr>
                <td class="text-bold">Active?</td>
                <td class="text-right">
                    <?php
                    if($record->active)
                    {
                    $active_str = '<i class="fa fa-circle text-green" title="Active" data-toggle="tooltip"></i>';
                    }
                    else
                    {
                    $active_str = '<i class="fa fa-circle-thin" title="Not Active" data-toggle="tooltip"></i>';
                    }
                    echo $active_str;
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>