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
                'form_record'   => $record
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
        <h3 class="box-title">Branch Target For Selected Fiscal Year</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
            $i = 0;
            foreach($branches as $branch_id=>$branch_name):

                $target_name = "target_total[$i]";
                $target_value = '';
                $target_id = '';
                if($action === 'edit')
                {
                    foreach($targets as $t)
                    {
                        if( $t->branch_id == $branch_id)
                        {
                            $target_value   = $t->target_total;
                            $target_id      = $t->id;
                            break;
                        }
                    }
                    echo form_hidden('target_ids[]', $target_id);
                }

                echo form_hidden('branch_id[]', $branch_id);

                // Do we have value from form post?
                if(set_value($target_name))
                {
                    $target_value = set_value($target_name, '', FALSE);
                }
                ?>
                <div class="col-sm-6">
                    <div class="form-group <?php echo form_error('target_total[]') ? 'has-error' : '';?>">
                        <label for="" class="control-label"><?php echo ucwords($branch_name) . field_compulsary_text( TRUE )?></label>
                        <input type="number" step="0.01" name="target_total[]" class="form-control" placeholder="Enter branch target total..." value="<?php echo $target_value;?>">
                            <?php if(form_error("target_total[]")):?><span class="help-block"><?php echo form_error("target_total[]"); ?></span><?php endif?>
                    </div>
                </div>
            <?php
                $i++;
            endforeach?>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
