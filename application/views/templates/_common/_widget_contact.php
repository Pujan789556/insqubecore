<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Widget
 *
 * 	Required Variables:
 * 		$contact   OBJECT
 *
 * Address Format:
 *
 *      <strong>Contact Name</strong> *
 *      address1
 *      address2
 *      city, state, zip
 *      country
 *
 *      Tel:
 *      Fax:
 *      Mobile:
 *      Email:
 *      Web:
 */
?>
<div class="box-header with-border">
    <h3 class="box-title">Contact Address</h3>
</div>
<div class="box-body">
    <?php
    /**
     * Load Contact Snippet
     */
    $this->load->view('templates/_common/_widget_contact_snippet');
    ?>
</div>