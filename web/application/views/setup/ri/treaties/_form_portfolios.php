<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Portfolios
 */
?>
<style type="text/css">
input.form-control, select.form-control{height:24px; max-width: 120px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-distribution',
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
            $this->load->view('setup/ri/treaties/snippets/_ri_basic');
            ?>
        </div>
    </div>
    <div style="overflow-x: scroll;">
        <table class="table table-bordered table-hover table-condensed">
            <thead style="background: #f9f9f9;">
                <tr>
                    <th>Portfolio</th>
                    <?php foreach($form_elements as $element):?>
                        <th class="<?php echo $element['_type'] == 'hidden' ? 'hide' : ''?>"><?php echo $element['label']?></th>
                    <?php endforeach?>
                </tr>
            </thead>

            <tbody class="form-inline">
                <?php foreach ($portfolios as $portfolio):?>
                    <tr>
                        <td><?php echo $portfolio->portfolio_name_en;?></td>
                        <?php foreach($form_elements as $element):?>
                            <?php if($element['_type'] == 'hidden'):?>
                                <?php echo form_hidden($element['field'], $portfolio->{$element['_field']});?>
                            <?php else:?>
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
                                <?php endif?>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>