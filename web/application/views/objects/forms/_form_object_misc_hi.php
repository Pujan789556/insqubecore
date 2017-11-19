<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - MISCELLANEOUS - HEALTH INSURANCE (HI)
 */
?>
<style type="text/css">
    td > .form-group{margin-bottom: 0}
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Basic Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements = $form_elements['basic'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record
                ]);
                ?>
                <p class="help-block">Please note that the excel file must have the following format:</p>
                <table class="table table-condensed table-bordered text-danger">
                    <thead>
                        <?php
                        $item_headings = _OBJ_MISC_HI_item_headings_dropdown();
                         ?>
                        <tr>
                            <?php foreach($item_headings as $key=>$label): ?>
                                <th><?php echo $label; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach($item_headings as $key=>$label): ?>
                                <td><strong>...</strong></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
                <p class="text-info"><strong>NOTE:</strong> If you have uploaded item list, please do not fill the <strong>Item Details. It will be overwritten by the excel data.</strong> section below.</p>
                <div class="alert alert-danger">
                    <p>The excel file must have three sheets namely:</p>
                    <ul>
                        <li>
                            <strong>Employees</strong> : List of employee
                        </li>
                        <li>
                            <strong>Benefits</strong>: Medical Benefit List with Medical Groups
                        </li>
                        <li>
                            <strong>HeadingWiseLimit</strong>: Medical Group Summary
                        </li>
                    </ul>
                    <p>Please note that each excel sheet have the <strong>EXACTLY SAME SHEET NAME</strong> and <strong>SAME FORMAT</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
