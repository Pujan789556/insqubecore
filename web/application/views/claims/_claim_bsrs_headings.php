<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claim: Details - Beema Samiti Report Headings
*/

$section_data = [];
foreach( $bsrs_headings_claim as $single )
{
    $box_title = $single->heading_type_name_np . ' (' . $single->heading_type_name_en . ')';
    $section_data["{$box_title}"][] = [$single->code, $single->heading_name];
}
?>
<div class="box box-bordered box-default" id="claim-bsrs-headings">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
            <span class="pull-left">Beema Samiti Report Information</span>
            <span class="pull-right">
                <?php if($record->status === IQB_CLAIM_STATUS_VERIFIED && $this->dx_auth->is_authorized('claims', 'assign.claim.surveyors')): ?>
                        <a href="#"
                            class="trg-dialog-edit btn btn-primary btn-sm"
                            title="Beema Samiti Report Information"
                            data-toggle="tooltip"
                            data-box-size="large"
                            data-title='<i class="fa fa-pencil-square-o"></i> Beema Samiti Report Information - <?php echo $record->claim_code?>'
                            data-url="<?php echo site_url('claims/bs_tags/' . $record->id . '/d');?>"
                            data-form="#_form-claims">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                <?php endif?>
            </span>
        </h3>
    </div>
    <table class="table table-hover">
        <?php
        /**
         * Load Rows from View
         */
        foreach($section_data as $box_title=>$children)
        {
            echo    "<thead>",
                        "<tr><th colspan=\"2\">{$box_title}</th></tr>",
                        "<tr><th width=\"10%\">Code</th><th>Report Heading</th></tr>",
                    "</thead>";

            foreach($children as $single)
            {
                echo    "<tr>",
                            "<td>", $single[0], "</td>",
                            "<td>", $single[1], "</td>",
                        "</tr>";
            }
        }
        ?>
    </table>

</div>