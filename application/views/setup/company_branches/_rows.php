<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Company Branch:  Data List Rows
*/

/**
 * Load Rows from View
 */
foreach($records as $record)
{
	$this->load->view('setup/company_branches/_single_row', ['record' => $record]);
}

/**
 * Next Link?
 */
$next_id  = $next_id ?? NULL;
if($next_id):
	$loader_box_id = '__next-loader-company-branch-'.$next_id;
?>
	<tr id="<?php echo $loader_box_id;?>">
		<td colspan="8" class="text-center pointer filter-next-page-trigger"
			data-loading-text="Loading ..."
			data-url="<?php echo $next_url;?>"
			data-method="append"
			data-box="#search-result-company-branch"
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