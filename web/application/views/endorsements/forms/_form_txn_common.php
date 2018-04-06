<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy TXN - Common TXN Fields
 */
?>
<div class="box box-solid box-bordered form-horizontal">
    <div class="box-header with-border">
        <h4 class="box-title">General Information</h4>
    </div>
    <div class="box-body">
        <?php

        /**
         * Default Stamp Duty if NULL
         */
        if($endorsement_record->amt_stamp_duty === NULL )
        {
            foreach($form_elements as $elem)
            {
                if($elem['field'] === 'amt_stamp_duty')
                {
                    $endorsement_record->amt_stamp_duty = $elem['_default'];
                }
            }
        }


        /**
         * Load Form Components - Basic Elements
         */
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements'     => $form_elements,
            'form_record'       => $endorsement_record,
            'grid_label'        => 'col-sm-4',
            'grid_form_control' => 'col-sm-8'
        ]);
        ?>
    </div>
</div>