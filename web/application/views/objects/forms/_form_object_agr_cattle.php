<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - AGRICULTURE - CATTLE SUB-PORTFOLIO
 */
?>
<style type="text/css">
    td > .form-group{margin-bottom: 0}
</style>
<div class="row">

    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">आधारभूत जानकारीहरु</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements = $form_elements['basic'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">कृषिजन्य सुबिधाहरू</h4>
            </div>
            <div class="box-body form-horizontal">
                <p class="help-block">कृषिजन्य सुबिधाहरू प्राप्त गर्नु भएको छ ? यदि छ भने त्यो कहाँबाट प्राप्त गर्नुभयो ?</p>
                <?php
                $section_elements = $form_elements['facilities'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">पशुधनको विवरण</h4>
            </div>
            <?php
            $js_breeds = [];
            $section_elements   = $form_elements['items'];
            $items               = $record->items ?? NULL;
            $item_count          = count( $items->sum_insured ?? [] );
            ?>
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($item_count):
                            for ($i=0; $i < $item_count; $i++):
                                /**
                                 * Extract Breed Info For Javascript Rendering of Breed Dropdown
                                 * on EDIT mode
                                 */
                                $js_breeds[] = is_numeric($items->breed[$i]) ? $items->breed[$i] : '';
                                ?>
                                <tr <?php echo $i == 0 ? 'id="__cattle_items_row"' : '' ?>>
                                    <?php foreach($section_elements as $single_element):?>
                                        <td>
                                            <?php
                                            /**
                                             * Load Single Element
                                             */
                                            $single_element['_default']    = $items->{$single_element['_key']}[$i] ?? '';
                                            $single_element['_value']      = $single_element['_default'];
                                            $this->load->view('templates/_common/_form_components_inline', [
                                                'form_elements' => [$single_element],
                                                'form_record'   => NULL
                                            ]);
                                            ?>
                                        </td>
                                    <?php
                                    endforeach;
                                    if($i == 0):?>
                                        <td>&nbsp;</td>
                                    <?php else:?>
                                        <td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'><i class="fa fa-trash"></i></a></td>
                                    <?php endif;?>
                                </tr>
                        <?php
                            endfor;
                        else:?>
                            <tr id="__cattle_items_row">
                                <?php foreach($section_elements as $single_element):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$single_element],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php endforeach?>
                                <td>&nbsp;</td>
                            </tr>
                        <?php endif;?>
                </tbody>
            </table>
            <div class="box-footer bg-info">
                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__cattle_items_row', this)">Add More</a>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">विगत १ वर्षमा तपाईंको पशुधनमा भएको हानी नोक्सानीको विवरण</h4>
            </div>
            <?php
            $section_elements   = $form_elements['damages'];
            $damages            = $record->damages ?? NULL;
            $item_count         = count( $damages->year ?? [] );
            ?>
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($item_count):
                            for ($i=0; $i < $item_count; $i++):?>
                            <tr <?php echo $i == 0 ? 'id="__cattle_damages_row"' : '' ?>>
                                <?php foreach($section_elements as $single_element):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $single_element['_default']    = $damages->{$single_element['_key']}[$i] ?? '';
                                        $single_element['_value']      = $single_element['_default'];
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$single_element],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php
                                endforeach;
                                if($i == 0):?>
                                    <td>&nbsp;</td>
                                <?php else:?>
                                    <td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'><i class="fa fa-trash"></i></a></td>
                                <?php endif;?>
                            </tr>
                        <?php
                            endfor;
                        else:?>
                            <tr id="__cattle_damages_row">
                                <?php foreach($section_elements as $single_element):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$single_element],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php endforeach?>
                                <td>&nbsp;</td>
                            </tr>
                        <?php endif;?>
                </tbody>
            </table>
            <div class="box-footer bg-info">
                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__cattle_damages_row', this)">Add More</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Breeds update on Edit MODE
    var breeds = [<?php echo implode(',', $js_breeds) ?>];
    setTimeout(function(){
        var category = $('#bs_agro_category_id option:selected').val();
        console.log(category);
        if(category) __update_breed(category, breeds);
    }, 300);

    function __update_breed(category, breeds)
    {
        var $target = $('select.breed-dropdown');
            $target.empty();

        $.getJSON('<?php echo base_url()?>objects/dd_agro_breed/'+category, function(r){
            // Update dropdown
            if(r.status == 'success' && typeof r.options !== 'undefined'){
                $target.append($('<option>', {
                    value: '',
                    text : 'Select...'
                }));
                $.each(r.options, function(key, value) {
                    $target.append($('<option>', {
                        value: key,
                        text : value
                    }));
                });
                $target.prop('selectedIndex',0).trigger('change');

                if(breeds) {
                    for(var i=0; i < breeds.length; i++){
                        $( $target[i] ).val(breeds[i]);
                    }
                    $target.trigger('change');
                }
            }
        });
    }

    // Edit mode: Get category id and fetch list of options and update on all breed dropdown.
    $('#bs_agro_category_id').on('change', function(e){
        e.preventDefault();

        // Fetch the breed list
        var v = $(this).val();
        if(v) __update_breed(v);

    });


    // Field Togggler
    $('input[name="object[flag_ownership]"]').on('ifChecked', function(event){
        __toggle_field(this, 'J', 'textarea[name="object[partner_details]"]');
    });
    $('input[name="object[flag_investment]"]').on('ifChecked', function(event){
        __toggle_field(this, 'Y', 'textarea[name="object[invester_details]"]');
    });

    // On form load toggle field
    __toggle_field('input[name="object[flag_investment]"]:checked', 'Y', 'textarea[name="object[invester_details]"]');
    __toggle_field('input[name="object[flag_ownership]"]:checked', 'J', 'textarea[name="object[partner_details]"]');

    function __toggle_field(trigger, match, textbox)
    {
        var $fg = $(textbox).closest('.form-group'),
            chkValue = $(trigger).val(),
            txtLoan = '',
            $tg = null;

        if( textbox == 'textarea[name="object[invester_details]"]'){
            txtLoan = 'input[name="object[investment_amount]"]';
            $tg = $(txtLoan).closest('.form-group');
        }

        if(  chkValue === match ){
            $fg.fadeIn();
            if(txtLoan){
                $tg.fadeIn();
            }
        }else{
            $fg.fadeOut();
            $(textbox).val('');

            if(txtLoan){
                $tg.fadeOut();
                $(txtLoan).val('');
            }
        }
    }

    /**
     * Duplicate Treaty Distribution Row
     */
    function __duplicate_tr(src, a)
    {
        var $src = $(src),
            $box = $src.closest('tbody'),
            html = $src.html(),
            $row  = $('<tr></tr>');

        $row.html(html);

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'><i class="fa fa-trash"></i></a></td>');

        // Append to table body
        $box.append($row);
    }
</script>