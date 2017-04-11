<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Portfolios
 */
?>
<div class="box box-default box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Pool Portfolio Table</h4>
    </div>
    <div class="box-body" style="overflow-x: scroll;">
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Portfolio<?php echo field_compulsary_text( TRUE )?></th>
                    <th>Retention (%)<?php echo field_compulsary_text( TRUE )?></th>
                    <th>Commision (%)<?php echo field_compulsary_text( TRUE )?></th>
                    <th>IB Tax (%)<?php echo field_compulsary_text( TRUE )?></th>
                    <th>RI Tax (%)<?php echo field_compulsary_text( TRUE )?></th>
                    <th>Action</th>
                </tr>
            </thead>

            <?php
            $total_percentage = 0.00;
            ?>
            <tbody class="form-inline">
                <?php
                $i = 0;
                if($pool_portfolios):
                    foreach ($pool_portfolios as $portfolio):?>
                    <tr <?php echo $i == 0 ? 'id="__pool_portfolios_row"' : '' ?>>
                        <?php foreach($form_elements as $element):?>
                            <td>
                                <?php
                                /**
                                 * Load Single Element
                                 */
                                $element['_default']    = $portfolio->{$element['_field']} ?? '';
                                $element['_value']      = $element['_default'];
                                $this->load->view('templates/_common/_form_components_inline', [
                                    'form_elements' => [$element],
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </td>
                        <?php
                        endforeach;
                        if($i == 0):?>
                            <td class="text-success">&nbsp;</td>
                        <?php else:?>
                            <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove();'>Remove</a></td>
                        <?php endif;?>
                    </tr>
                <?php
                    $i++;
                    endforeach;
                else:?>
                    <tr id="__pool_portfolios_row">
                        <?php foreach($form_elements as $single_element):?>
                            <td>
                                <?php
                                /**
                                 * Load Single Element
                                 */
                                $this->load->view('templates/_common/_form_components_inline', [
                                    'form_elements' => [$single_element],
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </td>
                        <?php endforeach?>
                        <td class="text-success">&nbsp;</td>
                    </tr>
                <?php endif;?>
            </tbody>
        </table>
    </div>
    <div class="box-footer bg-info">
        <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__pool_portfolios_row', this)">Add More</a>
    </div>
</div>