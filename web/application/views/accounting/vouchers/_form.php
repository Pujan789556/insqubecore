<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Vouchers
 */
?>
<style type="text/css">
    .table-voucher tbody > tr > td.amount input{text-align: right; height: 28px;}
    .table-voucher tfoot > tr > td{
        border-top: 2px solid #ddd;
    }
    .table-voucher .form-group{display: block; margin-bottom: 0px;}
    .table-voucher-row td{padding:0 1px !important;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-ac-voucher',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box-body form-horizontal">
        <div class="row">
            <div class="col-md-5 col-md-offset-1">
                <?php
                /**
                 * Load Form Components
                 */
                $basic_elements = $form_elements['basic'];
                $voucher_date   = $basic_elements[0];
                $voucher_type   = $basic_elements[1];
                $narration      = $basic_elements[2];

                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => [$voucher_date, $voucher_type],
                    'form_record'   => $record
                ]);
                ?>
            </div>
            <div class="col-md-5">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => [$narration],
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>

    <table class="table table-responsive table-striped table-voucher table-bordered table-condensed">
        <thead>
            <tr>
                <th colspan="5"><h4 class="no-margin">Voucher Details</h4></th>
            </tr>
            <tr>
                <th width="44%">Account Name</th>
                <th width="30%">Party Name (if any)</th>
                <th rowspan="2" class="text-right" width="10%">Debit (Rs.)</th>
                <th rowspan="2" class="text-right" width="10%">Credit (Rs.)</th>
                <th rowspan="2" width="6%">Actions</th>
            </tr>
        </thead>

        <tbody id="debit-box">
            <?php
            $debit_elements     = $form_elements['details']['debits'];
            $account_id         = $debit_elements[0];
            $party_type_dr = $party_type = $debit_elements[1];
            $party_id           = $debit_elements[2];
            $debit_amount       = $debit_elements[3];


            $debit_rows = $voucher_detail_rows['debit_rows'] ?? [];
            $row_counter = 1;
            if( $debit_rows )
            {
                foreach($debit_rows as $row)
                {
                    /**
                     * Render Default, Regular Rows
                     */
                    $row_type = $row_counter == 1 ? 'default' : 'regular';
                    $this->load->view('accounting/vouchers/_form_debit_row', [
                        'row_type'              => $row_type,
                        'row_id'                => 'tmpl-debit-row-' . $row_counter,
                        'amount_element'        => $debit_amount,
                        'party_type_element'    => $party_type,
                        'row'                   => $row
                    ]);
                    $row_counter++;
                }
            }
            else
            {
                /**
                 * Render Default Row
                 */
                $this->load->view('accounting/vouchers/_form_debit_row', [
                    'row_type'              => 'default',
                    'row_id'                => 'tmpl-debit-row-' . $row_counter,
                    'amount_element'        => $debit_amount,
                    'party_type_element'    => $party_type,
                    'row'                   => NULL
                ]);
            }
            ?>
            <tr id="debit-row-add-more">
                <td class="add-more" colspan="5">
                    <a class="btn btn-sm btn-success _add_row" data-row-template="tmpl-debit-row" href="#" data-toggle="tooltip" title="Add Debit Row">+ Debit Row</a>
                </td>
            </tr>
        </tbody>

        <tbody id="credit-box">
            <?php
            $credit_elements    = $form_elements['details']['credits'];
            $account_id         = $credit_elements[0];
            $party_type_cr = $party_type         = $credit_elements[1];
            $party_id           = $credit_elements[2];
            $credit_amount      = $credit_elements[3];

            $credit_rows = $voucher_detail_rows['credit_rows'] ?? [];
            $row_counter = 1;
            if( $credit_rows )
            {
                foreach($credit_rows as $row)
                {
                    /**
                     * Render Default, Regular Rows
                     */
                    $row_type = $row_counter == 1 ? 'default' : 'regular';
                    $this->load->view('accounting/vouchers/_form_credit_row', [
                        'row_type'              => $row_type,
                        'row_id'                => 'tmpl-credit-row-' . $row_counter,
                        'amount_element'        => $credit_amount,
                        'party_type_element'    => $party_type,
                        'row'                   => $row
                    ]);
                    $row_counter++;
                }
            }
            else
            {
                /**
                 * Render Default Row
                 */
                $this->load->view('accounting/vouchers/_form_credit_row', [
                    'row_type'              => 'default',
                    'row_id'                => 'tmpl-credit-row-' . $row_counter,
                    'amount_element'        => $credit_amount,
                    'party_type_element'    => $party_type,
                    'row'                   => NULL
                ]);
            }
            ?>

            <tr id="credit-row-add-more">
                <td class="add-more" colspan="5">
                    <a class="btn btn-sm btn-success _add_row" data-row-template="tmpl-credit-row" href="#" data-toggle="tooltip" title="Add Credit Row">+ Credit Row</a>
                </td>
            </tr>
        </tbody>

        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="text-right text-bold" id="_debit_total_text">0.00</td>
                <td class="text-right text-bold" id="_credit_total_text">0.00</td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<template id="tmpl-debit-row" class="hide">
    <?php
    /**
     * Render Template Row
     */
    $this->load->view('accounting/vouchers/_form_debit_row', [
        'row_type'              => 'template',
        'row_id'                => NULL,
        'amount_element'        => $debit_amount,
        'party_type_element'    => $party_type_dr,
        'row'                   => NULL
    ]);
    ?>
