<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Debit Row : Vouchers
 *
 * Variables:
 *      $row_type       default|template|regular
 *
 */

// NOTE: We don't supply row_id on template row
// Data API - data-widget-account & data-widget-party are used to pass widget explorer reference
?>

<?php if( $row_type !== 'template' ):?>
    <tr id="<?php echo $row_id?>" data-widget-account="account" data-widget-party="party">
<?php else:?>
    <tr data-widget-account="account" data-widget-party="party">
<?php endif;?>
    <td>
        <div class="form-group">
            <a href="#" class="btn btn-xs btn-round bg-purple mrg-r-5" data-toggle="tooltip" title="Find Account" onclick="return __find_account(this);"><i class="fa fa-filter"></i>...</a>
            <span class="text-purple _text-ref-account" readonly></span>
            <input type="hidden" name="account_id[dr][]" value="" data-field="account_id">
        </div>
    </td>
    <td>
        <div class="form-group">
            <div class="col-md-4">
                <?php
                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => [ $party_type_element ],
                    'form_record'   => NULL
                ]);
                ?>
            </div>
            <a href="#" class="btn btn-xs btn-round bg-purple mrg-r-5" data-toggle="tooltip" title="Find Party" onclick="return __find_party(this);"><i class="fa fa-filter"></i>...</a>
            <span class="mrg-r-5 text-purple _text-ref-party" readonly></span>
            <input type="hidden" name="party_id[dr][]" value="" data-field="party_id">
        </div>
    </td>
    <td class="text-right amount">
        <?php
        $this->load->view('templates/_common/_form_components_inline', [
            'form_elements' => [ $amount_element ],
            'form_record'   => NULL
        ]);
        ?>
    </td>
    <td>&nbsp;</td>
    <td>
        <?php if( $row_type !== 'default' ):?>
            <a href="#" class="btn btn-sm btn-danger" onclick="return __remove_row(this);">Remove</a>
        <?php endif?>
    </td>
</tr>
