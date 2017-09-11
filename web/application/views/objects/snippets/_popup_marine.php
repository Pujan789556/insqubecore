<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Marine Popover
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 		= _OBJ_MARINE_validation_rules($record->portfolio_id);

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
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Transit Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$section_elements 	= $form_elements['transit'];
				$section_object 	= $attributes->transit;
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
            					echo $section_object->{$elem['_key']};
            				}
            			?></td>
            		</tr>
        		<?php endforeach ?>
            </table>
        </div>
    </div>

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Sum Insured Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$section_elements 	= $form_elements['sum_insured'];
            	$section_object 	= $attributes->sum_insured;
            	foreach($section_elements as $elem): ?>
            		<tr>
            			<th><?php echo $elem['label']; ?></th>
            			<td class="text-right"><?php
            				if($elem['_type'] == 'dropdown')
            				{
            					echo $elem['_data'][$section_object->{$elem['_key']}];
            				}
            				else
            				{
            					echo $section_object->{$elem['_key']};
            				}
            			?></td>
            		</tr>
        		<?php endforeach ?>
                <tr>
                    <th>Sum Insured Amount (NRS)</th>
                    <td class="text-right"><strong><?php echo number_format($record->amt_sum_insured, 2, '.', '') ?></strong></td>
                </tr>
            </table>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Risk Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$section_elements 		= $form_elements['risk'];
				$section_object 		= $attributes->risk;
                $clauses = $section_object->clauses;

                // Remove Cluases
                unset($section_elements[count($section_elements)-1]);
                unset($section_object->clauses);
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
            					echo $section_object->{$elem['_key']};
            				}
            			?></td>
            		</tr>
        		<?php endforeach ?>
                <tr>
                    <th>Clauses</th>
                    <td>
                        <?php
                        $clauses_list = [];
                        $i = 1;
                        foreach($clauses as $cls )
                        {
                            $clauses_list[] = $i . '. ' . _OBJ_MARINE_clauses_list(FALSE)[$cls];
                            $i++;
                        }
                        echo implode('<br/>', $clauses_list);
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Surveyor Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$section_elements 		= $form_elements['surveyor'];
            	$section_object 		= $attributes->surveyor;
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
            					echo $section_object->{$elem['_key']};
            				}
            			?></td>
            		</tr>
        		<?php endforeach ?>
            </table>
        </div>
    </div>
</div>