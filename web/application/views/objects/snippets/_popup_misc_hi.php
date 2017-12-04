<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - HEALTH INSURANCE (HI)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$item_headings 	= _OBJ_MISC_HI_item_headings_dropdown();

$ref = $ref ?? '';
if($ref === 'policy_overview_tab')
{
    $col = 'col-xs-12';
}
else
{
    $col = 'col-md-6';
}
?>

<div class="row">
    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Basic Information</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <tr>
                    <th>Staff List File</th>
                    <td>
                        <?php echo anchor('objects/download/' . $attributes->document, 'Download', 'target="_blank"') ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box box-solid box-bordered scroll-x">
            <div class="box-header with-border">
                <h4 class="box-title">बीमालेखले रक्षावरण गर्ने बीमितहरु</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $items              = $attributes->items ?? NULL;
                $item_count         = count( $items->sum_insured ?? [] );
                ?>
                <thead>
                    <tr>
                        <?php foreach($item_headings as $key=>$label): ?>
                            <th><?php echo $label ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i=0; $i < $item_count; $i++): ?>
                        <tr>
                            <?php foreach($item_headings as $key=>$label):
                                $value = $items->{$key}[$i];
                            ?>

                                <td <?php echo $key == 'sum_insured' || $key == 'premium' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                    <tr>
                        <td colspan="6" class="text-bold">जम्मा (रु)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2, '.', '') ?></td>
                        <td class="text-bold text-right"><?php echo number_format( _OBJ_MISC_HI_compute_total_premium_amount ($items), 2, '.', '') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>