<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: POULTRY SUB-PORTFOLIO (AGRICULTURE)
*/
$this->load->helper('ph_agr_poultry');
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_AGR_POULTRY_validation_rules($record->portfolio_id);

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
        <div class="box box-solid box-bordered" style="overflow-x: scroll;">
            <div class="box-header with-border">
                <h4 class="box-title">पन्छीको विवरण</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['items'];
                $items              = $attributes->items ?? NULL;
                $item_count         = count( $items->sum_insured ?? [] );
                ?>
                <thead>
                    <tr>
                        <th>क्र. सं.</th>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i=0; $i < $item_count; $i++): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <?php foreach($section_elements as $elem):
                                $key =  $elem['_key'];
                                $value = $items->{$key}[$i];

                                $elem_data  = $elem['_data'] ?? NULL;
                                if($elem_data){
                                    $value = $elem_data[$value];
                                }
                                ?>

                                <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                    <tr>
                        <td colspan="6" class="text-bold">जम्मा बीमांक रकम(रु)</td>
                        <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2, '.', '') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">आधारभूत जानकारीहरु</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
            	<?php
            	$basic_elements = $form_elements['basic'];

            	foreach($basic_elements as $elem): ?>
            		<tr>
            			<th><?php echo $elem['label']; ?></th>
            			<td>
                            <?php
                            $value      = $attributes->{$elem['_key']};
                            $elem_data  = $elem['_data'] ?? NULL;
                            if($elem_data){
                                $value = $elem_data[$value];
                            }
                            echo $value;
                            ?>
                        </td>
            		</tr>
        		<?php endforeach ?>
            </table>
        </div>
    </div>

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">कृषिजन्य सुबिधाहरू</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $basic_elements = $form_elements['facilities'];

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
                <h4 class="box-title">विगत १ वर्षमा तपाईंको पन्छीमा भएको हानी नोक्सानीको विवरण</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $section_elements   = $form_elements['damages'];
                $damages            = $attributes->damages ?? NULL;
                $item_count         = count( $damages->year ?? [] );
                ?>
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i=0; $i < $item_count; $i++): ?>
                        <tr>
                            <?php foreach($section_elements as $elem):
                                $key =  $elem['_key'];
                                $value = $damages->{$key}[$i];
                            ?>

                                <td>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endfor ?>
                </tbody>
            </table>
        </div>
    </div>
</div>