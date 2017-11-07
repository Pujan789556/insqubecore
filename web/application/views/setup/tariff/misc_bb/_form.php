<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Tariff - MISC (Banker's Blanket)
 */
$anchor_remove = '<div class="row remove-row"><div class="col-xs-12 text-right">' .
                         '<a href="#" onclick=\'$(this).closest(".box-body").remove()\'>Remove</a>' .
                     '</div></div>' .
                 '</div>';
?>
<style type="text/css">
.remove-row{margin-top: 10px; margin-bottom: 10px; border-top:1px solid #ccc;}
.box-body.with-bordered{border: 1px solid #eee;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">Portfolio</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $portfolio_record->name_en;?></p>
            </div>
        </div>

        <?php
        /**
         * Default Configurations
         *
         * Load Form Components
         */
        $section_elements = $form_elements['defaults'];
        $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $section_elements,
                'form_record'       => $record
        ]);
        ?>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Tariff Details</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            $section_elements   = $form_elements['tariff'];
            $tariff_record = json_decode($record->tariff ?? NULL);
            $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $section_elements,
                    'form_record'       => $tariff_record
            ]);
            ?>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
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