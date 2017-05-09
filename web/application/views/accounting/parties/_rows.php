<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Accounting Parties:  Data List Rows
*/

/**
 * Load Rows from View
 */
$_flag__show_widget_row = $_flag__show_widget_row ?? FALSE;
foreach($records as $record)
{
	$single_row = $_flag__show_widget_row ? 'accounting/parties/_single_row_widget' : 'accounting/parties/_single_row';
	$this->load->view($single_row, compact('record'));
}

/**
 * Next Link?
 */
if($next_id):
	// $next_url = site_url('customers/page/'.$next_id);
	$loader_box_id = '__next-loader-ac_party-'.$next_id;
?>
	<tr id="<?php echo $loader_box_id;?>">
		<td colspan="8" class="text-center pointer filter-next-page-trigger"
			data-loading-text="Loading ..."
			data-url="<?php echo $next_url;?>"
			data-method="append"
			data-box="#search-result-ac_party"
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