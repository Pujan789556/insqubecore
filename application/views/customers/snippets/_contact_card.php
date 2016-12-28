<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Snippet : Contact Card
*/
?>
<div class="box box-primary">
    <?php
    /**
     * Contact Widget
     */
    echo get_contact_widget($record->contact);
    ?>
    <div class="box-footer">
        <a href="#" class="btn btn-primary btn-block"><i class="fa fa-pencil-square-o margin-r-5"></i><b>Edit Contact</b></a>
    </div>

</div>