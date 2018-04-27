<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Contractor Plant & Machinary(ENG)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_ENG_CPM_validation_rules($record->portfolio_id);

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

            	foreach($basic_elements as $elem): ?>
            		<tr>
            			<th><?php echo $elem['label']; ?></th>
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
                $section_elements   = $form_elements['item'];
                $item              = $attributes->item ?? NULL;
                ?>
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach($section_elements as $elem):
                            $key =  $elem['_key'];
                            $value = $item->{$key};
                        ?>

                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : $value;?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                    <tr>
                        <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>