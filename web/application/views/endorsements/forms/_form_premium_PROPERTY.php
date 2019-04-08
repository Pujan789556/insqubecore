<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : PROPERTY - Policy Premium
 */
$object_attributes          = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
$premium_compute_options  = json_decode( $record->premium_compute_options ?? NULL );


$property_count = count($object_attributes->items);

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
                <h4 class="box-title">Premium Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Portfolio Specific Premium Fields
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $form_elements['premium'],
                    'form_record'       => $premium_compute_options,
                    'grid_label'        => 'col-md-4',
                    'grid_form_control' => 'col-md-8'
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
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
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Risks/Rates</h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Risk/Rate Table Per Property
                 */
                $section_elements   = $form_elements['property_risk'];
                $risk_codes         = $section_elements[1]['_risk_codes'];
                ?>
                <table class="table table-bordered table-condensed margin-b-10">
                    <thead>
                        <tr>
                            <th>Item #</th>
                            <th>Risk Category (दर संकेत) <?php echo field_compulsary_text(TRUE); ?></th>
                            <th>Risk Code (जोखिम संकेत) <?php echo field_compulsary_text(TRUE); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i=0; $i<$property_count; $i++):
                            $si_per_item = _TXN_PROPERTY_compute_si_per_property($object_attributes->items[$i]);
                            ?>
                            <tr class="risk-row">
                                <td>For Property of Place (SI of Rs. <?php echo number_format($si_per_item, 2) ?>) - <strong><?php echo $i+1; ?></strong> </td>
                                <?php
                                $risk_category = $premium_compute_options->risk_category[$i] ?? NULL;
                                $risk_code = $premium_compute_options->risk_code[$i] ?? NULL;
                                $item_record = (object)[
                                    'risk_category' => $risk_category,
                                    'risk_code'     => $risk_code,
                                ];

                                // UPDATE RISK CODE
                                if($risk_category)
                                {
                                    $_risk_codes = $risk_codes[$risk_category];
                                    $section_elements[1]['_data'] = $_risk_codes;
                                }
                                foreach($section_elements as $single_element):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $single_element['_default']    = $item_record->{$single_element['_key']} ?? '';
                                        $single_element['_value']      = $single_element['_default'];
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$single_element],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php endforeach;?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
</div>
<?php echo form_close();?>

<?php
$risk_codes     = $form_elements['property_risk'][1]['_risk_codes'] ?? [];
 ?>
<script type="text/javascript">
    /**
     * Risk Codes for all Categories
     */
     var __risk_codes = <?php echo json_encode($risk_codes) ?>;

     // Risk code change on risk category change
     $(document).on('change', '.risk_category', function(e){
        e.preventDefault();

        var v = $(this).val(),
            $risk_code = $(this).closest('.risk-row').find('.risk_code');

        // Empty risk code options
        $risk_code
                .find('option')
                .remove();

        if(v)
        {
            var codes = __risk_codes[v];
            if(codes){
                $.each(codes, function (key, value) {
                     $risk_code
                            .append($("<option></option>")
                            .attr("value",key)
                            .text(value));
                 });
            }
        }
     });

</script>
