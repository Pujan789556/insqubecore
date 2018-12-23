<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form - Premium : MISCELLANEOUS - CASH IN COUNTER
 */
$object_attributes  = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options = $endorsement_record->premium_compute_options ? json_decode($endorsement_record->premium_compute_options, TRUE) : NULL;
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);

    /**
     * Premium Summary Table
     */
    $this->load->view('endorsements/snippets/_premium_summary');
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Table (Risk-wise)</h4>
            </div>
            <div class="box-body form-inline" style="overflow-x: scroll;">
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
                             * Format: premium[<field>][<risk_id>][<ItemIndex>][]
                             *
                             */
                            $formatted_premium_elements = [];
                            foreach ($premium_elements as $elem)
                            {
                                $elem['field'] .= "[{$risk_id}]";
                                $formatted_premium_elements[] = $elem;
                            }


                            /**
                             * Apply Rate and Rate Base if We have $premium_compute_options
                             */
                            if($premium_compute_options)
                            {
                                // Rate
                                $rate = $premium_compute_options['rate'][$risk_id];
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

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Pool Premium</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Load Form Components - Pool Premium
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $form_elements['pool'],
                    'form_record'       => (object)$premium_compute_options
                ]);
                ?>
            </div>
        </div>

        <?php
        /**
         * Load TXN Common Elements
         */
        $this->load->view('endorsements/forms/_form_txn_common', [
            'endorsement_record'        => $endorsement_record,
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
