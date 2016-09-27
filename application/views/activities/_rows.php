<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Activities:  Data List Rows
*/

/**
 * Load Rows from View
 */ 
foreach($records as $record)
{
	$this->load->view('activities/_single_row', compact('record'));
}

/**
 * Next Link?
 */
if($next_id):
	$next_url = site_url('activities/page/'.$next_id);
	$loader_box_id = '__next-loader-'.$next_id;
?>	
	<tr id="<?php echo $loader_box_id;?>">
		<td colspan="4" class="text-center">
			<a 
			href="#" 
			data-loading-text="Loading ..."
			data-url="<?php echo $next_url;?>"
			data-method="append"
			data-box="#live-searchable"
			data-self-destruct="true"
			data-loader-box="#<?php echo $loader_box_id;?>"
			class="btn btn-default btn-flat"
			onclick="InsQube.load(this)">Load More Result</a>
		</td>
	</tr>
<?php endif;?>	