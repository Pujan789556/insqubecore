<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties: Snippet : Profile Card
*/
?>
<div class="box box-widget widget-user-2">
    <div class="widget-user-header bg-aqua">
        <h3><?php echo  $record->full_name;?></h3>
    </div>
    <div class="box-footer">
        <table class="table table-condensed no-margin no-border">
            <tbody>
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
</div>
