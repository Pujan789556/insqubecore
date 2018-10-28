<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Script for list Page:
*
* Account ID filter selectable
*/
?>
<script type="text/javascript">
    // Initialize Select2
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $('select#filter_account_id').select2();

        $(document).on('click', '#_btn-filter-reset', function(){
        	$('select#filter_account_id').select2();
        });
    });


    // ---------------------------------------------------------------

	/**
	 * Filter - Select Quarter/Month Render on Type Change
	 */
	$(document).on('change', 'select[name="filter_type"]', function(e){
		e.preventDefault();
		var $this = $(this),
			v = $this.val(),
			$target = $('select[name="filter_fy_quarter_month"]');

			$target.empty();
			if( v == 'Q' ){
				$('#quarter-dropdown-template option').clone().appendTo($target);
			}else if( v == 'M' ){
				$('#month-dropdown-template option').clone().appendTo($target);
			}
	});
</script>