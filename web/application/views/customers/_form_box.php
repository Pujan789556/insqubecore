<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form Outer Box: Customer
 */
?>
<div id="form-box-customer">
    <?php
    /**
     * Load customer form
     */
    $this->load->view($this->data['_view_base'] . '/_form');
    ?>
</div>