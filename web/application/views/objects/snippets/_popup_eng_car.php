<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Contractor All Risk(ENG) Popover
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_ENG_CAR_validation_rules($record->portfolio_id);

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
    <div class="col-xs-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Basic Information</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$basic_elements = $form_elements['basic'];
                foreach($basic_elements as $elem): ?>
            		<tr>
            			<th width="30%"><?php echo $elem['label']; ?></th>
            			<td><?php echo $attributes->{$elem['_key']}; ?></td>
            		</tr>
        		<?php endforeach ?>
            </table>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Item Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['items'];
                $items              = $attributes->items ?? NULL;
                $item_count         = count( $items ?? [] );
                $insured_items_dropdown = _OBJ_ENG_CAR_insured_items_dropdown(true, false);
                $insured_items_dropdown_g = _OBJ_ENG_CAR_insured_items_dropdown(false, true);
                ?>
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    unset($section_elements[0]); // remove sn field
                    $i = 0;
                    foreach($insured_items_dropdown_g as $title => $groups): ?>

                        <tr><th colspan="3"><?php echo $title ?></th></tr>

                        <?php foreach($groups as $sn=>$label ): ?>
                            <tr>
                                <td><?php echo $label ?></td>
                                <?php foreach($section_elements as $elem):
                                        $key =  $elem['_key'];
                                        $value = $items[$i]->{$key} ?? '';
                                        $value = $elem['_data'][$value] ?? $value;
                                    ?>
                                        <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                            <?php echo $key == 'sum_insured' ? number_format($value, 2) : $value;?>
                                        </td>
                                    <?php endforeach ?>
                            </tr>
                        <?php
                        $i++;
                        endforeach;
                    endforeach; ?>
                    <tr>
                        <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>AOG - Earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide</td>
                        <td class="text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                        <td><?php echo $attributes->risk->deductibles; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Third Party Liability</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['third_party'];
                $items              = $attributes->third_party ?? NULL;
                $item_count         = count( $items->limit ?? [] );
                ?>
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $total_tp_liability =  _OBJ_ENG_CAR_compute_tpl_amount($attributes->third_party->limit ?? []);
                    for ($i=0; $i < $item_count; $i++): ?>
                        <tr>
                            <?php
                            foreach($section_elements as $elem):
                                $key =  $elem['_key'];
                                $value = $items->{$key}[$i];

                                // If we have dropdown, load label from this
                                $dd_data = $elem['_data'] ?? NULL;
                                if( $dd_data )
                                {
                                    $value = $dd_data[$value] ?? $value;
                                }

                                // Comput total
                                if( $key == 'limit' )
                                {
                                    $value  = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                                    // format this to echo
                                    $value = number_format($value, 2);
                                }
                            ?>

                                <td <?php echo $key == 'limit' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                    <tr>
                        <td class="text-bold">Total Third Party Liability(Rs.)</td>
                        <td class="text-bold text-right"><?php echo number_format($total_tp_liability, 2) ?></td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Other Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements       = $form_elements['others'];
                $section_object         = $attributes->others;
                foreach($section_elements as $elem): ?>
                    <tr>
                        <th><?php echo $elem['label']; ?></th>
                        <td><?php
                            if($elem['_type'] == 'dropdown')
                            {
                                echo $elem['_data'][$section_object->{$elem['_key']}];
                            }
                            else
                            {
                                echo $section_object->{$elem['_key']} ?? '';
                            }
                        ?></td>
                    </tr>
                <?php endforeach ?>

            </table>
        </div>
    </div>
</div>