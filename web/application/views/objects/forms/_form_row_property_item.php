<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Property Row
 */
// echo '<pre>'; print_r($item_record);exit;
?>
<div class="box-body">
    <div class="row">
        <div class="col-md-5">
            <div class="form-horizontal">
                <table class="table table-responsive table-bordered table-condensed">
                    <tr>
                        <td>
                            <?php
                            $section_elements = $form_elements['property_location'];
                            $this->load->view('templates/_common/_form_components_horz', [
                                'form_elements' => $section_elements,
                                'form_record'   => $item_record ?? NULL
                            ]);
                            ?>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
        <div class="col-md-7">
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php
                        $item_types = _OBJ_PROPERTY_item_type_dropdown(FALSE);
                        $single_rule = $form_elements['property_item_list'][array_keys($item_types)[0]];
                        $i = 0;
                        foreach($single_rule as $elem): ?>
                            <th <?php echo $i++ == 0 ? 'width="30%"' : '' ?>>
                                <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $section_elements = $form_elements['property_item_list'];
                    $item_list = $item_record->list ?? NULL;

                    // echo '<pre>'; print_r($item_list);exit;

                    foreach($item_types as $item_type_code => $type_name ): ?>
                        <tr>
                            <?php
                            /**
                             * Single Row
                             */
                            // $item_record = $tariff_formatted[$item_type_code] ?? NULL;

                            $single_item = $item_list->{$item_type_code} ?? NULL;
                            $single_rule = $section_elements[$item_type_code];
                            if( !$single_item )
                            {
                                $single_rule[0]['_default'] = $item_type_code;
                            }
                            $single_rule[0]['_extra_html_below'] = '<strong>' . $item_type_code . '.</strong> ' . $type_name;

                            // echo '<pre>'; print_r($single_item); print_r($single_rule);

                            $this->load->view('templates/_common/_form_components_table', [
                                'form_elements' => $single_rule,
                                'form_record'   => $single_item
                            ]);
                            ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

