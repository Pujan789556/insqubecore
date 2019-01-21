<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Tariff - Property
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

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Tariff Details</h4>
        </div>
        <?php

        $tariff_formatted = [];
        if($tariff)
        {
            foreach($tariff as $single)
            {
                $tariff_formatted[$single->portfolio_id] = $single;
            }
        }
        ?>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php foreach($form_elements as $elem): ?>
                        <th><?php echo $elem['label'], field_compulsary_text( TRUE ) ?></th>
                    <?php endforeach ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach($portfolios as $portfolio_id => $portfolio_name ): ?>
                    <tr>
                        <?php
                        /**
                         * Single Row
                         */

                        $form_record = $tariff_formatted[$portfolio_id] ?? NULL;

                        // Add ID in hidden field if no form record (Category ID)
                        if( !$form_record )
                        {
                            $form_elements[0]['_default'] = $portfolio_id;
                        }


                        $form_elements[0]['_extra_html_below'] = $portfolio_name;
                        $this->load->view('templates/_common/_form_components_table', [
                            'form_elements' => $form_elements,
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