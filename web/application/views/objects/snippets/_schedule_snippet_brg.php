<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: BURGLARY
*/
$districts 	= district_dropdown();
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;

$form_elements  = _OBJ_MISC_BRG_validation_rules($record->portfolio_id);
?>
<strong>बीमाको विषयवस्तु रहेको स्थान/भवनको विवरण</strong><br/>
<table class="table table-bordered table-condensed no-margin" >
    <thead>
        <tr>
            <td>भवन/सम्पत्ति धनीको नाम</td>
            <td>ठेगाना</td>
            <td>सम्पर्क नं</td>
            <td>कित्ता नं.</td>
            <td>घर नं.</td>
            <td>टोल</td>
            <td>जिल्ला</td>
            <td>गा.वि.स./नगरपालिका</td>
            <td>वडा नं.</td>
            <td>तल्ला संख्या</td>
            <td>श्रेणी</td>
            <td>प्रयोगको सिमा</td>
        </tr>
    </thead>
    <?php
    $land_building	= $attributes->land_building ?? NULL;
    $item_count   	= count( $land_building->owner_name ?? [] );
    ?>
    <tbody>
        <?php
        if($item_count):
            for ($i=0; $i < $item_count; $i++):?>
        		<tr>
        			<td><?php echo $land_building->owner_name[$i] ?? ''?></td>
        			<td><?php echo $land_building->owner_address[$i] ?? ''?></td>
        			<td><?php echo $land_building->owner_contacts[$i] ?? ''?></td>
        			<td><?php echo $land_building->plot_no[$i] ?? ''?></td>
        			<td><?php echo $land_building->house_no[$i] ?? ''?></td>
        			<td><?php echo $land_building->tole[$i] ?? ''?></td>
        			<td><?php echo $land_building->district[$i] ? $districts[$land_building->district[$i]] : ''?></td>
        			<td><?php echo $land_building->vdc[$i] ?? ''?></td>
        			<td><?php echo $land_building->ward_no[$i] ?? ''?></td>
                    <td><?php echo $land_building->storey_no[$i] ?? ''?></td>
        			<td><?php echo _OBJ_MISC_BRG_item_building_category_dropdown( FALSE )[$land_building->category[$i]] ?? ''?></td>
                    <td><?php echo htmlspecialchars($land_building->used_for[$i] ?? '')?></td>
        		</tr>
        <?php
        	endfor;
        endif;?>
    </tbody>
</table>
<br>
<strong>बीमांक सम्पत्तिको विवरण</strong><br/>
<table class="table table-bordered table-condensed no-margin">
        <?php
        $section_elements   = $form_elements['items'];
        $items              = $attributes->items ?? NULL;
        $item_count         = count( $items ?? [] );
        ?>
        <thead>
            <tr>
                <td>SN</td>
                <?php foreach($section_elements as $elem): ?>
                    <td><?php echo $elem['label'] ?></td>
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
                            <?php echo $key == 'sum_insured' ? number_format($value, 2) : nl2br(htmlspecialchars($value));?>
                        </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
            <tr>
                <td colspan="2" class="text-bold">Total Sum Insured Amount(Rs.)</td>
                <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
            </tr>
        </tbody>
    </table>