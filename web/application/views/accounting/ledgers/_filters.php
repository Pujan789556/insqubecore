<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Advanced Search Filter: Ledger
*
* Variables Required: $filter_url, $filters, $DOM_DataListBoxId, $DOM_FilterFormId
*/
$DOM_DataListBoxId = $DOM_DataListBoxId ?? '#_iqb-data-list';
$DOM_FilterFormId = $DOM_FilterFormId ?? '_form-iqb-filter';
?>
<style type="text/css">
	.ins-box-filter label{clear:both; display: block;}
</style>
<div class="box box-solid no-margin ins-box-filter">
	<?php echo form_open( $filter_url,
            [
            	'id'  			=> $DOM_FilterFormId,
                // 'class' 		=> 'form-inline form-iqb-filter',
                'class' 		=> 'form-inline',
                'method' 		=> 'post',
                'data-box' 		=> '#' . $DOM_DataListBoxId, // Filter Result Box
                'data-method' 	=> 'html',
                'data-filter-url' => $filter_url,
                'data-print-url' => $print_url
            ]);?>
		<div class="box-body">
			<?php
			/**
			 * Load Filter Components
			 *
			 * Section I
			 */
			$this->load->view('templates/_common/_form_components_inline', [
	            'form_elements' => $filters['section-1'],
	            'form_record'   => NULL
	        ]);
			?>

			<div id="party-box" data-widget-party="party">
	            <?php
	            /**
				 * Load Filter Components
				 *
				 * Section II
				 */
	            $party_type_element = $filters['section-2'][0];
	            $party_type_element['_extra_html_after'] =
	            	'<a href="#" class="btn btn-xs btn-round bg-purple mrg-r-5" data-toggle="tooltip" title="Find Party" onclick="return __find_party(this);"><i class="fa fa-filter"></i>...</a>' .
	            	'<span class="mrg-r-5 text-purple _text-ref-party" readonly></span>' .
	            	'<input type="hidden" name="filter_party_id" value="" data-field="party_id">';

                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => [ $party_type_element ],
                    'form_record'   => NULL
                ]);
                ?>

	        </div>

	        <?php
			/**
			 * Load Filter Components
			 *
			 * Section III
			 */
			$this->load->view('templates/_common/_form_components_inline', [
	            'form_elements' => $filters['section-3'],
	            'form_record'   => NULL
	        ]);
			?>
		</div>
		<div class="box-footer text-right">
			<button type="submit" class="btn btn-info filter" name="btn_search" id="_btn-filter-search" value="search"><i class="fa fa-search"></i> Search</button>
			<button type="submit" class="btn btn-info filter" id="_btn-filter-print" name="btn_print" value="print"><i class="fa fa-print"></i> Print</button>
			<button type="reset" class="btn btn-default" id="_btn-filter-reset"
				onclick='var f = $(this).closest("form"); f[0].reset(); $("#_iqb-data-list-box-ac_ledgers").fadeOut(300, function(){$(this).html("").fadeIn();}); return false;'>Clear</button>
		</div>
	<?php echo form_close();?>
</div>