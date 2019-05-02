<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Tax & Commission
 */
?>
<style type="text/css">
input.form-control, select.form-control{height:24px; max-width: 120px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-tnc',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="row">
        <div class="col-md-4">
            <?php
            /**
             * Basic Overview
             */
            $this->load->view($this->data['_view_base'] . '/snippets/_ri_basic');

            /**
             * Extract Variables
             */
            $col_headings       = $form_elements['col_headings'];
            $tnc_val_prefix     = $form_elements['tnc_val_prefix'];
            $tnc_col_postfix    = $form_elements['tnc_col_postfix'];
            ?>
        </div>
    </div>
    <div style="overflow-x: scroll;">
        <table class="table table-bordered table-hover table-condensed">
            <thead style="background: #f9f9f9;">
                <tr>
                    <?php foreach($col_headings as $heading):?>
                        <th class=""><?php echo $heading?></th>
                    <?php endforeach?>
                </tr>
            </thead>

            <tbody class="form-inline">

                <?php
                /**
                 * Tax & Commission Titles
                 */
                foreach($tnc_val_prefix as $col_prefix => $rule_single):
                ?>
                    <tr>
                        <td><?php echo $rule_single['label'];?></td>
                        <?php foreach ($tnc_col_postfix as $col_postfix):?>
                            <td>
                                <?php
                                // Update field
                                $field_name = $col_prefix . '_' . $col_postfix;
                                $rule_single['field']       = $field_name;

                                // Update value
                                $rule_single['_default']    = $record->{$field_name};
                                $rule_single['_value']      = $rule_single['_default'];
                                $rule_single['_show_label'] = false;

                                // Render form element
                                $this->load->view('templates/_common/_form_components_inline', [
                                    'form_elements' => [$rule_single],
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </td>
                        <?php endforeach?>
                    </tr>
                <?php endforeach?>
            </tbody>
        </table>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>