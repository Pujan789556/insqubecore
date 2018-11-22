<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Premium Calculation Table
 *
 * English & Nepali
 */
if($lang == 'en')
{
    $cost_table_title           = 'Premium Calculation Table';
}
else
{
    $cost_table_title           = 'बीमाशुल्क गणना तालिका';
}
?>
<?php if($cost_calculation_table):?>
    <table class="table">
        <thead>
            <tr>
                <td colspan="2"><strong><?php echo $cost_table_title ?></strong></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cost_calculation_table as $row):?>
                <tr>
                    <td><?php echo $row->label ?></td>
                    <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

