<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Branch
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
            $fiscal_years = array($form_elements[0]);
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $fiscal_years,
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
            $i = 0;
            $setting_fields = [
                'agent_commission'  => ['label' => 'Agent Commission(%)'],
                'direct_discount'   => ['label' => 'Direct Discount(%)'],
                'policy_base_no'    => ['label' => 'Policy Base Number']
            ];

            foreach($portfolios as $portfolio_id=>$portfolio_name):

                $setting_id = '';
                if($action === 'edit')
                {
                    foreach($settings as $t)
                    {
                        if( $t->portfolio_id == $portfolio_id)
                        {
                            $setting_fields['agent_commission']['values'][$i] = $t->agent_commission;
                            $setting_fields['direct_discount']['values'][$i] = $t->direct_discount;
                            $setting_fields['policy_base_no']['values'][$i] = $t->policy_base_no;

                            $setting_id      = $t->id;
                            break;
                        }
                    }
                    echo form_hidden('setting_ids[]', $setting_id);
                }

                echo form_hidden('portfolio_id[]', $portfolio_id);
                ?>
                <div class="col-sm-6">

                    <div class="box box-solid">
                        <div class="box-header gray"><h3 class="box-title"><?php echo ucwords($portfolio_name)?></h3></div>
                        <div class="box-body">

                            <?php foreach($setting_fields as $field_name => $details):?>

                                <?php
                                $label = $details['label'];
                                $values = $details['values'] ?? [];
                                $input_name = "{$field_name}[]";
                                $input_value = $values["{$i}"] ?? '';

                                // From Form Submission
                                if( set_value("{$field_name}[$i]") )
                                {
                                    $input_value = set_value("{$field_name}[$i]");
                                }

                                ?>
                                <div class="form-group <?php echo form_error("{$input_name}") ? 'has-error' : '';?>">
                                    <label><?php echo $label . field_compulsary_text( TRUE )?></label>
                                    <input
                                        data-toggle="tooltip"
                                        title="<?php echo $label;?>"
                                        type="text"
                                        name="<?php echo $input_name;?>"
                                        class="form-control"
                                        placeholder="<?php echo $label;?>"
                                        value="<?php echo $input_value;?>">
                                    <?php if(form_error("{$input_name}")):?><span class="help-block"><?php echo form_error("{$input_name}"); ?></span><?php endif?>
                                </div>
                            <?php endforeach?>
                        </div>
                    </div>
                </div>
            <?php
                $i++;
            endforeach?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
