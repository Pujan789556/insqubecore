<?php
defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if( $this->dx_auth->get_auth_error() ):?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <p><?php echo $this->dx_auth->get_auth_error(); ?></p>
    </div>
<?php endif;?>