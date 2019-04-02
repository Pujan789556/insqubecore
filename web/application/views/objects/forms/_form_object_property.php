<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Property
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h3 class="box-title">Property Details</h3>
            </div>
            <div class="box-body" id="property-items">
                    <?php
                    $items               = $record->items ?? NULL;
                    $item_count          = count( $record->items ?? [] );
                    if($item_count):
                        $row_count = 1;
                        foreach ($items as $item_record)
                        {
                            $row_start = $row_count++ == 1 ? '<div class="box box-solid box-bordered property-row" id="__property_row">' : '<div class="box box-solid box-bordered property-row">';

                            echo $row_start;
                            $this->load->view('objects/forms/_form_row_property_item', [
                                'item_record'   => $item_record,
                                'form_elements' => $form_elements
                            ]);

                            $row_end = $row_count > 2 ? '<div class="box-footer bg-info"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest(".property-row").remove();\'>Remove Property</a></div></div>' : '</div>';
                           echo $row_end;
                        }
                    else: ?>
                        <div class="box box-solid box-bordered property-row" id="__property_row">
                            <div class="box-body">
                                <?php
                                $this->load->view('objects/forms/_form_row_property_item', [
                                    'item_record'   => NULL
                                ]);
                                ?>
                            </div>
                        </div>
                    <?php endif ?>
            </div>
            <div class="box-footer bg-info">
                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__property_row', this)">Add Another Property</a>
            </div>
        </div>
    </div>
</div>

<?php
$risk_codes     = $form_elements['property_risk'][1]['_risk_codes'];
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
            $risk_code = $(this).closest('.property-row').find('.risk_code');

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

    function __duplicate_tr(src, a)
    {
        var $src = $(src),
            $box = $('#property-items'),
            html = $src.html(),
            $row  = $('<div class="box box-solid box-bordered property-row"></div>');

        $row.html(html);


        // Add Remover Column
        $row.append('<div class="box-footer bg-info"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest(".property-row").remove();\'>Remove Property</a></div>');

        // Reset Fields
        $('input, select, textarea', $row).val('');

        // Append to table body
        $box.append($row);
    }
</script>
