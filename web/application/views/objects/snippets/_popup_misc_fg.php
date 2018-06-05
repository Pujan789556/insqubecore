<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - FIDELITY GUARANTEE (FG)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_MISC_FG_validation_rules($record->portfolio_id);

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
    <div class="col-sm-12">
        <div class="box box-solid box-bordered scroll-x">
            <div class="box-header with-border">
                <h4 class="box-title">बीमालेखले रक्षावरण गर्ने बीमित कर्मचारीहरु</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['items'];
                $items              = $attributes->items ?? NULL;
                $item_count         = count( $items->sum_insured ?? [] );
                ?>
                <thead>
                    <tr>
                        <th>क्र सं</th>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i=0; $i < $item_count; $i++): ?>
                        <tr>
                            <td><?php echo ($i+1) ?></td>
                            <?php foreach($section_elements as $elem):
                                $key =  $elem['_key'];
                                $value = $items->{$key}[$i];
                            ?>
                                <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                    <?php echo $key == 'sum_insured' ? number_format($value, 2) : htmlspecialchars($value);?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                    <tr>
                        <td colspan="4" class="text-bold">जम्मा बीमांक रकम(रु)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>