<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form Outer Box: Premium
 */
?>
<div id="form-box-object">
    <?php
    /**
     * Load object form
     */
    $this->load->view($this->data['_view_base'] . '/_form');
    ?>
</div>