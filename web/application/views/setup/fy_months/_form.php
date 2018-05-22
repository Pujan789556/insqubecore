<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : FY Months
 */
?>
<style type="text/css">
    td .form-group{margin-bottom: 0;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-bs_agro_categories',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        ['fiscal_yr_id' => $fy_record->id]); ?>

    <div class="box box-solid box-bordered">
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th class="hide">&nbsp;</th>
                    <th>Month</th>
                    <th>Starts at <?php echo field_compulsary_text( TRUE )?></th>
                    <th>Ends at <?php echo field_compulsary_text( TRUE )?></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($months as $month_id=>$month_name): ?>
                    <tr>
                        <?php
                        $single = $records[$month_id] ?? NULL;
                        foreach($form_elements as $element):?>
                            <td>
                                <?php
                                /**
                                 * Load Single Element
                                 */
                                $element['_default']    = $single->{$element['_field']} ?? '';
                                $element['_value']      = $element['_default'];

                                // Month Name Display in MonthID hidden field
                                if($element['_field'] == 'month_id')
                                {
                                    $element['_help_text']  = $month_name;
                                    $element['_default']    = $month_id;
                                    $element['_value']      = $element['_default'];
                                }

                                $this->load->view('templates/_common/_form_components_inline', [
                                    'form_elements' => [$element],
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>