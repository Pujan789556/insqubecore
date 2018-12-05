<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - Group Personnel Accident(GPA)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_MISC_GPA_validation_rules($record->portfolio_id);

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
                <?php
                $basic_elements = $form_elements['basic'];
                // Remove temporary document field
                array_pop($basic_elements);

                foreach($basic_elements as $elem): ?>
                    <tr>
                        <th><?php echo $elem['label']; ?></th>
                        <td><?php echo nl2br(htmlspecialchars($attributes->{$elem['_key']})); ?></td>
                    </tr>
                <?php endforeach ?>
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
                $section_elements   = $form_elements['items'];
                $items              = $attributes->items ?? NULL;
                $item_count         = count( $items ?? [] );
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
                    <?php
                    $i = 1;
                    foreach($items as $item_record): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <?php foreach($section_elements as $elem):
                                $key =  $elem['_key'];
                                $value = $item_record->{$key};
                            ?>

                                <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                    <?php echo $key == 'sum_insured' ? number_format($value, 2) : htmlspecialchars($value);?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                    <tr>
                        <td colspan="5" class="text-bold">जम्मा बीमांक रकम(रु)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>