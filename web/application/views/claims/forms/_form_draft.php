<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Claim - Draft
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-claims'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                  <h4 class="box-title">Accident Details</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['accident_details'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
            </div>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                  <h4 class="box-title">Intimation Details</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['intimation_details'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                  <h4 class="box-title">Loss Details</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['loss_details'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
            </div>

            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                  <h4 class="box-title">Claim Estimation</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $form_elements['claim_estimation'],
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Death/Injured Information</h4>
        </div>
        <div class="box-body" style="overflow-x: scroll;">
            <?php
            $section_elements = $form_elements['death_injured_details'];
            $death_injured  = json_decode($record->death_injured ?? '[]');
            ?>
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach ?>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody class="form-inline">
                    <?php
                    $i = 0;
                    if($death_injured):
                        foreach ($death_injured as $single):?>
                            <tr <?php echo $i == 0 ? 'id="__death_injured_row"' : '' ?>>
                                <?php foreach($section_elements as $elem):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $value = $single->{$elem['_key']} ?? '';
                                        $elem['_default']    = $value;
                                        $elem['_value']      = $value;
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$elem],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php
                                endforeach;
                                if($i == 0):?>
                                    <td>&nbsp;</td>
                                <?php else:?>
                                    <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                                <?php endif;?>
                            </tr>
                    <?php
                            $i++;
                        endforeach;
                    else:?>
                        <tr id="__death_injured_row">
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
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
        </div>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__death_injured_row', this)">Add More</a>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">
    // Datetimepicker
    $('.input-group.datetime, .input-group.datetime input').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:00',
        showClose: true,
        showClear: true
    });

    // Compute Claim Estimation
    $('#loss_amount_ip').on('keyup', function(){
        __compute_claim_estimation();
    } )
    $('#loss_amount_tpp').on('keyup', function(){
        __compute_claim_estimation();
    } )

    function __compute_claim_estimation()
    {
        var $dst    = $('#estimated_claim_amount'),
            $ip     = $('#loss_amount_ip'),
            $tpp    = $('#loss_amount_tpp'),
            v1      = parseFloat($ip.val()),
            v2      = parseFloat($tpp.val()),
            total   = 0;
            if(v1) total += v1;
            if(v2) total += v2;

        $dst.val( total );
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
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);

        // Update Sum
        __compute_sum();
    }

</script>