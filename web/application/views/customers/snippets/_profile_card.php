<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Snippet : Profile Card
*/
?>
<div class="box box-widget widget-user-2">
    <!-- Add the bg color to the header using any of the bg-* classes -->
    <div class="widget-user-header bg-aqua">
        <div class="widget-user-image">
            <?php if( $record->picture ):?>
                <img
                    class="img-circle ins-img-ip"
                    title="View large"
                    src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo thumbnail_name($record->picture);?>"
                    alt="User profile picture"
                    data-src="<?php echo INSQUBE_MEDIA_URL?>customers/<?php echo $record->picture?>"
                    onclick="InsQube.imagePopup(this, 'Profile Picture')">
            <?php endif?>
        </div>
        <!-- /.widget-user-image -->
        <h3 class="widget-user-username"><?php echo  $record->full_name;?></h3>
        <h5 class="widget-user-desc"><?php echo  $record->profession;?></h5>
    </div>
    <div class="box-footer">
        <table class="table table-condensed no-margin no-border">
            <tbody>
                <tr>
                    <td class="text-bold">Code</td>
                    <td class="text-right"><?php echo $record->code?></td>
                </tr>
                <?php if($record->type == 'I'): ?>
                    <tr>
                        <td class="text-bold">Grandfather</td>
                        <td class="text-right"><?php echo $record->grandfather_name?></td>
                    </tr>
                    <tr>
                        <td class="text-bold">Father</td>
                        <td class="text-right"><?php echo $record->father_name?></td>
                    </tr>
                    <tr>
                        <td class="text-bold">Mother</td>
                        <td class="text-right"><?php echo $record->mother_name?></td>
                    </tr>
                    <tr>
                        <td class="text-bold">Spouse</td>
                        <td class="text-right"><?php echo $record->spouse_name?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-bold">Type</td>
                    <td class="text-right"><?php echo $record->type == 'I' ? 'Individual' : 'Compamy';?></td>
                </tr>
                <?php if($record->type == 'C'):?>
                    <tr>
                        <td class="text-bold">Company Reg. No.</td>
                        <td class="text-right"><?php echo $record->company_reg_no?></td>
                    </tr>
                <?php else:?>
                    <tr>
                        <td class="text-bold">Citizenship/Passport No.</td>
                        <td class="text-right"><?php echo $record->identification_no?></td>
                    </tr>
                    <tr>
                        <td class="text-bold">DOB</td>
                        <td class="text-right"><?php echo $record->dob?></td>
                    </tr>
                <?php endif?>
                <tr>
                    <td class="text-bold">PAN</td>
                    <td class="text-right"><?php echo $record->pan?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
