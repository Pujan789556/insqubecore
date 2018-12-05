<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Content Header: Breadcrumbs
 */
?>
<h1><?php echo $content_header; ?></h1>
<?php 
/**
 * Load Breadcrumbs
 */
$this->load->view('templates/_common/_breadcrumbs');
?>