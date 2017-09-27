<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio Settings
 */

/**
 * Format Settings Based on Portfolio
 */
$settings_by_portfolios = [];
if($settings)
{
    foreach($settings as $single)
    {
        $single->setting_id = $single->id;
        $settings_by_portfolios["{$single->portfolio_id}"] = $single;
    }
}
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            // 'id'    => '__testform',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['fiscal_yr_id' => $record->fiscal_yr_id] : []); ?>


<div class="box box-solid box-bordered">
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
                'form_elements' => $form_elements['fiscal_yr'],
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
</div>

<div class="box-header with-border">
    <h3 class="box-title">Portfolio Basic Settings For Selected Fiscal Year</h3>
</div>
<div class="box-body form-inline" style="overflow-x: scroll;">
    <?php
    $basic_elements = $form_elements['basic'];
    ?>
    <table class="table table-responsive table-hover table-condensed table-bordered">
        <thead>
            <tr>
                <?php foreach($basic_elements as $elem):?>
                    <th><?php echo $elem['label'] ?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($portfolios_tree as $portfolio_parent=>$child_portfolios): ?>
                <tr>
                    <th colspan="9"><?php echo $portfolio_parent ?></th>
                </tr>
                <?php foreach($child_portfolios as $portfolio_id=>$portfolio_name):

                        // Get the Settings for This portfolio
                        $per_portfolio_settings  = $settings_by_portfolios["{$portfolio_id}"] ?? NULL;

                        // Per Portfolio Form Elements
                        $per_portfolio_elements = $basic_elements;

                        // Portfolio Lable show
                        $per_portfolio_elements[0]['_extra_html_below']   = $portfolio_name;

                        // Settings ID (edit)
                        if($per_portfolio_settings)
                        {
                            $per_portfolio_elements[ count($per_portfolio_elements) -1 ]['_extra_html_below'] = $per_portfolio_settings->id;
                        }
                    ?>
                    <tr>
                        <?php
                        /**
                         * Load Form Components
                         */
                        $this->load->view('templates/_common/_form_components_table', [
                            'form_elements' => $per_portfolio_elements,
                            'form_record'   => $per_portfolio_settings
                        ]);
                        ?>
                    </tr>
                <?php endforeach ?>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
