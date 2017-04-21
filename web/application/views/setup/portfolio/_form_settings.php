<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio Settings
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            // 'id'    => '__testform',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['fiscal_yr_id' => $record->fiscal_yr_id] : []); ?>
    <div class="box-header with-border">
        <h3 class="box-title">Fiscal Year</h3>
    </div>
    <div class="box-body">
        <?php
        /**
         * Load Form Components
         */
        if($action === 'add')
        {
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements,
                'form_record'   => $record,
                'grid_form_control' => 'col-sm-10 col-md-4'
            ]);
        }
        else
        {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">
                    Fiscal Year
                </label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo $record->code_np . " ({$record->code_en})"?></p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="box-header with-border">
        <h3 class="box-title">Portfolio Settings For Selected Fiscal Year</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
            $portfolio_repeat_count = 0;
            $setting_fields = $sectioned_elements;
            foreach($portfolios_tree as $portfolio_parent=>$child_portfolios):
            ?>
                <div class="box box-solid box-bordered">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $portfolio_parent?></h3>
                    </div>
                    <div class="box-body">
                        <?php
                        foreach($child_portfolios as $portfolio_id=>$portfolio_name):

                            $setting_id = '';
                            if($action === 'edit')
                            {
                                foreach($settings as $t)
                                {
                                    if( $t->portfolio_id == $portfolio_id)
                                    {
                                        $setting_fields['agent_commission']['values'][$portfolio_repeat_count]   = $t->agent_commission;
                                        $setting_fields['direct_discount']['values'][$portfolio_repeat_count]    = $t->direct_discount;
                                        $setting_fields['stamp_duty']['values'][$portfolio_repeat_count]         = $t->stamp_duty;
                                        $setting_fields['default_duration']['values'][$portfolio_repeat_count]   = $t->default_duration;
                                        $setting_fields['flag_short_term']['values'][$portfolio_repeat_count]    = $t->flag_short_term;

                                        $setting_id      = $t->id;
                                        break;
                                    }
                                }
                                echo form_hidden('setting_ids[]', $setting_id);
                            }

                            echo form_hidden('portfolio_id[]', $portfolio_id);
                            ?>
                            <div class="box box-solid box-bordered">
                                <div class="box-header bg-gray-light"><h3 class="box-title"><?php echo ucwords($portfolio_name)?></h3></div>
                                <div class="box-body form-horizontal">

                                    <?php
                                    foreach($setting_fields as $field_name => $details)
                                    {
                                        // Get the Value
                                        $input_name = "{$field_name}[]";
                                        $values = $details['values'] ?? [];
                                        $input_value = $values["{$portfolio_repeat_count}"] ?? '';

                                        // From Form Submission
                                        if( set_value("{$field_name}[$portfolio_repeat_count]") )
                                        {
                                            $input_value = set_value("{$field_name}[$portfolio_repeat_count]");
                                        }
                                        $details['_default']    = $input_value;
                                        $details['_value']      = $input_value;

                                        $this->load->view('templates/_common/_form_components_horz', [
                                            'form_elements' => [$details],
                                            'form_record'   => NULL,
                                            'grid_label'    => 'col-sm-3',
                                            'grid_form_control' => 'col-sm-9'
                                        ]);
                                    }
                                    ?>
                                </div>
                                <div class="box-body">
                                    <hr/>
                                    <div class="form-inline">
                                        <div class="box box-solid box-bordered">
                                            <div class="box-header with-border bg-teal">
                                                <h4 class="box-title">Short Term Policy Rate</h4>
                                            </div>
                                            <div class="box-body">
                                                <table class="table table-condensed">
                                                    <thead>
                                                        <tr>
                                                            <th>Title<?php echo field_compulsary_text( TRUE )?></th>
                                                            <th>Duration (days)<?php echo field_compulsary_text( TRUE )?></th>
                                                            <th>Rate (%)<?php echo field_compulsary_text( TRUE )?></th>
                                                        </tr>
                                                    </thead>

                                                    <?php
                                                    $__stpr_box_id = '__stpr_box_' . $portfolio_id;
                                                    $__stpr_row_id = '__stpr_row_' . $portfolio_id;

                                                    $spr_validation_rules_per_portfolio = $spr_validation_rules["PORT_" . $portfolio_id];
                                                    ?>
                                                    <tbody id="<?php echo $__stpr_box_id?>">
                                                        <?php $i = 0;?>
                                                        <?php foreach ($spr_validation_rules_per_portfolio as $stpr_single_row):?>
                                                            <tr <?php echo $i == 0 ? 'id="' . $__stpr_row_id . '"' : '' ?>>
                                                                <?php foreach($stpr_single_row as $stpr):?>
                                                                    <td>
                                                                        <div class="form-group <?php echo form_error($stpr['field']) ? 'has-error' : '';?>">
                                                                            <input
                                                                                title="<?php echo $stpr['label'];?>"
                                                                                type="text"
                                                                                name="<?php echo $stpr['field'];?>"
                                                                                class="form-control"
                                                                                placeholder="<?php echo $stpr['label'];?>"
                                                                                value="<?php echo $stpr['value'];?>">
                                                                            <?php if(form_error($stpr['field'])):?><span class="help-block"><?php echo form_error($stpr['field']); ?></span><?php endif?>
                                                                        </div>
                                                                    </td>
                                                                <?php endforeach?>
                                                                <?php if($i == 0):?>
                                                                    <td>&nbsp;</td>
                                                                <?php else:?>
                                                                    <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                                                                <?php endif;?>
                                                            </tr>
                                                            <?php $i++; ?>
                                                        <?php endforeach?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="box-footer bg-info">
                                                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#<?php echo $__stpr_box_id?>', '#<?php echo $__stpr_row_id?>', this)">Add More</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            $portfolio_repeat_count++;
                        endforeach?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">

    /**
     * Duplicate Short Term Policy Rate Row
     */
    function __duplicate_tr(box, src, a)
    {
        var $box = $(box),
            $src = $(src),
            html = $src.html(),
            $row  = $('<tr></tr>');

        $row.html(html);

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove()\'>Remove</a></td>');

        // Append to table body
        $(box).append($row);
    }
</script>
