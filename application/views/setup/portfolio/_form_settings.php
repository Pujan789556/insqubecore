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
            foreach($portfolios as $portfolio_id=>$portfolio_name):

                $_field_name_agent_commission = "agent_commission[$i]";
                $_value_agent_commission = '';

                $_field_name_direct_discount = "direct_discount[$i]";
                $_value_direct_discount = '';


                $setting_id = '';
                if($action === 'edit')
                {
                    foreach($settings as $t)
                    {
                        if( $t->portfolio_id == $portfolio_id)
                        {
                            $_value_agent_commission   = $t->agent_commission;
                            $_value_direct_discount   = $t->direct_discount;
                            $setting_id      = $t->id;
                            break;
                        }
                    }
                    echo form_hidden('setting_ids[]', $setting_id);
                }

                echo form_hidden('portfolio_id[]', $portfolio_id);

                // Do we have value from form post?
                if( set_value($_field_name_agent_commission) )
                {
                    $_value_agent_commission = set_value($_field_name_agent_commission, '', FALSE);
                }
                if( set_value($_field_name_direct_discount) )
                {
                    $_value_direct_discount = set_value($_field_name_direct_discount, '', FALSE);
                }
                ?>
                <div class="col-sm-6">
                    <div class="form-group <?php echo (form_error('agent_commission[]') != '' OR form_error('direct_discount[]') != '') ? 'has-error' : '';?>">
                        <label for="" class="control-label"><?php echo ucwords($portfolio_name) . field_compulsary_text( TRUE )?></label>
                        <div class="row form-inline">
                            <div class="cox-xs-12">
                                <div class="form-group">
                                    <input data-toggle="tooltip" title="Agent commission(%)" type="number" step="0.1" name="agent_commission[]" class="form-control" placeholder="Agent commission(%)" value="<?php echo $_value_agent_commission;?>">

                                    <input data-toggle="tooltip" title="Direct discount(%)" type="number" step="0.1" name="direct_discount[]" class="form-control" placeholder="Direct discount(%)" value="<?php echo $_value_direct_discount;?>">
                                    <?php if(form_error("agent_commission[]")):?><span class="help-block"><?php echo form_error("agent_commission[]"); ?></span><?php endif?>
                                    <?php if(form_error("direct_discount[]")):?><span class="help-block"><?php echo form_error("direct_discount[]"); ?></span><?php endif?>
                                </div>
                            </div>
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
