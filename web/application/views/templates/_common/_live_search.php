<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Live Search
 */
$options = $options ?? '';
?>
<i class="fa fa-search live-search-search"></i>
<input type="search" name="live_search" class="form-control live-search" placeholder="Search" data-options='<?php echo $options?>' onkeyup="InsQube.liveSearch(this)">
<i class="ion-android-close live-search-clear"
	title="Clear search"></i>