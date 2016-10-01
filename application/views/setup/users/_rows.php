<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Users:  Data List Rows
*/

/**
 * Load Rows from View
 */
foreach($records as $record)
{
	$this->load->view('setup/users/_single_row', compact('record'));
}

/**
 * Next Link?
 */
if($next_id):
	$next_url = site_url('users/page/'.$next_id);
	$loader_box_id = '__next-loader-'.$next_id;
?>	
	<tr id="<?php echo $loader_box_id;?>">
		<td colspan="7" class="text-center pointer"
			data-loading-text="Loading ..."
			data-url="<?php echo $next_url;?>"
			data-method="append"
			data-box="#live-searchable"
			data-self-destruct="true"
			data-loader-box="#<?php echo $loader_box_id;?>"
			onclick="return InsQube.load(event,this)"
		>
			<span class="text-blue">Load More Result<br/> <i class="fa fa-angle-down"></i></span>
		</td>
	</tr>
<?php endif;?>	