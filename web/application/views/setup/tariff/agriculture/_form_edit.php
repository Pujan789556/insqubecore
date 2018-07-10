<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Tariff - Agriculture
 */
?>
<style type="text/css">
.remove-row{margin-top: 10px; margin-bottom: 10px; border-top:1px solid #ccc;}
.box-body.with-bordered{border: 1px solid #eee;}
table .form-group{margin-bottom:0;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">Portfolio</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $record->portfolio_name_en;?></p>
            </div>
        </div>

        <?php
        /**
         * Default Configurations
         *
         * Load Form Components
         */
        $section_elements = $form_elements['defaults'];
        $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $section_elements,
                'form_record'       => $record
        ]);
        ?>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Tariff Details</h4>
        </div>
        <?php
        $section_elements   = $form_elements['tariff'];
        $tariff = $record->tariff ? json_decode($record->tariff) : [];

        $tariff_formatted = [];
        foreach($tariff as $single)
        {
            $tariff_formatted[$single->bs_agro_breed_id] = $single;
        }

        ?>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php foreach($section_elements as $elem): ?>
                        <th><?php echo $elem['label'] ?></th>
                    <?php endforeach ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach($bs_agro_breeds as $bs_agro_breed_id => $category_name ): ?>
                    <tr>
                        <?php
                        /**
                         * Single Row
                         */

                        $form_record = $tariff_formatted[$bs_agro_breed_id] ?? NULL;

                        // Add ID in hidden field if no form record (Category ID)
                        if( !$form_record )
                        {
                            $section_elements[0]['_default'] = $bs_agro_breed_id;
                        }


                        $section_elements[0]['_extra_html_below'] = $category_name;
                        $this->load->view('templates/_common/_form_components_table', [
                            'form_elements' => $section_elements,
                            'form_record'   => $form_record
                        ]);
                        ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>