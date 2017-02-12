<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Breadcrumbs
 */
?>
<?php if($breadcrumbs):?>
	<ol class="breadcrumb">
		<li><a href="<?php echo site_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
		<?php foreach($breadcrumbs as $name=>$uri):?>
				<li class="<?php echo empty($uri) ? 'active' : ''?>">
					<?php echo empty($uri) ? $name : anchor(site_url($uri), $name); ?>
				</li>
		<?php endforeach?>
	</ol>
<?php endif; ?>