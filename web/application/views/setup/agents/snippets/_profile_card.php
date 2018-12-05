<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Agent: Snippet : Profile Card
*/
?>
<div class="box box-widget widget-user-2">
    <!-- Add the bg color to the header using any of the bg-* classes -->
    <div class="widget-user-header bg-purple">
        <div class="widget-user-image">
            <?php if( $record->picture ):?>
                <img
                    class="img-circle ins-img-ip"
                    title="View large"
                    src="<?php echo site_url('static/media/agents/' . thumbnail_name($record->picture));?>"
                    alt="User profile picture"
                    data-src="<?php echo site_url('static/media/agents/' . $record->picture)?>"
                    onclick="InsQube.imagePopup(this, 'Profile Picture')">
            <?php endif?>
        </div>
        <!-- /.widget-user-image -->
        <h3 class="widget-user-username"><?php echo  $record->name;?></h3>
    </div>
    <div class="box-footer no-padding">
        <table class="table table-condensed no-margin no-border">
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
</div>