</template>
<template id="tmpl-credit-row" class="hide">
    <?php
    /**
     * Render Template Row
     */
    $this->load->view('accounting/vouchers/_form_credit_row', [
        'row_type'              => 'template',
        'row_id'                => NULL,
        'amount_element'        => $credit_amount,
        'party_type_element'    => $party_type_cr,
        'row'                   => NULL
    ]);
    ?>
</template>

<script type="text/javascript">

    // Add Debit/Credit Row
    $('a._add_row').on('click', function(e){

        e.preventDefault();

        var $a          = $(this),
            tmplId      = $a.data('row-template'),
            rowHtml     = $('#'+tmplId).html(),
            $aHolderRow = $a.closest('tr');

        // Insert this template before the aHolderRow
        $(rowHtml).insertBefore($aHolderRow).hide().fadeIn('normal', function(){
            // Assign This Row an ID
            $(this).attr('id', tmplId + '-' + $.now());
        });

        // Remove any opened tooltip UI (eg. edit button tooltip)
        $('div.tooltip[role="tooltip"]').remove();
    });

    // Find Account
    function __find_account(a)
    {
        var $this = $(a),
            rowId = $this.closest('tr').attr('id'),
            widgetType = $('#'+rowId).data('widget-account'),
            widgetReference = rowId + ':' + widgetType;

        $this.button('loading');
        InsQube.options.__btn_loading = $this;
        $.getJSON('<?php echo base_url()?>ac_accounts/page/f/y/0/' + widgetReference, function(r){
            if( typeof r.html !== 'undefined' && r.html != '' ){
                bootbox.dialog({
                    className: 'modal-default',
                    size: 'large',
                    title: 'Find Account',
                    closeButton: true,
                    message: r.html,
                    buttons:{
                        cancel: {
                            label: "Close",
                            className: 'btn-default'
                        }
                    }
                });
            }
            // Reset Loading
            $this.button('reset');
        });
    }

    // Find Party
    function __find_party(a)
    {
        var $this       = $(a),
            rowId       = $this.closest('tr').attr('id'),
            widgetType = $('#'+rowId).data('widget-party'),
            $targetRow  = $('#' + rowId ),
            $partyType  = $( 'select[data-field="party_type"]', $targetRow ),
            pt          = $partyType.val();

        // Valid Party Type?
        if( ! pt )
        {
            toastr.warning('Please select party type first.', 'OOPS!');
            $partyType.closest('div.form-group').addClass('has-error');
            return false;
        }
        var widgetReference = rowId + ':' + widgetType;

        $this.button('loading');
        InsQube.options.__btn_loading = $this;
        $.getJSON('<?php echo base_url()?>ac_parties/finder/' + pt + '/' + widgetReference, function(r){
            if( typeof r.html !== 'undefined' && r.html != '' ){
                bootbox.dialog({
                    className: 'modal-default',
                    size: 'large',
                    title: 'Find Party',
                    closeButton: true,
                    message: r.html,
                    buttons:{
                        cancel: {
                            label: "Close",
                            className: 'btn-default'
                        }
                    }
                });
            }
            // Reset Loading
            $this.button('reset');
        });
    }

    // Reset Party
    function __reset_party(a)
    {
        var $this       = $(a),
            rowId       = $this.closest('tr').attr('id');

        $( '#' + rowId + ' ._text-ref-party').html('');
        $( '#' + rowId + ' input[data-field="party_id"]').val('');
    }

    // Remove Row
    function __remove_row(a){

        var $a          = $(a),
        $toRemoveRow    = $a.closest('tr');
        $toRemoveRow.fadeOut('normal', function(){
            $toRemoveRow.remove();
        });
        return false;
    }

    // Select Account/Party
    function __do_select(a){
        var $a = $(a),
        selectable  = $a.data('selectable'),
        $targetRow  = $('#' + $a.data('target-rowid')),
        fields      = selectable.fields,
        html        = selectable.html;

        if( typeof fields === 'object'){
            for(var i = 0; i < fields.length; i++) {

                var obj = fields[i];
                $( 'input[data-field="'+obj.ref+'"]', $targetRow ).val(obj.val);
            }
        }
        if( typeof html === 'object'){
            for(var i = 0; i < html.length; i++) {
                var obj = html[i];
                $('.' + obj.ref, $targetRow ).html(obj.val);
            }
        }

        // Close the bootbox if any
        var $bootbox = $a.closest('.bootbox');
        $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
    }

    // Compute DR = CR
    function __compute_sum(a)
    {
        var $this       = $(a),
            $drBox      = $('#_debit_total_text'),
            $crBox      = $('#_credit_total_text'),
            c           = 0,
            d           = 0,
            v;

        $('#debit-box input[data-group="dr"]').each(function() {
            v = $(this).val();
            if( isNaN(v)){
                $(this).val('');
                v = 0;
            }
            d += parseFloat(v);
        });
        $('#credit-box input[data-group="cr"]').each(function() {
            v = $(this).val();
            if( isNaN(v)){
                $(this).val('');
                v = 0;
            }
            c += parseFloat(v);
        });

        d = parseFloat(d.toFixed(4));
        c = parseFloat(c.toFixed(4));

        $drBox.html(d);
        $crBox.html(c);

        $drBox.closest('tfoot').toggleClass('text-red', d != c);
        $drBox.closest('tfoot').toggleClass('text-green', d == c);
    }
</script>
