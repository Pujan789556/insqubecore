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
	$this->load->view($this->data['_view_base'] . '/_single_row', compact('record'));
}

/**
 * Next Link?
 */
if($next_id):
	$loader_box_id = '__next-loader-user-'.$next_id;
?>
	<tr id="<?php echo $loader_box_id;?>">
		<td colspan="9" class="text-center pointer filter-next-page-trigger"
			data-loading-text="Loading ..."
			data-url="<?php echo $next_url;?>"
			data-method="append"
			data-box="#<?php echo $DOM_RowBoxId ?>"
			data-self-destruct="true"
			data-loader-box="#<?php echo $loader_box_id;?>"
			data-load-method="post"
			data-post-form="#<?php echo $DOM_FilterFormId?>"
			onclick="return InsQube.load(event,this)"
		>
			<span class="text-blue">Load More Result<br/> <i class="fa fa-angle-down"></i></span>
		</td>
	</tr>
<?php endif;?>