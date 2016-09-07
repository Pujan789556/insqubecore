<?php
defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="alert alert-<?php echo isset($alert_type) ? $alert_type : 'info'?>">
    <p><?php echo $auth_message ?></p>
</div>