<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Premium Form : FIRE - HOUSEHOLDER
 */
$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options      = json_decode($record->premium_compute_options ?? NULL);
$premium_computation_table_arr  = json_decode($record->premium_compute_options ?? NULL, TRUE);

echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        ['id' => $record->id]);

    /**
     * Premium Summary Table
     */
    $this->load->view('endorsements/snippets/_premium_summary');
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Distribution by Risks</h4>
            </div>
            <div class="box-body form-inline">
                <table class="table table-responsive table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th>Risk</th>
                            <th>Rate (Rs. Per Thousand)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $premium_elements = $form_elements['premium'];
                        foreach($portfolio_risks as $risk_id=>$risk_name):
                            /**
                             * Add Value to the first element
                             */
                            $premium_elements[0]['_default']            = $risk_id;
                            $premium_elements[0]['_extra_html_below']   = $risk_name;

                            /**
                             * Format Field Name
                             *
                             * Format: premium[<field>][<risk_id>]
                             *
                             */
                            $formatted_premium_elements = [];
                            foreach ($premium_elements as $elem)
                            {
                                $elem['field'] .= "[{$risk_id}]";
                                $formatted_premium_elements[] = $elem;
                            }


                            /**
                             * Apply Rate if We have $premium_compute_options
                             */
                            if($premium_computation_table_arr)
                            {
                                // Premium
                                $rate = $premium_computation_table_arr['rate'][$risk_id];
                                $formatted_premium_elements[1]['_default'] = $rate;
                            }
                            ?>
                            <tr>
                                <?php
                                /**
                                 * Load Form Components
                                 */
                                $this->load->view('templates/_common/_form_components_table', [
                                    'form_elements' => $formatted_premium_elements,
                                    'form_record'   => NULL
                                ]);
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        /**
         * Load TXN Common Elements
         */
        $this->load->view('endorsements/forms/_form_txn_common', [
            'record'        => $record,
            'form_elements'     => $form_elements['basic']
        ]);

        /**
         * Other Common Components
         *  1. Premium Installments
         */
        echo $common_components;
        ?>

        <button type="submit" class="hide">Submit</button>
    </div>
</div>
<?php echo form_close();?>
