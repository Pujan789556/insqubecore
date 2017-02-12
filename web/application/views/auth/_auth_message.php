<?php
defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="alert alert-<?php echo $alert_type ?? 'info'?>">
    <h4><?php echo $title ?? 'OOPS!' ?></h4>
    <p><?php echo $auth_message ?></p>
</div>