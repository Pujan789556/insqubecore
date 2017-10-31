<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - PUBLIC LIABILITY(PL)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_MISC_PL_validation_rules($record->portfolio_id);

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

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Public Liability</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['public_liability'];
                $items              = $attributes->public_liability ?? NULL;
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
                                    $value = number_format($value, 2, '.', '');
                                }
                            ?>

                                <td <?php echo $key == 'limit' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                    <tr>
                        <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2, '.', '') ?></td>
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