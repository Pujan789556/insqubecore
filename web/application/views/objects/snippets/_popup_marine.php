<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Marine Popover
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;

$form_elements 		= _OBJ_MARINE_validation_rules($record->portfolio_id);
?>

<div class="row">
    <div class="col-md-6">
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

    <div class="col-md-6">
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

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Risk Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$section_elements 		= $form_elements['risk'];
				$section_object 		= $attributes->risk;
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