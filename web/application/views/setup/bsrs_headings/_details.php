<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Beema Samiti Report Setup - Headings - Details Page
 */

$sectioned_data = [];
foreach( $headings as $record )
{
    $key = $record->heading_type_name_np . ' (' . $record->heading_type_name_en . ')';
    $sectioned_data[$key][] = $record;
}
?>
<div id="claim-details">
    <div class="row">
        <?php foreach($sectioned_data as $section_heading=>$records): ?>
            <div class="col-sm-6">
                <div class="box box-bordered box-default">
                    <div class="box-header with-border">
                        <h4 class="box-title"><?php echo $section_heading ?></h4>
                    </div>
                    <table class="table table-bordered table-responsive table-condensed">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Heading Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($records as $single): ?>
                                <tr>
                                    <th><?php echo $single->code ?></th>
                                    <td><?php echo nl2br(htmlspecialchars($single->name)) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
